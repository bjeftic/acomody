<?php

namespace App\Services;

use App\Models\PriceableItem;
use App\Models\PricingPeriod;
use App\Models\PricingHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PricingService
 *
 * ALL pricing business logic goes here
 * Works for ANY entity (Accommodation, MenuItem, Service, Event, etc.)
 */
class PricingService
{
    public function __construct(
        private FeeService $feeService,
        private TaxService $taxService,
        private AvailabilityService $availabilityService
    ) {}

    // ============================================
    // MAIN PRICE CALCULATION
    // ============================================

    /**
     * Calculate complete price breakdown
     *
     * @param string $entityType - 'App\Models\Accommodation', etc.
     * @param int $entityId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $quantity - nights, hours, items, etc.
     * @param int $persons
     * @param array $guestAges - for age-based tax exemptions
     * @param array $optionalFeeIds - user-selected optional fees
     * @return array - complete breakdown
     */
    public function calculateTotalPrice(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate,
        int $quantity = 1,
        int $persons = 1,
        array $guestAges = [],
        array $optionalFeeIds = []
    ): array {
        // Get pricing configuration
        $pricing = PriceableItem::forEntity($entityType, $entityId)
            ->active()
            ->firstOrFail();

        // Calculate based on pricing type
        $breakdown = match ($pricing->pricing_type) {
            'nightly', 'daily', 'hourly' => $this->calculateTimeBasedPrice($pricing, $startDate, $endDate, $quantity, $persons),
            'per_item', 'per_person', 'per_table', 'fixed' => $this->calculateQuantityBasedPrice($pricing, $quantity, $persons),
            default => throw new \Exception("Unsupported pricing type: {$pricing->pricing_type}"),
        };

        // Add fees
        $breakdown['fees'] = $this->feeService->calculateAllFees(
            $entityType,
            $entityId,
            $breakdown['subtotal'],
            $quantity,
            $persons,
            $optionalFeeIds
        );

        $breakdown['fees_subtotal'] = collect($breakdown['fees']['mandatory'])->sum('amount') +
                                       collect($breakdown['fees']['optional'])->sum('amount');

        $breakdown['subtotal_before_tax'] = $breakdown['subtotal'] + $breakdown['fees_subtotal'];

        // Add taxes
        $breakdown['taxes'] = $this->taxService->calculateAllTaxes(
            $entityType,
            $entityId,
            $breakdown['subtotal'],
            $breakdown['fees_subtotal'],
            $quantity,
            $persons,
            $guestAges
        );

        $breakdown['taxes_subtotal'] = collect($breakdown['taxes'])->sum('amount');
        $breakdown['total'] = $breakdown['subtotal_before_tax'] + $breakdown['taxes_subtotal'];

        // Format all amounts
        $breakdown = $this->formatBreakdown($breakdown, $pricing->currency);

        return $breakdown;
    }

    // ============================================
    // TIME-BASED PRICING (Accommodations, Services)
    // ============================================

    /**
     * Calculate price for time-based entities (nightly, hourly)
     */
    protected function calculateTimeBasedPrice(
        PriceableItem $pricing,
        Carbon $startDate,
        Carbon $endDate,
        int $quantity,
        int $persons
    ): array {
        $breakdown = [
            'pricing_type' => $pricing->pricing_type,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'quantity' => $quantity,
            'persons' => $persons,
            'unit_prices' => [],
            'subtotal' => 0,
            'currency' => $pricing->currency,
        ];

        // Calculate for each day/hour/unit
        $currentDate = $startDate->copy();
        for ($i = 0; $i < $quantity; $i++) {
            $price = $this->getPriceForDate($pricing, $currentDate);

            $breakdown['unit_prices'][] = [
                'date' => $currentDate->format('Y-m-d'),
                'price' => $price,
            ];

            $breakdown['subtotal'] += $price;
            $currentDate->addDay(); // or addHour() for hourly
        }

        // Apply bulk discount if applicable
        $discountAmount = $this->calculateBulkDiscount($pricing, $quantity, $breakdown['subtotal']);

        if ($discountAmount > 0) {
            $breakdown['bulk_discount'] = [
                'amount' => $discountAmount,
                'threshold' => $pricing->bulk_discount_threshold,
                'percent' => $pricing->bulk_discount_percent,
            ];
            $breakdown['subtotal'] -= $discountAmount;
        }

        return $breakdown;
    }

    /**
     * Get price for specific date (with period pricing)
     */
    public function getPriceForDate(PriceableItem $pricing, Carbon $date): float
    {
        // Check for period pricing (seasonal, special date)
        $periodPrice = $this->getPeriodPriceForDate($pricing, $date);
        if ($periodPrice !== null) {
            return $periodPrice;
        }

        // Check for weekend pricing
        if ($pricing->has_weekend_pricing && $this->isWeekend($pricing, $date)) {
            return $pricing->weekend_price ?? $pricing->base_price;
        }

        return $pricing->base_price;
    }

    /**
     * Get period-based price for date
     */
    protected function getPeriodPriceForDate(PriceableItem $pricing, Carbon $date): ?float
    {
        $period = PricingPeriod::forEntity($pricing->priceable_type, $pricing->priceable_id)
            ->active()
            ->forDate($date)
            ->orderByPriority('desc')
            ->first();

        if (!$period) {
            return null;
        }

        // Calculate price based on period configuration
        if ($period->price_override) {
            return $period->price_override;
        }

        if ($period->price_multiplier) {
            return $pricing->base_price * $period->price_multiplier;
        }

        if ($period->price_adjustment) {
            return $pricing->base_price + $period->price_adjustment;
        }

        return null;
    }

    /**
     * Check if date is weekend
     */
    protected function isWeekend(PriceableItem $pricing, Carbon $date): bool
    {
        if (!$pricing->weekend_days) {
            return $date->isWeekend(); // Default: Sat & Sun
        }

        $dayName = strtolower($date->format('l'));
        return in_array($dayName, $pricing->weekend_days);
    }

    // ============================================
    // QUANTITY-BASED PRICING (Menu Items, Tickets)
    // ============================================

    /**
     * Calculate price for quantity-based entities (per item, per person)
     */
    protected function calculateQuantityBasedPrice(
        PriceableItem $pricing,
        int $quantity,
        int $persons
    ): array {
        $breakdown = [
            'pricing_type' => $pricing->pricing_type,
            'quantity' => $quantity,
            'persons' => $persons,
            'base_price' => $pricing->base_price,
            'subtotal' => 0,
            'currency' => $pricing->currency,
        ];

        // Calculate based on pricing type
        $breakdown['subtotal'] = match ($pricing->pricing_type) {
            'per_item' => $pricing->base_price * $quantity,
            'per_person' => $pricing->base_price * $persons,
            'per_table' => $pricing->base_price,
            'fixed' => $pricing->base_price,
            default => 0,
        };

        // Apply bulk discount
        $discountAmount = $this->calculateBulkDiscount($pricing, $quantity, $breakdown['subtotal']);

        if ($discountAmount > 0) {
            $breakdown['bulk_discount'] = [
                'amount' => $discountAmount,
                'threshold' => $pricing->bulk_discount_threshold,
                'percent' => $pricing->bulk_discount_percent,
            ];
            $breakdown['subtotal'] -= $discountAmount;
        }

        return $breakdown;
    }

    // ============================================
    // DISCOUNTS
    // ============================================

    /**
     * Calculate bulk discount (weekly, monthly, volume)
     */
    protected function calculateBulkDiscount(PriceableItem $pricing, int $quantity, float $subtotal): float
    {
        if (!$pricing->bulk_discount_threshold || !$pricing->bulk_discount_percent) {
            return 0;
        }

        if ($quantity >= $pricing->bulk_discount_threshold) {
            return $subtotal * ($pricing->bulk_discount_percent / 100);
        }

        return 0;
    }

    // ============================================
    // VALIDATION
    // ============================================

    /**
     * Validate if entity can be booked/purchased
     */
    public function validateBooking(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate,
        int $quantity,
        int $persons
    ): array {
        $errors = [];

        // Get pricing
        $pricing = PriceableItem::forEntity($entityType, $entityId)->active()->first();

        if (!$pricing) {
            return [
                'valid' => false,
                'errors' => ['Pricing not configured for this item']
            ];
        }

        // Check minimum quantity
        if ($quantity < $pricing->min_quantity) {
            $errors[] = "Minimum quantity is {$pricing->min_quantity}";
        }

        // Check maximum quantity
        if ($pricing->max_quantity && $quantity > $pricing->max_quantity) {
            $errors[] = "Maximum quantity is {$pricing->max_quantity}";
        }

        // Check availability
        $availabilityCheck = $this->availabilityService->checkAvailability(
            $entityType,
            $entityId,
            $startDate,
            $endDate
        );

        if (!$availabilityCheck['available']) {
            $errors = array_merge($errors, $availabilityCheck['reasons']);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // ============================================
    // QUICK QUOTE (Simple price estimate)
    // ============================================

    /**
     * Get quick price estimate (without full breakdown)
     */
    public function getQuickQuote(
        string $entityType,
        int $entityId,
        int $quantity
    ): array {
        $pricing = PriceableItem::forEntity($entityType, $entityId)->active()->firstOrFail();

        $baseTotal = $pricing->base_price * $quantity;
        $discount = $this->calculateBulkDiscount($pricing, $quantity, $baseTotal);
        $subtotal = $baseTotal - $discount;

        return [
            'base_price' => $pricing->base_price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'currency' => $pricing->currency,
            'formatted' => $this->formatPrice($subtotal, $pricing->currency),
        ];
    }

    // ============================================
    // FORMATTING
    // ============================================

    /**
     * Format complete breakdown with currency
     */
    protected function formatBreakdown(array $breakdown, string $currency): array
    {
        $breakdown['subtotal_formatted'] = $this->formatPrice($breakdown['subtotal'], $currency);
        $breakdown['fees_subtotal_formatted'] = $this->formatPrice($breakdown['fees_subtotal'], $currency);
        $breakdown['subtotal_before_tax_formatted'] = $this->formatPrice($breakdown['subtotal_before_tax'], $currency);
        $breakdown['taxes_subtotal_formatted'] = $this->formatPrice($breakdown['taxes_subtotal'], $currency);
        $breakdown['total_formatted'] = $this->formatPrice($breakdown['total'], $currency);

        return $breakdown;
    }

    /**
     * Format price with currency symbol
     */
    public function formatPrice(float $amount, string $currency): string
    {
        $symbol = match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'RSD' => 'дин',
            default => $currency,
        };

        return $symbol . number_format($amount, 2);
    }

    // ============================================
    // PRICING MANAGEMENT (CRUD)
    // ============================================

    /**
     * Create pricing for entity
     */
    public function createPricing(string $entityType, string $entityId, array $data): PriceableItem
    {
        DB::beginTransaction();
        try {
            $pricing = PriceableItem::create([
                'priceable_type' => $entityType,
                'priceable_id' => $entityId,
                ...$data
            ]);

            // Log change
            $this->logPricingChange($entityType, $entityId, 'base_price', null, $pricing->toArray());

            DB::commit();
            return $pricing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update pricing
     */
    public function updatePricing(int $pricingId, array $data): PriceableItem
    {
        DB::beginTransaction();
        try {
            $pricing = PriceableItem::findOrFail($pricingId);
            $oldValues = $pricing->toArray();

            $pricing->update($data);

            // Log change
            $this->logPricingChange(
                $pricing->priceable_type,
                $pricing->priceable_id,
                'base_price',
                $oldValues,
                $pricing->toArray()
            );

            DB::commit();
            return $pricing->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ============================================
    // PRICING PERIODS MANAGEMENT
    // ============================================

    /**
     * Add pricing period (seasonal, special date, etc.)
     */
    public function addPricingPeriod(string $entityType, int $entityId, array $data): PricingPeriod
    {
        DB::beginTransaction();
        try {
            $period = PricingPeriod::create([
                'priceable_type' => $entityType,
                'priceable_id' => $entityId,
                ...$data
            ]);

            // Log change
            $this->logPricingChange($entityType, $entityId, 'period_pricing', null, $period->toArray());

            DB::commit();
            return $period;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update pricing period
     */
    public function updatePricingPeriod(int $periodId, array $data): PricingPeriod
    {
        DB::beginTransaction();
        try {
            $period = PricingPeriod::findOrFail($periodId);
            $oldValues = $period->toArray();

            $period->update($data);

            $this->logPricingChange(
                $period->priceable_type,
                $period->priceable_id,
                'period_pricing',
                $oldValues,
                $period->toArray()
            );

            DB::commit();
            return $period->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete pricing period
     */
    public function deletePricingPeriod(int $periodId): bool
    {
        DB::beginTransaction();
        try {
            $period = PricingPeriod::findOrFail($periodId);

            $this->logPricingChange(
                $period->priceable_type,
                $period->priceable_id,
                'period_pricing',
                $period->toArray(),
                null
            );

            $deleted = $period->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ============================================
    // PRICING HISTORY
    // ============================================

    /**
     * Log pricing change
     */
    protected function logPricingChange(
        string $entityType,
        string $entityId,
        string $changeType,
        ?array $oldValues,
        ?array $newValues
    ): void {
        PricingHistory::create([
            'priceable_type' => $entityType,
            'priceable_id' => $entityId,
            'change_type' => $changeType,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by_user_id' => auth()->id(),
            'change_source' => 'manual',
            'changed_from_ip' => request()->ip(),
            'changed_at' => now(),
            'can_rollback' => true,
        ]);
    }

    /**
     * Get pricing history
     */
    public function getPricingHistory(string $entityType, int $entityId): \Illuminate\Database\Eloquent\Collection
    {
        return PricingHistory::forEntity($entityType, $entityId)
            ->orderBy('changed_at', 'desc')
            ->get();
    }
}

<?php

namespace App\Services;

use App\Models\TaxRate;
use App\Models\EntityTax;
use App\Models\PricingHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TaxService
 *
 * ALL tax calculation and management logic
 * Works for ANY entity (Accommodation, Restaurant, Service, Event, etc.)
 */
class TaxService
{
    // ============================================
    // TAX CALCULATION
    // ============================================

    /**
     * Calculate all applicable taxes
     *
     * @param string $entityType
     * @param string $entityId
     * @param float $subtotal - base subtotal before fees
     * @param float $feesTotal - total fees amount
     * @param int $quantity - nights, hours, items
     * @param int $persons
     * @param array $guestAges - for age-based exemptions
     * @return array - tax breakdown
     */
    public function calculateAllTaxes(
        string $entityType,
        string $entityId,
        float $subtotal,
        float $feesTotal = 0,
        int $quantity = 1,
        int $persons = 1,
        array $guestAges = []
    ): array {
        // Get all assigned taxes for this entity
        $entityTaxes = EntityTax::forEntity($entityType, $entityId)
            ->active()
            ->notExempt()
            ->with('taxRate')
            ->get();

        $taxes = [];

        foreach ($entityTaxes as $entityTax) {
            $taxRate = $entityTax->taxRate;

            // Check if tax is currently effective
            if (!$this->isTaxEffective($taxRate)) {
                continue;
            }

            // Calculate tax amount
            $amount = $this->calculateTaxAmount(
                $taxRate,
                $entityTax,
                $subtotal,
                $feesTotal,
                $quantity,
                $persons,
                $guestAges
            );

            if ($amount > 0) {
                $taxes[] = [
                    'id' => $taxRate->id,
                    'name' => $taxRate->tax_name,
                    'type' => $taxRate->tax_type,
                    'rate' => $taxRate->rate_percent ?? null,
                    'amount' => $amount,
                    'included_in_price' => $entityTax->use_override
                        ? $entityTax->override_included_in_price
                        : $taxRate->included_in_price,
                    'location' => $taxRate->location_description,
                ];
            }
        }

        return $taxes;
    }

    /**
     * Calculate single tax amount
     */
    protected function calculateTaxAmount(
        TaxRate $taxRate,
        EntityTax $entityTax,
        float $subtotal,
        float $feesTotal,
        int $quantity,
        int $persons,
        array $guestAges
    ): float {
        // Use overrides if set
        $ratePercent = $entityTax->use_override && $entityTax->override_rate_percent
            ? $entityTax->override_rate_percent
            : $taxRate->rate_percent;

        $flatAmount = $entityTax->use_override && $entityTax->override_flat_amount
            ? $entityTax->override_flat_amount
            : $taxRate->flat_amount;

        $calculationBasis = $entityTax->use_override && $entityTax->override_calculation_basis
            ? $entityTax->override_calculation_basis
            : $taxRate->calculation_basis;

        // Calculate based on calculation basis
        return match ($calculationBasis) {
            'subtotal_only' => $this->calculatePercentageTax($ratePercent, $subtotal),
            'subtotal_and_fees' => $this->calculatePercentageTax($ratePercent, $subtotal + $feesTotal),
            'per_unit' => $this->calculatePerUnitTax($flatAmount, $quantity, $taxRate->max_units),
            'per_person_per_unit' => $this->calculatePerPersonPerUnitTax(
                $flatAmount,
                $quantity,
                $persons,
                $guestAges,
                $taxRate->min_age,
                $taxRate->max_age,
                $taxRate->max_units
            ),
            default => 0,
        };
    }

    /**
     * Calculate percentage-based tax
     */
    protected function calculatePercentageTax(?float $ratePercent, float $basis): float
    {
        if (!$ratePercent) {
            return 0;
        }

        return $basis * ($ratePercent / 100);
    }

    /**
     * Calculate per-unit flat tax
     */
    protected function calculatePerUnitTax(?float $flatAmount, int $quantity, ?int $maxUnits): float
    {
        if (!$flatAmount) {
            return 0;
        }

        // Cap at max units if specified
        $applicableUnits = $maxUnits ? min($quantity, $maxUnits) : $quantity;

        return $flatAmount * $applicableUnits;
    }

    /**
     * Calculate per-person per-unit flat tax (tourist tax)
     */
    protected function calculatePerPersonPerUnitTax(
        ?float $flatAmount,
        int $quantity,
        int $persons,
        array $guestAges,
        ?int $minAge,
        ?int $maxAge,
        ?int $maxUnits
    ): float {
        if (!$flatAmount) {
            return 0;
        }

        // Count applicable persons based on age restrictions
        $applicablePersons = $this->countApplicablePersons($persons, $guestAges, $minAge, $maxAge);

        // Cap at max units if specified
        $applicableUnits = $maxUnits ? min($quantity, $maxUnits) : $quantity;

        return $flatAmount * $applicablePersons * $applicableUnits;
    }

    /**
     * Count persons subject to tax based on age restrictions
     */
    protected function countApplicablePersons(
        int $totalPersons,
        array $guestAges,
        ?int $minAge,
        ?int $maxAge
    ): int {
        // If no ages provided, assume all adults
        if (empty($guestAges)) {
            return $totalPersons;
        }

        $count = 0;

        foreach ($guestAges as $age) {
            // Check minimum age
            if ($minAge && $age < $minAge) {
                continue;
            }

            // Check maximum age
            if ($maxAge && $age > $maxAge) {
                continue;
            }

            $count++;
        }

        return $count;
    }

    /**
     * Check if tax is currently effective
     */
    protected function isTaxEffective(TaxRate $taxRate, ?Carbon $date = null): bool
    {
        $date = $date ?? now();

        if ($date->lt($taxRate->effective_from)) {
            return false;
        }

        if ($taxRate->effective_until && $date->gt($taxRate->effective_until)) {
            return false;
        }

        return $taxRate->is_active;
    }

    // ============================================
    // TAX ASSIGNMENT
    // ============================================

    /**
     * Auto-assign taxes to entity based on location
     */
    public function assignTaxesByLocation(
        string $entityType,
        string $entityId,
        string $countryCode,
        ?string $regionCode = null,
        ?string $city = null
    ): array {
        DB::beginTransaction();
        try {
            // Get applicable tax rates for location
            $taxRates = TaxRate::forLocation($countryCode, $regionCode, $city)
                ->active()
                ->effectiveOn(now())
                ->applicableTo($this->getEntityTypeShortName($entityType))
                ->get();

            $assigned = [];

            foreach ($taxRates as $taxRate) {
                // Check if already assigned
                $exists = EntityTax::forEntity($entityType, $entityId)
                    ->where('tax_rate_id', $taxRate->id)
                    ->exists();

                if (!$exists) {
                    $entityTax = EntityTax::create([
                        'taxable_type' => $entityType,
                        'taxable_id' => $entityId,
                        'tax_rate_id' => $taxRate->id,
                        'is_active' => true,
                    ]);

                    $assigned[] = $entityTax;

                    // Log change
                    $this->logTaxChange($entityType, $entityId, null, $entityTax->toArray());
                }
            }

            DB::commit();
            return $assigned;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Manually assign tax to entity
     */
    public function assignTax(
        string $entityType,
        string $entityId,
        int $taxRateId,
        array $overrides = []
    ): EntityTax {
        DB::beginTransaction();
        try {
            $entityTax = EntityTax::create([
                'taxable_type' => $entityType,
                'taxable_id' => $entityId,
                'tax_rate_id' => $taxRateId,
                ...$overrides,
                'is_active' => true,
            ]);

            // Log change
            $this->logTaxChange($entityType, $entityId, null, $entityTax->toArray());

            DB::commit();
            return $entityTax;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update tax assignment
     */
    public function updateTaxAssignment(int $entityTaxId, array $data): EntityTax
    {
        DB::beginTransaction();
        try {
            $entityTax = EntityTax::findOrFail($entityTaxId);
            $oldValues = $entityTax->toArray();

            $entityTax->update($data);

            // Log change
            $this->logTaxChange(
                $entityTax->taxable_type,
                $entityTax->taxable_id,
                $oldValues,
                $entityTax->toArray()
            );

            DB::commit();
            return $entityTax->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove tax assignment
     */
    public function removeTaxAssignment(int $entityTaxId): bool
    {
        DB::beginTransaction();
        try {
            $entityTax = EntityTax::findOrFail($entityTaxId);

            // Log change
            $this->logTaxChange(
                $entityTax->taxable_type,
                $entityTax->taxable_id,
                $entityTax->toArray(),
                null
            );

            $deleted = $entityTax->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Set tax exemption
     */
    public function setTaxExemption(
        int $entityTaxId,
        string $reason,
        ?string $certificateNumber = null,
        ?Carbon $validUntil = null
    ): EntityTax {
        return $this->updateTaxAssignment($entityTaxId, [
            'is_exempt' => true,
            'exemption_reason' => $reason,
            'exemption_certificate' => $certificateNumber,
            'exemption_valid_until' => $validUntil,
        ]);
    }

    // ============================================
    // TAX RATE MANAGEMENT
    // ============================================

    /**
     * Get all tax rates for location
     */
    public function getTaxRatesForLocation(
        string $countryCode,
        ?string $regionCode = null,
        ?string $city = null
    ): \Illuminate\Database\Eloquent\Collection {
        return TaxRate::forLocation($countryCode, $regionCode, $city)
            ->active()
            ->effectiveOn(now())
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get entity's assigned taxes with details
     */
    public function getEntityTaxes(string $entityType, string $entityId): array
    {
        $entityTaxes = EntityTax::forEntity($entityType, $entityId)
            ->active()
            ->with('taxRate')
            ->get();

        return $entityTaxes->map(function ($entityTax) {
            $taxRate = $entityTax->taxRate;

            return [
                'entity_tax_id' => $entityTax->id,
                'tax_rate_id' => $taxRate->id,
                'name' => $taxRate->tax_name,
                'type' => $taxRate->tax_type,
                'rate' => $taxRate->rate_percent ?? $taxRate->flat_amount,
                'rate_type' => $taxRate->rate_percent ? 'percentage' : 'flat',
                'included_in_price' => $entityTax->use_override
                    ? $entityTax->override_included_in_price
                    : $taxRate->included_in_price,
                'is_exempt' => $entityTax->is_exempt,
                'exemption_reason' => $entityTax->exemption_reason,
                'location' => $taxRate->location_description,
            ];
        })->toArray();
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Get entity type short name for tax applicability check
     */
    protected function getEntityTypeShortName(string $entityType): string
    {
        return match ($entityType) {
            'App\Models\Accommodation' => 'accommodation',
            'App\Models\Restaurant' => 'restaurant',
            'App\Models\Service' => 'service',
            'App\Models\Event' => 'event',
            default => strtolower(class_basename($entityType)),
        };
    }

    /**
     * Calculate total taxes amount
     */
    public function getTotalTaxesAmount(array $taxes): float
    {
        return collect($taxes)->sum('amount');
    }

    /**
     * Get included vs added taxes breakdown
     */
    public function getTaxesBreakdown(array $taxes): array
    {
        $included = collect($taxes)->where('included_in_price', true);
        $added = collect($taxes)->where('included_in_price', false);

        return [
            'included' => [
                'taxes' => $included->values()->toArray(),
                'total' => $included->sum('amount'),
            ],
            'added' => [
                'taxes' => $added->values()->toArray(),
                'total' => $added->sum('amount'),
            ],
        ];
    }

    // ============================================
    // TAX HISTORY
    // ============================================

    /**
     * Log tax change
     */
    protected function logTaxChange(
        string $entityType,
        string $entityId,
        ?array $oldValues,
        ?array $newValues
    ): void {
        PricingHistory::create([
            'priceable_type' => $entityType,
            'priceable_id' => $entityId,
            'change_type' => 'tax',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by_user_id' => auth()->id(),
            'change_source' => 'manual',
            'changed_from_ip' => request()->ip(),
            'changed_at' => now(),
            'can_rollback' => true,
        ]);
    }
}

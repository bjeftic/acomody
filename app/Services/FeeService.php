<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\PricingHistory;
use Illuminate\Support\Facades\DB;

/**
 * FeeService
 *
 * ALL fee calculation and management logic
 * Works for ANY entity (Accommodation, Restaurant, Service, Event, etc.)
 */
class FeeService
{
    // ============================================
    // FEE CALCULATION
    // ============================================

    /**
     * Calculate all fees (mandatory + optional)
     *
     * @param string $entityType
     * @param string $entityId
     * @param float $subtotal - base subtotal before fees
     * @param int $quantity - nights, hours, items
     * @param int $persons
     * @param array $optionalFeeIds - user-selected optional fees
     * @return array - ['mandatory' => [...], 'optional' => [...]]
     */
    public function calculateAllFees(
        string $entityType,
        string $entityId,
        float $subtotal,
        int $quantity = 1,
        int $persons = 1,
        array $optionalFeeIds = []
    ): array {
        // Get all active fees
        $fees = Fee::forEntity($entityType, $entityId)
            ->active()
            ->orderByDisplay()
            ->get();

        $mandatoryFees = [];
        $optionalFees = [];
        $feesTotal = 0; // Running total for percentage-based fees

        foreach ($fees as $fee) {
            // Skip optional fees not selected by user
            if (!$fee->mandatory && !in_array($fee->id, $optionalFeeIds)) {
                continue;
            }

            // Calculate fee amount
            $amount = $this->calculateFeeAmount($fee, [
                'subtotal' => $subtotal,
                'fees_total' => $feesTotal,
                'quantity' => $quantity,
                'persons' => $persons,
            ]);

            // Build fee data
            $feeData = [
                'id' => $fee->id,
                'name' => $fee->display_name,
                'description' => $fee->description,
                'amount' => $amount,
                'charge_type' => $fee->charge_type,
                'is_taxable' => $fee->is_taxable,
                'is_refundable' => $fee->is_refundable,
            ];

            // Add to appropriate array
            if ($fee->mandatory) {
                $mandatoryFees[] = $feeData;
            } else {
                $optionalFees[] = $feeData;
            }

            $feesTotal += $amount;
        }

        return [
            'mandatory' => $mandatoryFees,
            'optional' => $optionalFees,
        ];
    }

    /**
     * Calculate single fee amount
     */
    public function calculateFeeAmount(Fee $fee, array $params): float
    {
        $quantity = $params['quantity'] ?? 1;
        $persons = $params['persons'] ?? 1;
        $subtotal = $params['subtotal'] ?? 0;
        $feesTotal = $params['fees_total'] ?? 0;

        // Check if fee should apply based on conditions
        if (!$this->shouldApplyFee($fee, $quantity, $persons, $subtotal)) {
            return 0;
        }

        // Calculate based on charge type
        return match ($fee->charge_type) {
            'per_unit' => $this->calculatePerUnit($fee, $quantity),
            'per_booking' => $this->calculatePerBooking($fee),
            'per_person' => $this->calculatePerPerson($fee, $persons),
            'per_person_per_unit' => $this->calculatePerPersonPerUnit($fee, $persons, $quantity),
            'percentage' => $this->calculatePercentage($fee, $subtotal, $feesTotal),
            default => 0,
        };
    }

    /**
     * Check if fee should apply based on conditions
     */
    protected function shouldApplyFee(Fee $fee, int $quantity, int $persons, float $subtotal): bool
    {
        // Check quantity threshold
        if ($fee->applies_after_quantity && $quantity <= $fee->applies_after_quantity) {
            return false;
        }

        // Check persons threshold
        if ($fee->applies_after_persons && $persons <= $fee->applies_after_persons) {
            return false;
        }

        // Check amount threshold
        if ($fee->applies_after_amount && $subtotal <= $fee->applies_after_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate per unit fee (per night, per hour, per item)
     */
    protected function calculatePerUnit(Fee $fee, int $quantity): float
    {
        return $fee->amount * $quantity;
    }

    /**
     * Calculate per booking fee (one-time)
     */
    protected function calculatePerBooking(Fee $fee): float
    {
        return $fee->amount;
    }

    /**
     * Calculate per person fee
     */
    protected function calculatePerPerson(Fee $fee, int $persons): float
    {
        return $fee->amount * $persons;
    }

    /**
     * Calculate per person per unit fee
     */
    protected function calculatePerPersonPerUnit(Fee $fee, int $persons, int $quantity): float
    {
        return $fee->amount * $persons * $quantity;
    }

    /**
     * Calculate percentage fee
     */
    protected function calculatePercentage(Fee $fee, float $subtotal, float $feesTotal): float
    {
        if (!$fee->percentage_rate) {
            return 0;
        }

        // Determine basis for percentage calculation
        $basis = match ($fee->percentage_basis) {
            'subtotal' => $subtotal,
            'subtotal_and_fees' => $subtotal + $feesTotal,
            'total' => $subtotal + $feesTotal, // Taxes added later
            default => $subtotal,
        };

        return $basis * ($fee->percentage_rate / 100);
    }

    // ============================================
    // REFUND CALCULATION
    // ============================================

    /**
     * Calculate refundable fees
     */
    public function calculateRefundableFees(
        string $entityType,
        string $entityId,
        array $paidFees
    ): array {
        $refunds = [];
        $totalRefund = 0;

        foreach ($paidFees as $paidFee) {
            $fee = Fee::find($paidFee['fee_id']);

            if (!$fee || !$fee->is_refundable) {
                continue;
            }

            $refundAmount = $this->calculateRefundAmount($fee, $paidFee['amount_paid']);

            if ($refundAmount > 0) {
                $refunds[] = [
                    'fee_id' => $fee->id,
                    'fee_name' => $fee->display_name,
                    'amount_paid' => $paidFee['amount_paid'],
                    'refund_amount' => $refundAmount,
                    'refund_percentage' => $fee->refund_percentage ?? 100,
                ];

                $totalRefund += $refundAmount;
            }
        }

        return [
            'refunds' => $refunds,
            'total_refund' => $totalRefund,
        ];
    }

    /**
     * Calculate refund amount for single fee
     */
    protected function calculateRefundAmount(Fee $fee, float $originalAmount): float
    {
        if (!$fee->is_refundable) {
            return 0;
        }

        if ($fee->refund_percentage) {
            return $originalAmount * ($fee->refund_percentage / 100);
        }

        return $originalAmount; // Full refund
    }

    // ============================================
    // FEE MANAGEMENT (CRUD)
    // ============================================

    /**
     * Create fee for entity
     */
    public function createFee(string $entityType, string $entityId, array $data): Fee
    {
        DB::beginTransaction();
        try {
            $fee = Fee::create([
                'feeable_type' => $entityType,
                'feeable_id' => $entityId,
                ...$data
            ]);

            // Log change
            $this->logFeeChange($entityType, $entityId, null, $fee->toArray());

            DB::commit();
            return $fee;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update fee
     */
    public function updateFee(int $feeId, array $data): Fee
    {
        DB::beginTransaction();
        try {
            $fee = Fee::findOrFail($feeId);
            $oldValues = $fee->toArray();

            $fee->update($data);

            // Log change
            $this->logFeeChange($fee->feeable_type, $fee->feeable_id, $oldValues, $fee->toArray());

            DB::commit();
            return $fee->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete fee
     */
    public function deleteFee(int $feeId): bool
    {
        DB::beginTransaction();
        try {
            $fee = Fee::findOrFail($feeId);

            // Log change
            $this->logFeeChange($fee->feeable_type, $fee->feeable_id, $fee->toArray(), null);

            $deleted = $fee->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk create fees
     */
    public function bulkCreateFees(string $entityType, string $entityId, array $feesData): array
    {
        DB::beginTransaction();
        try {
            $createdFees = [];

            foreach ($feesData as $feeData) {
                $createdFees[] = $this->createFee($entityType, $entityId, $feeData);
            }

            DB::commit();
            return $createdFees;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ============================================
    // PRESET FEES (Quick Setup)
    // ============================================

    /**
     * Get common preset fees by entity type
     */
    public function getPresetFees(string $entityType): array
    {
        return match ($entityType) {
            'App\Models\Accommodation' => $this->getAccommodationPresets(),
            'App\Models\Restaurant' => $this->getRestaurantPresets(),
            'App\Models\Service' => $this->getServicePresets(),
            default => [],
        };
    }

    /**
     * Accommodation preset fees
     */
    protected function getAccommodationPresets(): array
    {
        return [
            [
                'name' => 'Cleaning Fee',
                'fee_type' => 'cleaning',
                'amount' => 30.00,
                'charge_type' => 'per_booking',
                'mandatory' => true,
                'is_taxable' => true,
            ],
            [
                'name' => 'Extra Guest Fee',
                'fee_type' => 'extra_guest',
                'amount' => 10.00,
                'charge_type' => 'per_person_per_unit',
                'applies_after_persons' => 2,
                'mandatory' => true,
                'is_taxable' => true,
            ],
            [
                'name' => 'Pet Fee',
                'fee_type' => 'pet',
                'amount' => 20.00,
                'charge_type' => 'per_booking',
                'mandatory' => false,
                'is_taxable' => true,
            ],
        ];
    }

    /**
     * Restaurant preset fees
     */
    protected function getRestaurantPresets(): array
    {
        return [
            [
                'name' => 'Service Charge',
                'fee_type' => 'service_charge',
                'charge_type' => 'percentage',
                'percentage_rate' => 10.00,
                'percentage_basis' => 'subtotal',
                'mandatory' => true,
                'is_taxable' => false,
            ],
            [
                'name' => 'Corkage Fee',
                'fee_type' => 'corkage',
                'amount' => 15.00,
                'charge_type' => 'per_booking',
                'mandatory' => false,
                'is_taxable' => true,
            ],
        ];
    }

    /**
     * Service preset fees
     */
    protected function getServicePresets(): array
    {
        return [
            [
                'name' => 'Booking Fee',
                'fee_type' => 'booking',
                'amount' => 5.00,
                'charge_type' => 'per_booking',
                'mandatory' => true,
                'is_taxable' => true,
            ],
            [
                'name' => 'Equipment Fee',
                'fee_type' => 'equipment',
                'amount' => 50.00,
                'charge_type' => 'per_booking',
                'mandatory' => false,
                'is_taxable' => true,
            ],
        ];
    }

    // ============================================
    // FEE HISTORY
    // ============================================

    /**
     * Log fee change
     */
    protected function logFeeChange(
        string $entityType,
        string $entityId,
        ?array $oldValues,
        ?array $newValues
    ): void {
        PricingHistory::create([
            'priceable_type' => $entityType,
            'priceable_id' => $entityId,
            'change_type' => 'fee',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by_user_id' => auth()->id(),
            'change_source' => 'manual',
            'changed_from_ip' => request()->ip(),
            'changed_at' => now(),
            'can_rollback' => true,
        ]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Get all fees for entity
     */
    public function getEntityFees(string $entityType, string $entityId): array
    {
        $fees = Fee::forEntity($entityType, $entityId)
            ->active()
            ->orderByDisplay()
            ->get();

        return [
            'mandatory' => $fees->where('mandatory', true)->values(),
            'optional' => $fees->where('mandatory', false)->values(),
        ];
    }

    /**
     * Get taxable fees total
     */
    public function getTaxableFeesTotal(array $fees): float
    {
        $total = 0;

        foreach ($fees['mandatory'] ?? [] as $fee) {
            if ($fee['is_taxable'] ?? false) {
                $total += $fee['amount'];
            }
        }

        foreach ($fees['optional'] ?? [] as $fee) {
            if ($fee['is_taxable'] ?? false) {
                $total += $fee['amount'];
            }
        }

        return $total;
    }
}

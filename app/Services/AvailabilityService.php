<?php

namespace App\Services;

use App\Models\AvailabilityPeriod;
use App\Models\PricingHistory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * AvailabilityService
 *
 * ALL availability/blocking logic
 * Works for ANY entity (Accommodation, Restaurant, Service, Event, etc.)
 */
class AvailabilityService
{
    // ============================================
    // AVAILABILITY CHECK
    // ============================================

    /**
     * Check if entity is available for date range
     *
     * @param string $entityType
     * @param int $entityId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array - ['available' => bool, 'reasons' => array]
     */
    public function checkAvailability(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        // Get all periods that overlap with requested range
        $periods = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->forDateRange($startDate, $endDate)
            ->get();

        $blockedDates = [];
        $reasons = [];

        foreach ($periods as $period) {
            // Skip if explicitly available
            if ($period->status === 'available') {
                continue;
            }

            // Check if period blocks the requested dates
            if ($this->periodsOverlap($startDate, $endDate, $period->start_date, $period->end_date)) {
                $blockedDates[] = [
                    'start' => $period->start_date->format('Y-m-d'),
                    'end' => $period->end_date->format('Y-m-d'),
                    'status' => $period->status,
                    'reason' => $period->reason,
                ];

                $reasons[] = $this->getBlockedReason($period);
            }
        }

        return [
            'available' => empty($blockedDates),
            'blocked_dates' => $blockedDates,
            'reasons' => array_unique($reasons),
        ];
    }

    /**
     * Get available dates in range
     */
    public function getAvailableDates(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $period = CarbonPeriod::create($startDate, $endDate);
        $availableDates = [];

        foreach ($period as $date) {
            if ($this->isDateAvailable($entityType, $entityId, $date)) {
                $availableDates[] = $date->format('Y-m-d');
            }
        }

        return $availableDates;
    }

    /**
     * Check if single date is available
     */
    public function isDateAvailable(
        string $entityType,
        int $entityId,
        Carbon $date
    ): bool {
        $blockedPeriods = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->whereIn('status', ['blocked', 'booked', 'closed', 'sold_out'])
            ->forDate($date)
            ->exists();

        return !$blockedPeriods;
    }

    /**
     * Get blocked dates in range
     */
    public function getBlockedDates(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $periods = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->whereIn('status', ['blocked', 'booked', 'closed', 'sold_out'])
            ->forDateRange($startDate, $endDate)
            ->get();

        $blockedDates = [];

        foreach ($periods as $period) {
            $periodRange = CarbonPeriod::create($period->start_date, $period->end_date);

            foreach ($periodRange as $date) {
                if ($date->between($startDate, $endDate)) {
                    $blockedDates[] = [
                        'date' => $date->format('Y-m-d'),
                        'status' => $period->status,
                        'reason' => $period->reason_label,
                    ];
                }
            }
        }

        return $blockedDates;
    }

    // ============================================
    // BLOCKING/UNBLOCKING
    // ============================================

    /**
     * Block dates
     */
    public function blockDates(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate,
        string $reason = 'owner_blocked',
        ?string $notes = null
    ): AvailabilityPeriod {
        DB::beginTransaction();
        try {
            // Check for existing periods that overlap
            $this->removeOverlappingPeriods($entityType, $entityId, $startDate, $endDate);

            $period = AvailabilityPeriod::create([
                'available_type' => $entityType,
                'available_id' => $entityId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'blocked',
                'reason' => $reason,
                'notes' => $notes,
            ]);

            // Log change
            $this->logAvailabilityChange($entityType, $entityId, null, $period->toArray());

            DB::commit();
            return $period;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Unblock dates
     */
    public function unblockDates(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): bool {
        DB::beginTransaction();
        try {
            $periods = AvailabilityPeriod::forEntity($entityType, $entityId)
                ->forDateRange($startDate, $endDate)
                ->whereIn('status', ['blocked'])
                ->get();

            foreach ($periods as $period) {
                // Log change
                $this->logAvailabilityChange($entityType, $entityId, $period->toArray(), null);

                $period->delete();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark dates as booked
     */
    public function markAsBooked(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate,
        ?string $notes = null
    ): AvailabilityPeriod {
        DB::beginTransaction();
        try {
            $this->removeOverlappingPeriods($entityType, $entityId, $startDate, $endDate);

            $period = AvailabilityPeriod::create([
                'available_type' => $entityType,
                'available_id' => $entityId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'booked',
                'reason' => 'booking',
                'notes' => $notes,
            ]);

            // Log change
            $this->logAvailabilityChange($entityType, $entityId, null, $period->toArray());

            DB::commit();
            return $period;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark dates as available (override blocks)
     */
    public function markAsAvailable(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): AvailabilityPeriod {
        DB::beginTransaction();
        try {
            $this->removeOverlappingPeriods($entityType, $entityId, $startDate, $endDate);

            $period = AvailabilityPeriod::create([
                'available_type' => $entityType,
                'available_id' => $entityId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'available',
                'reason' => null,
            ]);

            // Log change
            $this->logAvailabilityChange($entityType, $entityId, null, $period->toArray());

            DB::commit();
            return $period;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ============================================
    // RECURRING AVAILABILITY
    // ============================================

    /**
     * Set recurring closed days (e.g., restaurant closed on Mondays)
     */
    public function setRecurringClosed(
        string $entityType,
        int $entityId,
        array $days,
        Carbon $startDate,
        Carbon $endDate,
        string $reason = 'closed_day'
    ): AvailabilityPeriod {
        DB::beginTransaction();
        try {
            $period = AvailabilityPeriod::create([
                'available_type' => $entityType,
                'available_id' => $entityId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'recurring_days' => $days,
                'status' => 'closed',
                'reason' => $reason,
            ]);

            // Log change
            $this->logAvailabilityChange($entityType, $entityId, null, $period->toArray());

            DB::commit();
            return $period;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ============================================
    // CAPACITY MANAGEMENT (Restaurants, Events)
    // ============================================

    /**
     * Set capacity for date
     */
    public function setCapacity(
        string $entityType,
        int $entityId,
        Carbon $date,
        int $maxCapacity
    ): AvailabilityPeriod {
        DB::beginTransaction();
        try {
            // Check if capacity already exists for this date
            $period = AvailabilityPeriod::forEntity($entityType, $entityId)
                ->forDate($date)
                ->first();

            if ($period) {
                $period->update(['max_capacity' => $maxCapacity]);
            } else {
                $period = AvailabilityPeriod::create([
                    'available_type' => $entityType,
                    'available_id' => $entityId,
                    'start_date' => $date,
                    'end_date' => $date,
                    'status' => 'available',
                    'max_capacity' => $maxCapacity,
                    'current_bookings' => 0,
                ]);
            }

            DB::commit();
            return $period;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Increment booking count
     */
    public function incrementBookings(
        string $entityType,
        int $entityId,
        Carbon $date,
        int $count = 1
    ): bool {
        $period = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->forDate($date)
            ->first();

        if (!$period) {
            return true; // No capacity limit
        }

        if (!$period->hasCapacity()) {
            return false;
        }

        return $period->incrementBookings($count);
    }

    /**
     * Decrement booking count
     */
    public function decrementBookings(
        string $entityType,
        int $entityId,
        Carbon $date,
        int $count = 1
    ): bool {
        $period = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->forDate($date)
            ->first();

        if (!$period) {
            return true;
        }

        return $period->decrementBookings($count);
    }

    /**
     * Check if has capacity
     */
    public function hasCapacity(
        string $entityType,
        int $entityId,
        Carbon $date
    ): bool {
        $period = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->forDate($date)
            ->first();

        if (!$period || !$period->max_capacity) {
            return true; // No limit
        }

        return $period->hasCapacity();
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if two date ranges overlap
     */
    protected function periodsOverlap(
        Carbon $start1,
        Carbon $end1,
        Carbon $start2,
        Carbon $end2
    ): bool {
        return $start1->lte($end2) && $end1->gte($start2);
    }

    /**
     * Remove overlapping periods before creating new one
     */
    protected function removeOverlappingPeriods(
        string $entityType,
        int $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        AvailabilityPeriod::forEntity($entityType, $entityId)
            ->forDateRange($startDate, $endDate)
            ->delete();
    }

    /**
     * Get human-readable blocked reason
     */
    protected function getBlockedReason(AvailabilityPeriod $period): string
    {
        return match ($period->status) {
            'blocked' => "Blocked: {$period->reason_label}",
            'booked' => "Already booked",
            'closed' => "Closed: {$period->reason_label}",
            'maintenance' => "Under maintenance",
            'sold_out' => "Sold out",
            default => "Not available",
        };
    }

    /**
     * Get availability calendar for month
     */
    public function getMonthlyCalendar(
        string $entityType,
        int $entityId,
        int $year,
        int $month
    ): array {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $periods = AvailabilityPeriod::forEntity($entityType, $entityId)
            ->forDateRange($startDate, $endDate)
            ->get();

        $calendar = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $calendar[$dateStr] = [
                'date' => $dateStr,
                'status' => 'available',
                'reason' => null,
            ];

            // Check if date is blocked
            foreach ($periods as $availPeriod) {
                if ($date->between($availPeriod->start_date, $availPeriod->end_date)) {
                    $calendar[$dateStr] = [
                        'date' => $dateStr,
                        'status' => $availPeriod->status,
                        'reason' => $availPeriod->reason_label,
                    ];
                    break;
                }
            }
        }

        return array_values($calendar);
    }

    // ============================================
    // AVAILABILITY HISTORY
    // ============================================

    /**
     * Log availability change
     */
    protected function logAvailabilityChange(
        string $entityType,
        int $entityId,
        ?array $oldValues,
        ?array $newValues
    ): void {
        PricingHistory::create([
            'priceable_type' => $entityType,
            'priceable_id' => $entityId,
            'change_type' => 'availability',
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

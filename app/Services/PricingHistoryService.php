<?php

namespace App\Services;

use App\Models\PricingHistory;
use App\Models\PriceableItem;
use App\Models\PricingPeriod;
use App\Models\Fee;
use App\Models\EntityTax;
use App\Models\AvailabilityPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PricingHistoryService
 *
 * Audit trail and rollback functionality
 * Works for ANY entity (Accommodation, Restaurant, Service, Event, etc.)
 */
class PricingHistoryService
{
    // ============================================
    // HISTORY RETRIEVAL
    // ============================================

    /**
     * Get complete pricing history for entity
     */
    public function getEntityHistory(
        string $entityType,
        string $entityId,
        ?int $limit = null
    ): \Illuminate\Database\Eloquent\Collection {
        $query = PricingHistory::forEntity($entityType, $entityId)
            ->with(['changedByUser', 'rolledBackByUser'])
            ->orderBy('changed_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get history by change type
     */
    public function getHistoryByType(
        string $entityType,
        string $entityId,
        string $changeType
    ): \Illuminate\Database\Eloquent\Collection {
        return PricingHistory::forEntity($entityType, $entityId)
            ->byType($changeType)
            ->with(['changedByUser'])
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Get history by user
     */
    public function getHistoryByUser(
        string $entityType,
        string $entityId,
        int $userId
    ): \Illuminate\Database\Eloquent\Collection {
        return PricingHistory::forEntity($entityType, $entityId)
            ->byUser($userId)
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Get history in date range
     */
    public function getHistoryInRange(
        string $entityType,
        string $entityId,
        Carbon $startDate,
        Carbon $endDate
    ): \Illuminate\Database\Eloquent\Collection {
        return PricingHistory::forEntity($entityType, $entityId)
            ->whereBetween('changed_at', [$startDate, $endDate])
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Get formatted history with details
     */
    public function getFormattedHistory(
        string $entityType,
        string $entityId
    ): array {
        $history = $this->getEntityHistory($entityType, $entityId);

        return $history->map(function ($record) {
            return [
                'id' => $record->id,
                'change_type' => $record->change_type_label,
                'field_name' => $record->field_name,
                'old_value' => $this->formatValue($record->old_values),
                'new_value' => $this->formatValue($record->new_values),
                'changed_by' => $record->changedByUser
                    ? $record->changedByUser->name
                    : $record->changed_by_system,
                'changed_at' => $record->changed_at->format('Y-m-d H:i:s'),
                'source' => $record->change_source_label,
                'reason' => $record->change_reason,
                'can_rollback' => $record->can_rollback && !$record->rolled_back_at,
                'rolled_back' => (bool) $record->rolled_back_at,
            ];
        })->toArray();
    }

    // ============================================
    // ROLLBACK FUNCTIONALITY
    // ============================================

    /**
     * Rollback a pricing change
     */
    public function rollback(int $historyId): bool
    {
        DB::beginTransaction();
        try {
            $history = PricingHistory::findOrFail($historyId);

            // Check if can rollback
            if (!$history->can_rollback) {
                throw new \Exception("This change cannot be rolled back");
            }

            if ($history->rolled_back_at) {
                throw new \Exception("This change has already been rolled back");
            }

            // Perform rollback based on change type
            $this->performRollback($history);

            // Mark as rolled back
            $history->update([
                'rolled_back_at' => now(),
                'rolled_back_by_user_id' => auth()->id(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Perform actual rollback based on change type
     */
    protected function performRollback(PricingHistory $history): void
    {
        match ($history->change_type) {
            'base_price' => $this->rollbackBasePrice($history),
            'period_pricing' => $this->rollbackPeriodPricing($history),
            'fee' => $this->rollbackFee($history),
            'tax' => $this->rollbackTax($history),
            'availability' => $this->rollbackAvailability($history),
            default => throw new \Exception("Unsupported rollback type: {$history->change_type}"),
        };
    }

    /**
     * Rollback base price change
     */
    protected function rollbackBasePrice(PricingHistory $history): void
    {
        if (!$history->old_values) {
            // Was created - delete it
            PriceableItem::forEntity($history->priceable_type, $history->priceable_id)
                ->delete();
        } else {
            // Was updated - restore old values
            PriceableItem::forEntity($history->priceable_type, $history->priceable_id)
                ->first()
                ->update($history->old_values);
        }
    }

    /**
     * Rollback pricing period change
     */
    protected function rollbackPeriodPricing(PricingHistory $history): void
    {
        if (!$history->old_values) {
            // Was created - delete it
            $periodId = $history->new_values['id'] ?? null;
            if ($periodId) {
                PricingPeriod::destroy($periodId);
            }
        } elseif (!$history->new_values) {
            // Was deleted - recreate it
            PricingPeriod::create($history->old_values);
        } else {
            // Was updated - restore old values
            $periodId = $history->new_values['id'] ?? null;
            if ($periodId) {
                PricingPeriod::find($periodId)->update($history->old_values);
            }
        }
    }

    /**
     * Rollback fee change
     */
    protected function rollbackFee(PricingHistory $history): void
    {
        if (!$history->old_values) {
            // Was created - delete it
            $feeId = $history->new_values['id'] ?? null;
            if ($feeId) {
                Fee::destroy($feeId);
            }
        } elseif (!$history->new_values) {
            // Was deleted - recreate it
            Fee::create($history->old_values);
        } else {
            // Was updated - restore old values
            $feeId = $history->new_values['id'] ?? null;
            if ($feeId) {
                Fee::find($feeId)->update($history->old_values);
            }
        }
    }

    /**
     * Rollback tax change
     */
    protected function rollbackTax(PricingHistory $history): void
    {
        if (!$history->old_values) {
            // Was created - delete it
            $entityTaxId = $history->new_values['id'] ?? null;
            if ($entityTaxId) {
                EntityTax::destroy($entityTaxId);
            }
        } elseif (!$history->new_values) {
            // Was deleted - recreate it
            EntityTax::create($history->old_values);
        } else {
            // Was updated - restore old values
            $entityTaxId = $history->new_values['id'] ?? null;
            if ($entityTaxId) {
                EntityTax::find($entityTaxId)->update($history->old_values);
            }
        }
    }

    /**
     * Rollback availability change
     */
    protected function rollbackAvailability(PricingHistory $history): void
    {
        if (!$history->old_values) {
            // Was created - delete it
            $periodId = $history->new_values['id'] ?? null;
            if ($periodId) {
                AvailabilityPeriod::destroy($periodId);
            }
        } elseif (!$history->new_values) {
            // Was deleted - recreate it
            AvailabilityPeriod::create($history->old_values);
        } else {
            // Was updated - restore old values
            $periodId = $history->new_values['id'] ?? null;
            if ($periodId) {
                AvailabilityPeriod::find($periodId)->update($history->old_values);
            }
        }
    }

    // ============================================
    // COMPARISON & DIFF
    // ============================================

    /**
     * Compare two pricing configurations
     */
    public function comparePricing(
        string $entityType,
        string $entityId,
        Carbon $date1,
        Carbon $date2
    ): array {
        $history1 = PricingHistory::forEntity($entityType, $entityId)
            ->where('changed_at', '<=', $date1)
            ->orderBy('changed_at', 'desc')
            ->first();

        $history2 = PricingHistory::forEntity($entityType, $entityId)
            ->where('changed_at', '<=', $date2)
            ->orderBy('changed_at', 'desc')
            ->first();

        return [
            'date1' => $date1->format('Y-m-d'),
            'pricing1' => $history1 ? $history1->new_values : null,
            'date2' => $date2->format('Y-m-d'),
            'pricing2' => $history2 ? $history2->new_values : null,
            'differences' => $this->calculateDifferences(
                $history1?->new_values ?? [],
                $history2?->new_values ?? []
            ),
        ];
    }

    /**
     * Calculate differences between two pricing configs
     */
    protected function calculateDifferences(array $values1, array $values2): array
    {
        $differences = [];
        $allKeys = array_unique(array_merge(array_keys($values1), array_keys($values2)));

        foreach ($allKeys as $key) {
            $val1 = $values1[$key] ?? null;
            $val2 = $values2[$key] ?? null;

            if ($val1 !== $val2) {
                $differences[$key] = [
                    'old' => $val1,
                    'new' => $val2,
                    'changed' => true,
                ];
            }
        }

        return $differences;
    }

    // ============================================
    // STATISTICS & REPORTS
    // ============================================

    /**
     * Get pricing change statistics
     */
    public function getChangeStatistics(
        string $entityType,
        string $entityId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = PricingHistory::forEntity($entityType, $entityId);

        if ($startDate) {
            $query->where('changed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('changed_at', '<=', $endDate);
        }

        $history = $query->get();

        return [
            'total_changes' => $history->count(),
            'changes_by_type' => $history->groupBy('change_type')->map->count(),
            'changes_by_user' => $history->groupBy('changed_by_user_id')->map->count(),
            'changes_by_source' => $history->groupBy('change_source')->map->count(),
            'rollbacks' => $history->where('rolled_back_at', '!=', null)->count(),
            'most_recent_change' => $history->first()?->changed_at,
            'most_active_user' => $history->groupBy('changed_by_user_id')
                ->sortByDesc->count()
                ->keys()
                ->first(),
        ];
    }

    /**
     * Get price history timeline
     */
    public function getPriceTimeline(
        string $entityType,
        string $entityId
    ): array {
        $history = PricingHistory::forEntity($entityType, $entityId)
            ->byType('base_price')
            ->orderBy('changed_at', 'asc')
            ->get();

        return $history->map(function ($record) {
            $oldPrice = $record->old_values['base_price'] ?? null;
            $newPrice = $record->new_values['base_price'] ?? null;

            return [
                'date' => $record->changed_at->format('Y-m-d'),
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'change' => $newPrice && $oldPrice ? $newPrice - $oldPrice : null,
                'change_percent' => $newPrice && $oldPrice
                    ? (($newPrice - $oldPrice) / $oldPrice) * 100
                    : null,
                'changed_by' => $record->changedByUser?->name ?? $record->changed_by_system,
            ];
        })->toArray();
    }

    // ============================================
    // BULK OPERATIONS
    // ============================================

    /**
     * Clear history older than specified date
     */
    public function clearOldHistory(Carbon $beforeDate): int
    {
        return PricingHistory::where('changed_at', '<', $beforeDate)
            ->where('rolled_back_at', null) // Don't delete if rolled back
            ->delete();
    }

    /**
     * Export history to CSV
     */
    public function exportHistory(
        string $entityType,
        string $entityId
    ): string {
        $history = $this->getFormattedHistory($entityType, $entityId);

        $csv = "Date,Change Type,Field,Old Value,New Value,Changed By,Source,Reason\n";

        foreach ($history as $record) {
            $csv .= implode(',', [
                $record['changed_at'],
                $record['change_type'],
                $record['field_name'] ?? '',
                $this->escapeCsv($record['old_value']),
                $this->escapeCsv($record['new_value']),
                $record['changed_by'],
                $record['source'],
                $this->escapeCsv($record['reason'] ?? ''),
            ]) . "\n";
        }

        return $csv;
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Format value for display
     */
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return 'N/A';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_numeric($value)) {
            return number_format($value, 2);
        }

        return (string) $value;
    }

    /**
     * Escape value for CSV
     */
    protected function escapeCsv($value): string
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }
}

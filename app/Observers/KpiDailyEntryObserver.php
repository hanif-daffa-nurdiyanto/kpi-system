<?php

namespace App\Observers;

use App\Models\KpiDailyEntry;
use App\Models\KpiEntryDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KpiDailyEntryObserver
{
    /**
     * Handle the KpiDailyEntry "created" event.
     */
    public function created(KpiDailyEntry $kpiDailyEntry): void
    {
        //
    }
    /**
     * Handle the KpiDailyEntry "updated" event.
     */
    public function updated(KpiDailyEntry $kpiDailyEntry)
    {
        $oldKpiDetails = Cache::get('old_kpi_details_entry_'.$kpiDailyEntry->id);
        $newKpiDetails = $kpiDailyEntry->kpiEntryDetails()->with('kpiMetric')->get()->map(function ($detail) {
            $array = $detail->toArray();
            $array['metric_name'] = $detail->kpiMetric->name ?? null;
            return $array;
        })->toArray();

        $attributes = $kpiDailyEntry->only([
            'user_id', 'entry_date', 'submitted_at', 'status', 'notes'
        ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kpiDailyEntry)
                ->withProperties([
                    'old' => [
                        'kpiEntryDetails' => $oldKpiDetails ?: [],
                        'attributes' => $kpiDailyEntry->getOriginal(),
                    ],
                    'new' => [
                        'kpiEntryDetails' => $newKpiDetails,
                        'attributes' => $attributes,
                    ]
                ])
                ->log('updated');
        Cache::forget('old_kpi_details_entry_'.$kpiDailyEntry->id);
    }

    /**
     * Handle the KpiDailyEntry "deleted" event.
     */
    public function deleted(KpiDailyEntry $kpiDailyEntry): void
    {
        //
    }

    /**
     * Handle the KpiDailyEntry "restored" event.
     */
    public function restored(KpiDailyEntry $kpiDailyEntry): void
    {
        //
    }

    /**
     * Handle the KpiDailyEntry "force deleted" event.
     */
    public function forceDeleted(KpiDailyEntry $kpiDailyEntry): void
    {
        //
    }
}

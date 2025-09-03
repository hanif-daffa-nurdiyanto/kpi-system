<?php

namespace App\Observers;

use App\Models\KpiEntryDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KpiEntryDetailObserver
{
    /**
     * Handle the KpiEntryDetail "created" event.
     */
    public function created(KpiEntryDetail $kpiEntryDetail): void
    {
        //
    }

    public function updating(KpiEntryDetail $kpiEntryDetail)
    {
        $entryId = $kpiEntryDetail->entry_id;
        $existing = Cache::get('old_kpi_details_entry_'.$entryId);

        if (is_null($existing)) {
            $existing = [];
            $details = KpiEntryDetail::where('entry_id', $entryId)->get();
            foreach ($details as $detail) {
                $existing[$detail->id] = [
                    'id' => $detail->id,
                    'metric_id' => $detail->metric_id,
                    'metric_name' => $detail->kpiMetric->name ?? null,
                    'value' => $detail->value,
                ];
            }
        }

        $existing[$kpiEntryDetail->id] = [
            'id' => $kpiEntryDetail->id,
            'metric_id' => $kpiEntryDetail->metric_id,
            'metric_name' => $kpiEntryDetail->kpiMetric->name ?? null,
            'value' => $kpiEntryDetail->getOriginal('value'),
            
        ];

        Cache::put('old_kpi_details_entry_'.$entryId, $existing, now()->addMinutes(10));
    }

    public function deleting(KpiEntryDetail $kpiEntryDetail)
    {
        $entryId = $kpiEntryDetail->entry_id;
        $existing = Cache::get('old_kpi_details_entry_'.$entryId);

        if (is_null($existing)) {
            $existing = [];
            $details = KpiEntryDetail::where('entry_id', $entryId)->get();
            foreach ($details as $detail) {
                $existing[$detail->id] = [
                    'id' => $detail->id,
                    'metric_id' => $detail->metric_id,
                    'metric_name' => $detail->kpiMetric->name ?? null,
                    'value' => $detail->value,
                ];
            }
        }

        $existing[$kpiEntryDetail->id] = [
            'id' => $kpiEntryDetail->id,
            'metric_id' => $kpiEntryDetail->metric_id,
            'metric_name' => $kpiEntryDetail->kpiMetric->name ?? null,
            'value' => $kpiEntryDetail->getOriginal('value'),
        ];

        Cache::put('old_kpi_details_entry_'.$entryId, $existing, now()->addMinutes(10));
    }

    /**
     * Handle the KpiEntryDetail "updated" event.
     */
    public function updated(KpiEntryDetail $kpiEntryDetail): void
    {
        //
    }

    /**
     * Handle the KpiEntryDetail "deleted" event.
     */
    public function deleted(KpiEntryDetail $kpiEntryDetail): void
    {
        //
    }

    /**
     * Handle the KpiEntryDetail "restored" event.
     */
    public function restored(KpiEntryDetail $kpiEntryDetail): void
    {
        //
    }

    /**
     * Handle the KpiEntryDetail "force deleted" event.
     */
    public function forceDeleted(KpiEntryDetail $kpiEntryDetail): void
    {
        //
    }
}

<?php

namespace App\Filament\App\Resources\KpiDailyEntryResource\Pages;

use App\Filament\App\Resources\KpiDailyEntryResource;
use App\Models\KpiEntryDetail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EditKpiDailyEntry extends EditRecord
{
    protected static string $resource = KpiDailyEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $original = $this->record->toArray();
        $formData = $this->data;

        $originalDetails = collect($original['kpi_entry_details'])
            ->mapWithKeys(fn ($item) => [$item['id'] => [
                'metric_id' => $item['metric_id'],
                'value' => $item['value'],
            ]]);

        $formDetails = collect($formData['kpi_metrics'])
            ->mapWithKeys(fn ($item, $key) => [
                (int) str_replace('record-', '', $key) => [
                    'metric_id' => $item['metric_id'],
                    'value' => $item['value'],
                ]
            ]);

        $sameMainData = collect($formData)
            ->except(['updated_at', 'created_at', 'kpi_metrics', 'parsed_data'])
            ->diffAssoc(collect($original)->except(['updated_at', 'created_at', 'kpi_entry_details']))
            ->isEmpty();
        
        $sameDetails = $originalDetails->toArray() === $formDetails->toArray();

        if ($sameMainData && $sameDetails) {
            $this->halt();
        }

        $oldKpiDetails = Cache::get('old_kpi_details_entry_'.$this->record->id);

        if (is_null($oldKpiDetails)) {
            $oldKpiDetails = [];
            $details = KpiEntryDetail::where('entry_id', $this->record->id)->get();
            foreach ($details as $detail) {
                $oldKpiDetails[$detail->id] = [
                    'id' => $detail->id,
                    'metric_id' => $detail->metric_id,
                    'metric_name' => $detail->kpiMetric->name ?? null,
                    'value' => $detail->value,
                ];
            }
            Cache::put('old_kpi_details_entry_'.$this->record->id, $oldKpiDetails, now()->addMinutes(10));
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $updatedRecord = parent::handleRecordUpdate($record, $data);
        $updatedRecord->touch();

        Cache::forget('old_kpi_details_entry_'.$record->id);
        return $updatedRecord;
    }
}

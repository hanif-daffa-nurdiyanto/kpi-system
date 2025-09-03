<?php

namespace App\Models;

use App\Models\TeamGoals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiEntryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'metric_id',
        'value',
    ];

    public function kpiDailyEntry(): BelongsTo
    {
        return $this->belongsTo(KpiDailyEntry::class, 'entry_id');
    }

    public function kpiMetric(): BelongsTo
    {
        return $this->belongsTo(KpiMetric::class, 'metric_id');
    }

    public function teamGoal()
    {
        return $this->belongsTo(TeamGoals::class, 'metric_id', 'metric_id');
    }
}

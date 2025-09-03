<?php

namespace App\Models;

use App\Models\KpiEntryDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamGoals extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'metric_id',
        'target_value',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(KpiMetric::class, 'metric_id');
    }

    public function kpiEntryDetails()
    {
        return $this->hasMany(KpiEntryDetail::class, 'metric_id', 'metric_id');
    }
}

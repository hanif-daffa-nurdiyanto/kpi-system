<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_category_id',
        'name',
        'description',
        'unit',
        'target_value',
        'weight',
        'is_higher_better',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KpiCategory::class, 'kpi_category_id');
    }

    public function kpiEntryDetails()
    {
        return $this->hasMany(KpiEntryDetail::class, 'metric_id');
    }

    public function teamGoals()
    {
        return $this->hasMany(TeamGoals::class, 'metric_id');
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }
}

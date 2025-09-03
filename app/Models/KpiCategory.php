<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'applicable_roles',
        'is_active',
    ];

    protected $casts = [
        'applicable_roles' => 'array',
    ];

    public function kpiMetrics()
    {
        return $this->hasMany(KpiMetric::class, 'kpi_category_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class KpiDailyEntry extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'entry_date',
    'submitted_at',
        'status',
        'notes',
    ];

    protected $with = ['kpiEntryDetails.kpiMetric'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly(['user_id', 'entry_date', 'submitted_at', 'status', 'notes'])
            ->dontSubmitEmptyLogs();
    }

    public function shouldLogEvent(string $eventName): bool
    {
        return in_array($eventName, ['created', 'deleted']);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function kpiEntryDetails(): HasMany
    {
        return $this->hasMany(KpiEntryDetail::class, 'entry_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'subject_id', 'id')
            ->where('subject_type', KpiDailyEntry::class)
            ->orderBy('created_at', 'desc');
    }
}

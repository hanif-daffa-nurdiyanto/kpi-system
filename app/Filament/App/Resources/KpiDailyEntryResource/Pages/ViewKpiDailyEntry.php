<?php

namespace App\Filament\App\Resources\KpiDailyEntryResource\Pages;

use Filament\Actions;
use App\Helpers\FilamentHelper;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\App\Resources\KpiDailyEntryResource;
use App\Filament\App\Resources\KpiDailyEntryResource\Widgets\PerformanceSummaryOverview;
use App\Models\Department;
use App\Models\KpiDailyEntry;
use App\Models\User;
use Carbon\Carbon;

class ViewKpiDailyEntry extends ViewRecord
{
    protected static string $resource = KpiDailyEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->status === 'draft')
                ->action(function ($record, KpiDailyEntry $kpiDailyEntry) {
                    $record->update([
                        'status' => 'submitted',
                        'submitted_at' => Carbon::now(),
                    ]);
                    $creator = User::find($kpiDailyEntry->user_id);
                    $employee = $kpiDailyEntry->employee;
                    if (!$employee || !$employee->department_id) {
                        return;
                    }
                    $department = Department::where('id', $employee->department_id)->first();
                    $departmentName = $department->name ?? 'Unknown Department';
                    $manager = User::find($department->manager_id);
                    $superAdmins = User::whereHas('roles', function ($query) {
                        $query->where('name', 'super_admin');
                    })->get();
                    $recipients = $superAdmins->push($manager)->filter();
                    foreach ($recipients as $recipient) {
                        $message = "A new KPI entry has been submitted from {$creator->name} (Department: {$departmentName})";
                        if ($recipient->hasRole('super_admin')) {
                            $message = "A new KPI entry from {$creator->name} - ({$departmentName}) Department requires attention.";
                        } elseif ($recipient->id === $manager->id) {
                            $message = "Your team member {$creator->name} from {$departmentName} Department has submitted a KPI entry.";
                        }
                        $title = "New KPI Entry Submitted from {$creator->name} - {$departmentName}";
                        if ($recipient->hasRole('super_admin')) {
                            $title = "New KPI Entry Submitted from {$creator->name} - {$departmentName} Department";
                        } elseif ($recipient->id === $manager->id) {
                            $title = "New KPI Entry Submitted from {$creator->name}";
                        }
                        Notification::make()
                        ->title($title)
                        ->body($message)
                        ->icon('heroicon-o-chart-bar')
                        ->color('primary')
                        ->actions([
                            Action::make('View')
                                ->button()
                                ->url(
                                    fn () => $recipient->hasRole('super_admin')
                                    ? route('filament.admin.resources.kpi-daily-entries.view', $kpiDailyEntry->id)
                                    : route('filament.app.resources.kpi-daily-entries.view', $kpiDailyEntry->id),
                                    false
                                )
                                ->markAsRead()
                        ])
                        ->seconds(30)
                        ->broadcast($recipient)
                        ->sendToDatabase($recipient, isEventDispatched: true);
                    }
                }),
            Actions\Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->hasRole('manager'))
                ->action(function() {
                     $this->record->update(['status' => 'approved']);

                    Notification::make()
                                ->title('KPI Entry Approved')
                                ->body("Your KPI entry has been approved by your manager.")
                                ->icon('heroicon-o-check-circle')
                                ->iconColor('success')
                                ->color('success')
                                ->actions([
                                    Action::make('View Approve Entry')
                                        ->button()
                                        ->color('success')
                                        ->url(route('filament.app.resources.kpi-daily-entries.view', $this->record->id), false)
                                        ->markAsRead()
                                ])
                                ->seconds(30)
                                ->broadcast($this->record->user)
                                ->sendToDatabase($this->record->user, isEventDispatched: true);
                }),
            Actions\Action::make('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->hasRole('manager'))
                ->action(function() {
                     $this->record->update(['status' => 'rejected']);

                    Notification::make()
                                ->title('KPI Entry Rejected')
                                ->body("Your KPI entry has been rejected by your manager.")
                                ->icon('heroicon-o-x-circle')
                                ->iconColor('danger')
                                ->color('danger')
                                ->actions([
                                    Action::make('View Rejected Entry')
                                        ->button()
                                        ->color('danger')
                                        ->url(route('filament.app.resources.kpi-daily-entries.view', $this->record->id), false)
                                        ->markAsRead()
                                ])
                                ->seconds(30)
                                ->broadcast($this->record->user)
                                ->sendToDatabase($this->record->user, isEventDispatched: true);
                }),
            Actions\EditAction::make()
            ->visible(fn($record) => $record->status != 'approved' && $record->status != 'rejected'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PerformanceSummaryOverview::class,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(1)
                    ->schema([
                        Section::make('KPI Daily Entry Details')
                            ->collapsible()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Employee')
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-o-user')
                                            ->size('lg')
                                            ->columnSpan(1),

                                        TextEntry::make('entry_date')
                                            ->label('Entry Date')
                                            ->date('F j, Y')
                                            ->icon('heroicon-o-calendar')
                                            ->columnSpan(1),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'submitted' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            })
                                            ->icon(fn(string $state): string => match ($state) {
                                                'draft' => 'heroicon-o-document',
                                                'submitted' => 'heroicon-o-paper-airplane',
                                                'approved' => 'heroicon-o-check-circle',
                                                'rejected' => 'heroicon-o-x-circle',
                                                default => 'heroicon-o-document',
                                            })
                                            ->iconPosition(IconPosition::Before)
                                            ->size('lg')
                                            ->columnSpan(1),

                                        TextEntry::make('submitted_at')
                                            ->label('Submission Info')
                                            ->formatStateUsing(function ($state, $record) {
                                                if ($state === null) {
                                                    return 'Not yet submitted';
                                                }
                                                return 'Submitted on ' . date('F j, Y \a\t g:i a', strtotime($state));
                                            })
                                            ->icon('heroicon-o-clock')
                                            ->columnSpan(1),

                                        TextEntry::make('created_at')
                                            ->label('Created')
                                            ->formatStateUsing(function ($state, $record) {
                                                $timeAgo = $record->created_at->diffForHumans();
                                                return $record->created_at->format('F j, Y \a\t g:i a') . ' (' . $timeAgo . ')';
                                            })
                                            ->icon('heroicon-o-document-text')
                                            ->columnSpan(1),

                                        TextEntry::make('notes')
                                            ->label('Entry Notes')
                                            ->markdown()
                                            ->columnSpanFull()
                                            ->visible(fn($record) => !empty($record->notes))
                                    ]),
                            ])
                            ->compact(),
                    ]),

                Tabs::make('KPI Information')
                    ->tabs([

                        Tabs\Tab::make('KPI Metrics')
                            ->schema([
                                RepeatableEntry::make('kpiEntryDetails')
                                    ->schema([
                                        Section::make(fn($record) => $record->kpiMetric->name)
                                            ->description(fn($record) => $record->kpiMetric->category->name)
                                            ->icon(fn($record) => FilamentHelper::getPerformanceIcon($record))
                                            ->iconColor(fn($record) => FilamentHelper::getPerformanceColor($record))
                                            ->collapsible()
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('value')
                                                            ->label('Actual Value')
                                                            ->formatStateUsing(
                                                                fn($state, $record) =>
                                                                is_numeric($state) ? number_format($state, 2) . ' ' . ($record->kpiMetric->unit ?? '') : $state
                                                            )
                                                            ->color(fn($record) => FilamentHelper::getPerformanceColor($record))
                                                            ->weight(FontWeight::Bold)
                                                            ->size('xl')
                                                            ->columnSpan(1),

                                                        TextEntry::make('kpiMetric.target_value')
                                                            ->label('Target')
                                                            ->formatStateUsing(function ($state, $record) {
                                                                return $state ? number_format($state, 2) . ' ' . ($record->kpiMetric->unit ?? '') : 'N/A';
                                                            })
                                                            ->columnSpan(1),

                                                        TextEntry::make('performance_percentage')
                                                            ->label('Performance')
                                                            ->state(function ($record) {
                                                                $target = $record->kpiMetric->target_value ?? 0;
                                                                if ($target == 0)
                                                                    return 'N/A';

                                                                $value = $record->value ?? 0;
                                                                $percentage = ($value / $target) * 100;

                                                                if (!$record->kpiMetric->is_higher_better) {
                                                                    if ($value == 0)
                                                                        return '100%';
                                                                    $percentage = ($target / $value) * 100;
                                                                }

                                                                return number_format($percentage, 1) . '%';
                                                            })
                                                            ->color(function ($record, $state) {
                                                                if ($state === 'N/A')
                                                                    return 'gray';

                                                                $percentage = (float) str_replace(['%', ','], '', $state);

                                                                if ($percentage >= 100)
                                                                    return 'success';
                                                                if ($percentage >= 80)
                                                                    return 'warning';
                                                                return 'danger';
                                                            })
                                                            ->icon(function ($record, $state) {
                                                                if ($state === 'N/A')
                                                                    return 'heroicon-o-minus-circle';

                                                                $percentage = (float) str_replace(['%', ','], '', $state);

                                                                if ($percentage >= 100)
                                                                    return 'heroicon-o-check-circle';
                                                                if ($percentage >= 80)
                                                                    return 'heroicon-o-exclamation-circle';
                                                                return 'heroicon-o-x-circle';
                                                            })
                                                            ->weight(FontWeight::Bold)
                                                            ->size('xl')
                                                            ->columnSpan(1),
                                                    ]),

                                                TextEntry::make('kpiMetric.description')
                                                    ->label('Description')
                                                    ->markdown()
                                                    ->visible(fn($record) => !empty($record->kpiMetric->description))
                                                    ->columnSpanFull(),

                                                TextEntry::make('notes')
                                                    ->label('Notes')
                                                    ->markdown()
                                                    ->visible(fn($record) => !empty($record->notes))
                                                    ->columnSpanFull(),

                                            ]),
                                    ])
                                    ->columns(1),
                            ]),

                        Tabs\Tab::make('Activity Log')
                            ->schema([
                                RepeatableEntry::make('activities')
                                    ->schema([
                                        Section::make(function ($record) {
                                                $props = $record->properties;

                                                $props = $props instanceof \Illuminate\Support\Collection
                                                    ? $props->toArray()
                                                    : (is_array($props) ? $props : json_decode($props, true));

                                                $oldStatus = data_get($props, 'old.attributes.status');
                                                $newStatus = data_get($props, 'new.attributes.status');

                                                $finalStatus = ($oldStatus !== $newStatus && !empty($newStatus))
                                                    ? $newStatus
                                                    : ($oldStatus ?? $newStatus ?? 'draft');

                                                return ucfirst($finalStatus);
                                            })
                                            ->description(fn($record) => $record->causer->name ?? 'System')
                                            ->icon(fn($record) => match ($record->description) {
                                                'created' => 'heroicon-o-plus-circle',
                                                'updated' => 'heroicon-o-pencil',
                                                'deleted' => 'heroicon-o-trash',
                                                'submitted' => 'heroicon-o-paper-airplane',
                                                'approved' => 'heroicon-o-check-circle',
                                                'rejected' => 'heroicon-o-x-circle',
                                                default => 'heroicon-o-information-circle',
                                            })
                                            ->iconColor(fn($record) => match ($record->description) {
                                                'created' => 'success',
                                                'updated' => 'warning',
                                                'deleted' => 'danger',
                                                'submitted' => 'info',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            })
                                            ->collapsible()
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('created_at')
                                                            ->label('Created At')
                                                            ->dateTime('F j, Y \a\t g:i a')
                                                            ->icon('heroicon-o-calendar')
                                                            ->columnSpan(1),
                                                        TextEntry::make('updated_at')
                                                            ->label('Updated At')
                                                            ->dateTime('F j, Y \a\t g:i a')
                                                            ->icon('heroicon-o-pencil')
                                                            ->columnSpan(1),
                                                        TextEntry::make('causer.name')
                                                            ->label('User')
                                                            ->columnSpan(1),
                                                    ]),
                                                Grid::make(1)
                                            ->schema([
                                            TextEntry::make('change_summary')
                                                ->label('Changes')
                                                ->state('dummy')
                                                ->formatStateUsing(function ($state, $record) {
                                                    $properties = $record->properties instanceof \Illuminate\Support\Collection
                                                        ? $record->properties->toArray()
                                                        : (is_array($record->properties)
                                                            ? $record->properties
                                                            : (is_string($record->properties)
                                                                ? json_decode($record->properties, true)
                                                                : [])
                                                        );

                                                    if (!is_array($properties)) {
                                                        return "<span class='text-gray-900 dark:text-gray-100'>Invalid properties format</span>";
                                                    }

                                                    $oldAttributes = $properties['old']['attributes'] ?? [];
                                                    $newAttributes = $properties['new']['attributes'] ?? [];

                                                    $oldDetails = $properties['old']['kpiEntryDetails'] ?? [];
                                                    $newDetails = collect($properties['new']['kpiEntryDetails'] ?? []);

                                                    $attributeLabels = [
                                                        'user_id' => 'User',
                                                        'entry_date' => 'Entry Date',
                                                        'status' => 'Status',
                                                        'notes' => 'Notes',
                                                    ];

                                                    $changes = "";

                                                    $attributeItems = [];
                                                    foreach ($attributeLabels as $key => $label) {
                                                        $oldVal = $oldAttributes[$key] ?? '-';
                                                        $newVal = $newAttributes[$key] ?? '-';

                                                        if ($oldVal != $newVal) {
                                                            if ($key === 'status') {
                                                                $badgeColor = fn($status) => match ($status) {
                                                                    'draft' => '#9ca3af',
                                                                    'submitted' => '#facc15',
                                                                    'approved' => '#22c55e',
                                                                    'rejected' => '#ef4444',
                                                                    default => '#6b7280',
                                                                };
                                                                $oldBadge = "<span style='background-color: {$badgeColor($oldVal)}; color: #fff; padding: 2px 8px; border-radius: 12px; display:inline-block;'>{$oldVal}</span>";
                                                                $newBadge = "<span style='background-color: {$badgeColor($newVal)}; color: #fff; padding: 2px 8px; border-radius: 12px; display:inline-block;'>{$newVal}</span>";
                                                                $attributeItems[] = "<li class='text-gray-900 dark:text-gray-100' style='margin-left: 20px;'><strong>{$label}:</strong> {$oldBadge} → {$newBadge}</li>";
                                                            } else {
                                                                $attributeItems[] = "<li class='text-gray-900 dark:text-gray-100' style='margin-left: 20px;'><strong>{$label}:</strong> {$oldVal} → {$newVal}</li>";
                                                            }
                                                        }
                                                    }

                                                    if (!empty($attributeItems)) {
                                                        $changes .= "<div class='font-bold text-gray-900 dark:text-gray-100' style='margin-bottom: 5px;'>Attribute Changes</div>";
                                                        $changes .= "<ul style='margin-bottom: 15px;'>".implode('', $attributeItems)."</ul>";
                                                    }

                                                    $kpiItems = [];

                                                    foreach ($newDetails as $newDetail) {
                                                        $id = $newDetail['id'];
                                                        $newValue = $newDetail['value'] ?? '-';
                                                        $newMetricName = $newDetail['metric_name'] ?? ($newDetail['kpi_metric']['name'] ?? 'Unknown');

                                                        if (isset($oldDetails[$id])) {
                                                            $oldDetail = $oldDetails[$id];
                                                            $oldValue = $oldDetail['value'] ?? '-';
                                                            $oldMetricName = $oldDetail['metric_name'] ?? 'Unknown';

                                                            if ($oldValue != $newValue || $oldMetricName !== $newMetricName) {
                                                                $kpiItems[] = "<li class='text-gray-900 dark:text-gray-100' style='margin-left: 20px;'>
                                                                    <strong>Metric:</strong> {$oldMetricName} → {$newMetricName}<br>
                                                                    <strong>Value:</strong> {$oldValue} → {$newValue}<br><br>
                                                                </li>";
                                                            }
                                                        } else {
                                                            $kpiItems[] = "<li class='text-gray-900 dark:text-gray-100' style='margin-left: 20px;'>
                                                                <strong>Metric:</strong> (new) → {$newMetricName}<br>
                                                                <strong>Value:</strong> → {$newValue}<br><br>
                                                            </li>";
                                                        }
                                                    }

                                                    $newDetailIds = $newDetails->pluck('id')->all();
                                                    foreach ($oldDetails as $id => $oldDetail) {
                                                        if (!in_array($id, $newDetailIds)) {
                                                            $oldMetricName = $oldDetail['metric_name'] ?? 'Unknown';
                                                            $oldValue = $oldDetail['value'] ?? '-';
                                                            $kpiItems[] = "<li class='text-gray-900 dark:text-gray-100' style='margin-left: 20px;'>
                                                                <strong>Metric:</strong> {$oldMetricName} → (deleted)<br>
                                                                <strong>Value:</strong> {$oldValue} → (deleted)<br><br>
                                                            </li>";
                                                        }
                                                    }

                                                    if (!empty($kpiItems)) {
                                                        $changes .= "<div class='font-bold text-gray-900 dark:text-gray-100' style='margin-bottom: 5px;'>KPI Detail Changes</div>";
                                                        $changes .= "<ul>".implode('', $kpiItems)."</ul>";
                                                    }

                                                    return $changes ?: '<span class="text-gray-900 dark:text-gray-100">— No changes —</span>';
                                                })

                                                ->html()

                                                ->columnSpanFull()
                                                ->visible(fn($record) => $record->description !== 'created')
                                            ]),
                                        ]),
                    ])
                    ->columns(1),
                ])
                ->icon('heroicon-o-clock')
            ])
            ->activeTab(1)
            ->columnSpanFull(),
        ]);
    }
}

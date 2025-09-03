<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\KpiMetric;
use App\Models\Department;
use Filament\Tables\Table;
use App\Models\KpiDailyEntry;
use Illuminate\Support\Carbon;
use App\Helpers\FilamentHelper;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Actions\Action;
use Filament\Forms\Components\Actions as FormActions;
use App\Filament\App\Resources\KpiDailyEntryResource\Pages;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\App\Resources\KpiDailyEntryResource\RelationManagers;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class KpiDailyEntryResource extends Resource
{
    protected static ?string $model = KpiDailyEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Daily KPI Entries';
    protected static ?string $pluralModelLabel = 'Daily Entry';
    protected static ?string $modelLabel = 'Entry';
    protected static ?string $navigationGroup = 'Performance Tracking';

    protected static ?int $navigationSort = 3;

    private function processKpiImport(array $data, $livewire)
    {
        $filePath = storage_path('app/public/' . $data['kpi_import_file']);

        try {
            $result = $this->importKpiData($filePath);
            $livewire->data['kpi_metrics'] = $result['data'];
            $this->showImportResult($result);
        } catch (\Exception $e) {
            $this->showError($e->getMessage());
        } finally {
            $this->cleanupFile($filePath);
        }
    }

    private function importKpiData(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new \Exception('Invalid Excel file. Make sure to use .xlsx format');
        }
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        if ($highestRow < 2) {
            throw new \Exception('Empty file. There must be at least 1 line of data besides the header');
        }
        return $this->processExcelRows($worksheet, $highestRow);
    }

    private function processExcelRows($worksheet, int $highestRow): array
    {
        $successData = [];
        $errors = [];
        $rowCount = 0;
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowCount++;
            $rowData = $this->getRowData($worksheet, $row);
            if ($this->isRowEmpty($rowData)) {
                continue;
            }
            try {
                $processedData = $this->validateAndProcessRow($rowData, $row);
                $successData[] = $processedData;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        return [
            'data' => $successData,
            'total_rows' => $rowCount,
            'success_count' => count($successData),
            'error_count' => count($errors),
            'errors' => $errors
        ];
    }

    private function getRowData($worksheet, int $row): array
    {
        return [
            'metric_name' => trim($worksheet->getCell("A{$row}")->getCalculatedValue() ?? ''),
            'value' => $worksheet->getCell("B{$row}")->getCalculatedValue(),
            'notes' => trim($worksheet->getCell("C{$row}")->getCalculatedValue() ?? ''),
        ];
    }

    private function isRowEmpty(array $rowData): bool
    {
        return empty($rowData['metric_name']) &&
            ($rowData['value'] === null || $rowData['value'] === '');
    }

    private function validateAndProcessRow(array $rowData, int $row): array
    {
        if (empty($rowData['metric_name'])) {
            throw new \Exception("Line {$row}: KPI name cannot be empty");
        }
        $metric = KpiMetric::where('name', $rowData['metric_name'])
            ->where('is_active', true)
            ->first();
        if (!$metric) {
            throw new \Exception("Line {$row}: KPI '{$rowData['metric_name']}' not found");
        }
        if ($rowData['value'] === null || $rowData['value'] === '') {
            throw new \Exception("Line {$row}: Value cannot be empty");
        }
        if (!is_numeric($rowData['value'])) {
            throw new \Exception("Line {$row}: Value must be a number");
        }

        return [
            'metric_id' => $metric->id,
            'target' => $metric->target_value,
            'unit' => $metric->unit,
            'value' => floatval($rowData['value']),
            'notes' => $rowData['notes'],
        ];
    }

    private function showImportResult(array $result): void
    {
        $success = $result['success_count'];
        $errors = $result['error_count'];
        if ($success === 0) {
            $this->showNotification('Import Failed', 'No data was successfully imported', 'danger');
            return;
        }
        if ($errors === 0) {
            $this->showNotification('Import Successful', "Successfully imported {$success} KPI data", 'success');
            return;
        }
        $this->showNotification('Import Complete', "{$success} succeeded, {$errors} failed", 'warning');
    }

    private function showNotification(string $title, string $message, string $type): void
    {
        $notification = Notification::make()
            ->title($title)
            ->body($message);
        switch ($type) {
            case 'success':
                $notification->success()->duration(3000);
                break;
            case 'warning':
                $notification->warning()->duration(5000);
                break;
            case 'danger':
                $notification->danger()->persistent();
                break;
        }

        $notification->send();
    }

    private function cleanupFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isEmployee = $user->hasRole('employee');
        $isManager = $user->hasRole('manager');

        return $form
            ->schema([
                Section::make('Entry Information')
                    ->schema([
                        Select::make('user_id')
                            ->label('Employee')
                            ->options(function () use ($user, $isManager, $isEmployee) {
                                if ($isEmployee) {
                                    return [$user->id => $user->name];
                                }

                                if ($isManager) {
                                    $managerDepartment = Department::where('manager_id', $user->id)->first();

                                    if ($managerDepartment) {
                                        return User::whereHas('employee', function ($query) use ($managerDepartment) {
                                            $query->where('department_id', $managerDepartment->id);
                                        })
                                            ->with('employee.department')
                                            ->get()
                                            ->mapWithKeys(function ($user) {
                                                if ($user->employee && $user->employee->department) {
                                                    $departmentName = $user->employee->department->name;
                                                    $badgeColor = 'rgba(59, 130, 246, 0.5)';

                                                    return [
                                                        $user->id => $user->name . ' <span style="display: inline-block; margin-left: 8px; padding: 1px 6px; font-size: 0.60rem; background-color: ' . $badgeColor . '; color: white; border-radius: 9999px;">' . $departmentName . '</span>'
                                                    ];
                                                }
                                                return [];
                                            })
                                            ->filter();
                                    }
                                    return [];
                                }

                                return User::whereHas('employee')
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        $departmentName = $user->employee->department->name ?? 'No Department';
                                        $badgeColor = $user->employee->department ? 'rgba(59, 130, 246, 0.5)' : 'rgba(156, 163, 175, 0.5)';

                                        return [
                                            $user->id => $user->name . ' <span style="display: inline-block; margin-left: 8px; padding: 1px 6px; font-size: 0.60rem; background-color: ' . $badgeColor . '; color: white; border-radius: 9999px;">' . $departmentName . '</span>'
                                        ];
                                    });
                            })
                            ->default($isEmployee ? $user->id : null)
                            ->disabled($isEmployee)
                            ->dehydrated(true)
                            ->searchable()
                            ->allowHtml()
                            ->required(),

                        DatePicker::make('entry_date')
                            ->label('Entry Date')
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'reviewed' => 'Reviewed',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->disabled($isEmployee)
                            ->dehydrated(false)
                            ->required(),

                        Textarea::make('notes')
                            ->placeholder('Add any additional notes or context about this entry')
                            ->hidden()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                    Section::make('KPI Metrics')
                    ->schema([
                        FormActions::make([
                            FormAction::make('showImportFormat')
                                ->label('View Format')
                                ->icon('heroicon-o-information-circle')
                                ->color('info')
                                ->modalHeading('KPI Import Format Guide')
                                ->modalDescription('Complete guide for Excel file format to import KPI data')
                                ->modalWidth('7xl')
                                ->modalContent(view('filament.app.modals.kpi-import-format'))
                                ->modalSubmitAction(false)
                                ->modalCancelAction(false)
                                ->action(fn() => null),

                            FormAction::make('downloadTemplate')
                                ->label('Download Template')
                                ->icon('heroicon-o-cloud-arrow-down')
                                ->color('success')
                                ->action(function () {
                                    $data = [
                                        ['KPI Metric', 'Value', 'Notes'],
                                        ['Outbound Calls', 75, 'Target achievement 75%'],
                                        ['Talk Time', 85, 'Increased from last month'],
                                        ['Auto Quotes', 92, 'Target achieved'],
                                        ['Customer Satisfaction', 88, 'Needs improvement'],
                                        ['Response Time', 45, 'Within normal limits'],
                                    ];

                                    return Excel::download(new class($data) implements FromArray {
                                        protected $data;

                                        public function __construct($data)
                                        {
                                            $this->data = $data;
                                        }

                                        public function array(): array
                                        {
                                            return $this->data;
                                        }
                                    }, 'KPI_Import_Template.xlsx');
                                }),

                            FormAction::make('importKpiData')
                                ->label('Import Excel')
                                ->icon('heroicon-o-arrow-down-on-square')
                                ->color('warning')
                                ->form([
                                    Section::make('Import KPI Data')
                                        ->description('Upload an Excel file with the appropriate format. Click "View Import Format" to see the complete guide.')
                                        ->schema([
                                            FileUpload::make('kpi_import_file')
                                                ->label('Excel File (.xlsx)')
                                                ->required()
                                                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                                                ->helperText('Ensure the file format matches the provided template.')
                                                ->disk('public')
                                                ->directory('kpi-imports')
                                                ->visibility('private')
                                                ->maxSize(5120),

                                                Placeholder::make('format_info')
                                                ->label('Required Format')
                                                ->content(new HtmlString('
                                                    <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                                        <div class="space-y-2 text-sm">
                                                            <div class="grid grid-cols-3 gap-4 font-medium text-blue-900 dark:text-blue-100">
                                                                <span>Column A</span>
                                                                <span>Column B</span>
                                                                <span>Column C</span>
                                                            </div>
                                                            <div class="grid grid-cols-3 gap-4 text-blue-700 dark:text-blue-300">
                                                                <span>Name KPI</span>
                                                                <span>Value (number)</span>
                                                                <span>Note</span>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-700">
                                                            <p class="text-xs text-blue-600 dark:text-blue-400">
                                                                💡 The first line will be ignored as a header
                                                            </p>
                                                        </div>
                                                    </div>
                                                ')),
                                        ])
                                ])
                                ->action(function (array $data, $livewire) {
                                    return (new static())->processKpiImport($data, $livewire);
                                })
                        ]),

                        Repeater::make('kpi_metrics')
                            ->label('KPI Entry Details')
                            ->relationship('kpiEntryDetails')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('metric_id')
                                            ->label('KPI Metric')
                                            ->options(KpiMetric::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (!$state) return;

                                                $metric = KpiMetric::find($state);
                                                if ($metric) {
                                                    $set('target', $metric->target_value);
                                                    $set('unit', $metric->unit);
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('target')
                                            ->label('Target')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(1),

                                        TextInput::make('value')
                                            ->label('Actual Value')
                                            ->required()
                                            ->numeric()
                                            ->step(0.01)
                                            ->columnSpan(1),

                                        TextInput::make('unit')
                                            ->label('Unit')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(1),

                                        Textarea::make('notes')
                                            ->label('Notes')
                                            ->placeholder('Additional information about this metric')
                                            ->columnSpan(3)
                                            ->rows(2),
                                    ]),
                            ])
                            ->itemLabel(fn(array $state): ?string =>
                                isset($state['metric_id']) ? KpiMetric::find($state['metric_id'])?->name : null
                            )
                            ->addActionLabel('Add KPI Metric')
                            ->collapsible()
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->cloneable(),

                        Hidden::make('parsed_data')
                            ->default([])
                            ->dehydrated(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->weight(FontWeight::Medium),

                TextColumn::make('entry_date')
                    ->label('Entry Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'reviewed',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-pencil' => 'draft',
                        'heroicon-o-paper-airplane' => 'submitted',
                        'heroicon-o-eye' => 'reviewed',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ]),

                TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not submitted')
                    ->color('gray'),

                TextColumn::make('metrics_count')
                    ->label('Metrics')
                    ->getStateUsing(function ($record) {
                        return $record ? $record->kpiEntryDetails->count() : 0;
                    })
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->placeholder('Not assigned')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name', function ($query) {
                        $manager = auth()->user();
                        if (!$manager || !$manager->hasRole('manager')) {
                            return $query->whereNull('id');
                        }
                        $department = Department::where('manager_id', $manager->id)->first();
                        if (!$department) {
                            return $query->whereNull('id');
                        }
                        $employeeUserIds = Employee::where('department_id', $department->id)->pluck('user_id')->toArray();
                        return $query->whereIn('id', $employeeUserIds);
                    })
                    ->searchable()
                    ->placeholder('All')
                    ->visible(fn() => auth()->user()->hasRole('manager'))
                    ->indicateUsing(fn($data) => $data['value']
                        ? 'Employee: ' . User::find($data['value'])->name
                        : null),

                    Filter::make('entry_date_range')
                        ->form([
                            DatePicker::make('from')->label('From')->reactive()
                            ->disabled(fn (Get $get) => !empty($get('../quick_date_range.options_date'))),
                            DatePicker::make('until')->label('Until')->reactive()
                            ->disabled(fn (Get $get) => !empty($get('../quick_date_range.options_date'))),
                        ])
                        ->indicateUsing(function (array $data) {
                            if ($data['from'] && $data['until']) {
                                return 'Entry: ' . Carbon::parse($data['from'])->toFormattedDateString()
                                        . ' – '        . Carbon::parse($data['until'])->toFormattedDateString();
                            }
                            if ($data['from'])  return 'Entry ≥ ' . Carbon::parse($data['from'])->toFormattedDateString();
                            if ($data['until']) return 'Entry ≤ ' . Carbon::parse($data['until'])->toFormattedDateString();
                            return null;
                        })
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when($data['from'],  fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
                                ->when($data['until'], fn ($q, $date) => $q->whereDate('entry_date', '<=', $date));
                        }),

                    Filter::make('quick_date_range')
                        ->form([
                            Select::make('options_date')
                            ->label('Quick Date Range')
                            ->options([
                                'today' => 'Today',
                                'this_week' => 'This Week',
                                'this_month' => 'This Month'
                            ])
                            ->reactive()
                            ->disabled(fn (Get $get) => !empty($get('../entry_date_range.from')) || !empty($get('../entry_date_range.until')))
                        ])
                        ->query(function (Builder $query, array $data) {
                            if (!empty($data['options_date'])) {
                                switch ($data['options_date']) {
                                    case 'today':
                                        return $query->whereDate('entry_date', Carbon::today());
                                    case 'this_week':
                                        return $query->whereBetween('entry_date', [
                                            Carbon::now()->startOfWeek(),
                                            Carbon::now()->endOfWeek()
                                        ]);
                                    case 'this_month':
                                        return $query->whereMonth('entry_date', Carbon::now()->month)
                                                ->whereYear('entry_date', Carbon::now()->year);
                                }
                            }
                            return $query;
                        })
                        ->indicateUsing(function (array $data) {
                            if (!empty($data['options_date'])) {
                                switch ($data['options_date']) {
                                    case 'today':
                                        return 'Entry: Today';
                                    case 'this_week':
                                        return 'Entry: This Week';
                                    case 'this_month':
                                        return 'Entry: This Month';
                                }
                            }
                            return null;
                        }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('submit')
                        ->label('Submit')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
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
                                        ->url(route('filament.app.resources.kpi-daily-entries.view', $kpiDailyEntry->id), false)
                                        ->markAsRead()
                                ])
                                ->seconds(30)
                                ->broadcast($recipient)
                                ->sendToDatabase($recipient, isEventDispatched: true);
                            }
                        }),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn($record) => auth()->user()->hasRole('manager') && $record->status === 'submitted')
                        ->action(function ($record) {
                            $record->update(['status' => 'approved']);

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
                                        ->url(route('filament.app.resources.kpi-daily-entries.view', $record->id), false)
                                        ->markAsRead()
                                ])
                                ->seconds(30)
                                ->broadcast($record->user)
                                ->sendToDatabase($record->user, isEventDispatched: true);
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn($record) => auth()->user()->hasRole('manager') && $record->status === 'submitted')
                        ->action(function ($record) {
                            $record->update(['status' => 'rejected']);

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
                                        ->url(route('filament.app.resources.kpi-daily-entries.view', $record->id), false)
                                        ->markAsRead()
                                ])
                                ->seconds(30)
                                ->broadcast($record->user)
                                ->sendToDatabase($record->user, isEventDispatched: true);
                        }),
                ])
                    ->label('Actions')
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn() => auth()->user()->hasRole('manager'))
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn($record) => $record->update(['status' => 'approved']));
                        }),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                if ($user->hasRole('employee')) {
                    $query->where('user_id', $user->id);
                } elseif ($user->hasRole('manager')) {
                    $managerDepartment = Department::where('manager_id', $user->id)->first();

                    if ($managerDepartment) {
                        $query->whereHas('user.employee', function ($q) use ($managerDepartment) {
                            $q->where('department_id', $managerDepartment->id);
                        });
                    }
                }
            })
        ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKpiDailyEntries::route('/'),
            'create' => Pages\CreateKpiDailyEntry::route('/create'),
            'view' => Pages\ViewKpiDailyEntry::route('/{record}'),
            'edit' => Pages\EditKpiDailyEntry::route('/{record}/edit'),
        ];
    }
}

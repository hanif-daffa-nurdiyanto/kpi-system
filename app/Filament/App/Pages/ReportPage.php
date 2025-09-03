<?php

namespace App\Filament\App\Pages;

use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Department;
use App\Models\KpiDailyEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ReportPage extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.admin.pages.report-page';
    protected static ?string $navigationLabel = 'KPI Report';
    protected static ?string $navigationGroup = 'Performance Tracking';
    protected static ?string $title = 'KPI Report';
    protected ?string $heading = '';
    protected static ?int $navigationSort = 5;
    public $dateRangeEmployee = null;
    public ?string $monthEmployee = null;
    public ?string $employee = null;

    public function form(Form $form): Form
    {
        $columns = Auth::user()->hasRole('manager') ? 3 : 4;
        $user = Auth::user();
        if ($user->hasRole('manager')) {
            $hasDepartment = $user->department !== null;
        } else {
            $hasDepartment = $user->employee && $user->employee->department !== null;
        }

        return $form
            ->schema([
                Section::make('Employee Filter')
                    ->description(function () {
                        $user = Auth::user();

                        if ($user->hasRole('manager')) {
                            return "Select the employee and date filter or you can leave the employee form blank to get all employee in your department.";
                        } else {
                            return "Select the date filter for your report.";
                        }
                    })
                    ->columns($columns)
                    ->columnSpan(1)
                    ->schema(!$hasDepartment ? 
                        [
                            Placeholder::make('no_department')
                            ->content('You must be assigned to a department to access this report.')
                            ->extraAttributes(['class' => 'text-primary-600 dark:text-primary-500 font-bold'])
                            ->columnSpanFull(),
                            Actions::make([
                                Action::make('goToDepartments')
                                    ->label('Go to Departments')
                                    ->color('primary')
                                    ->url(route('filament.app.resources.departments.index')),
                            ]),
                        ]
                        : 
                        [
                            DateRangePicker::make('dateRangeEmployee')
                                ->label('Date Range')
                                ->placeholder('Select Date Range')
                                ->maxSpan(['months' => 1])
                                ->columnSpan(fn() => Auth::user()->hasRole('manager') ? 1 : 2)
                                ->disableClear(false)
                                ->requiredWithout('month')
                                ->reactive()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('month', null);
                                })
                                ->disabled(fn(Get $get) => $get('monthEmployee') !== null),

                            Select::make('monthEmployee')
                                ->label('Month')
                                ->options($this->getMonthOptions())
                                ->searchable()
                                ->requiredWithout('dateRange')
                                ->columnSpan(fn() => Auth::user()->hasRole('manager') ? 1 : 2)
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('dateRange', null);
                                })
                                ->disabled(fn(Get $get) => $get('dateRangeEmployee') !== null),

                            Select::make('employee')
                                ->label('Employee Filter')
                                ->searchable()
                                ->columnSpan(1)
                                ->preload()
                                ->visible(fn(Get $get) => Auth::user()->hasRole('manager'))
                                ->options(function (callable $get) {
                                    $user = Auth::user();
                                    $departmentId = $user->department->id;
                                    $query = Employee::query()
                                        ->join('users', 'employees.user_id', '=', 'users.id')
                                        ->where('employees.department_id', $departmentId)
                                        ->whereNotNull('users.name');

                                    if ($get('department')) {
                                        $query->where('employees.department_id', $get('department'));
                                    }

                                    return $query->pluck('users.name', 'employees.id');
                                })
                                ->searchable()
                                ->preload()
                                ->reactive(),

                            Actions::make([
                                Action::make('printByEmployee')
                                    ->label(fn() => Auth::user()->hasRole('manager') ? 'Print by Employee' : 'Print')
                                    ->color('primary')
                                    ->action(fn() => Auth::user()->hasRole('manager') ? $this->printByManager() : $this->printByEmployee()),
                            ]),
                        ]),
            ])
            ->statePath('');
    }

    protected function getMonthOptions(): array
    {
        $months = [];

        $current = Carbon::now();

        for ($i = 0; $i <= 12; $i++) {
            $date = $current->copy()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }

        ksort($months);
        $months = array_reverse($months);

        return $months;
    }

    protected function printByManager()
    {
        if (!$this->dateRangeEmployee && !$this->monthEmployee) {
            Notification::make()
                ->title('Please select a date range or month')
                ->danger()
                ->send();
            return;
        }

        $this->validate([
            'dateRangeEmployee' => 'nullable|string',
            'monthEmployee' => 'nullable|string',
        ]);

        $user = Auth::user();
        $departmentId = $user->department->id;
        $query = KpiDailyEntry::with(['employee.department', 'kpiEntryDetails.kpiMetric', 'employee'])
                    ->where('status', 'approved');

        if ($user->hasRole('manager')) {
            $department = Department::where('manager_id', $user->id)->first();
            if ($department) {
                $query->whereHas('employee', fn($q) => $q->where('department_id', $department->id));
            } else {
                $query->whereNull('id');
            }
        }

        $employeeName = 'All Employee';
        $departmentName = null;
        if ($departmentId) {
            $department = Department::find($departmentId);
            if ($department) {
                $departmentName = $department->name;
                $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
            }
        }

        if ($this->dateRangeEmployee) {
            [$startRaw, $endRaw] = explode(' - ', $this->dateRangeEmployee);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($startRaw))->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', trim($endRaw))->endOfDay();
        } elseif ($this->monthEmployee) {
            $startDate = Carbon::createFromFormat('Y-m', $this->monthEmployee)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $this->monthEmployee)->endOfMonth();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $query->whereBetween('submitted_at', [$startDate, $endDate])
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });

        $records = $query->get();

        $groupedRecords = $records->groupBy(fn($r) => $r->employee->user->name)
            ->map(
                fn($group) =>
                $group->flatMap(
                    fn($record) =>
                    $record->kpiEntryDetails->map(function ($detail) use ($record) {
                        $detail->record = $record;
                        return $detail;
                    })
                )->groupBy(fn($detail) => $detail->kpiMetric->name)
            );

        $pdf = Pdf::loadView('pdf.kpi_daily_entries_export', [
            'groupedRecords' => $groupedRecords,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeName' => $employeeName,
            'departmentName' => $departmentName,
        ])->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'defaultFont' => 'Arial',
                    'fontHeightRatio' => 0.9,
                    'dpi' => 96,
                ])->setPaper('tabloid', 'landscape');

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            'KPI_' . str_replace(' ', '_', $departmentName) . '_' . str_replace(' ', '_', $employeeName) . '.pdf'
        );
    }

    protected function printByEmployee()
    {
        if (!$this->dateRangeEmployee && !$this->monthEmployee) {
            Notification::make()
                ->title('Please select a date range or month')
                ->danger()
                ->send();
            return;
        }

        $this->validate([
            'dateRangeEmployee' => 'nullable|string',
            'monthEmployee' => 'nullable|string',
        ]);

        $user = Auth::user();
        $departmentId = $user->employee->department->id;
        $query = KpiDailyEntry::with(['employee.department', 'kpiEntryDetails.kpiMetric', 'employee']);

        $employeeName = $user->name;
        $departmentName = null;
        if ($departmentId) {
            $department = Department::find($departmentId);
            if ($department) {
                $departmentName = $department->name;
                $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
            }
        }

        $employeeId = $user->employee->id;

        if ($employeeId) {
            $employee = Employee::find($employeeId);

            if ($employee) {
                $employeeName = $employee->user->name;

                $query->where('user_id', $employee->user_id);
            }
        }

        if ($this->dateRangeEmployee) {
            [$startRaw, $endRaw] = explode(' - ', $this->dateRangeEmployee);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($startRaw))->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', trim($endRaw))->endOfDay();
        } elseif ($this->monthEmployee) {
            $startDate = Carbon::createFromFormat('Y-m', $this->monthEmployee)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $this->monthEmployee)->endOfMonth();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $query->whereBetween('submitted_at', [$startDate, $endDate])
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });

        $records = $query->get();

        $groupedRecords = $records->groupBy(fn($r) => $r->employee->user->name)
            ->map(
                fn($group) =>
                $group->flatMap(
                    fn($record) =>
                    $record->kpiEntryDetails->map(function ($detail) use ($record) {
                        $detail->record = $record;
                        return $detail;
                    })
                )->groupBy(fn($detail) => $detail->kpiMetric->name)
            );

        $pdf = Pdf::loadView('pdf.kpi_daily_entries_export', [
            'groupedRecords' => $groupedRecords,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeName' => $employeeName,
            'departmentName' => $departmentName,
        ])->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'defaultFont' => 'Arial',
                    'fontHeightRatio' => 0.9,
                    'dpi' => 96,
                ])->setPaper('tabloid', 'landscape');

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            'KPI_' . str_replace(' ', '_', $departmentName) . '_' . str_replace(' ', '_', $employeeName) . '.pdf'
        );
    }

    // protected function printByDepartment()
    // {
    //     if (!$this->dateRangeDepartment && !$this->monthDepartment) {
    //         Notification::make()
    //             ->title('Please select a date range or month')
    //             ->danger()
    //             ->send();
    //         return;
    //     }

    //     $this->validate([
    //         'dateRangeDepartment' => 'nullable|string',
    //         'monthDepartment' => 'nullable|string',
    //     ]);

    //     $user = Auth::user();
    //     $query = KpiDailyEntry::with(['employee.department', 'kpiEntryDetails.kpiMetric', 'employee']);

    //     if ($user->hasRole('manager')) {
    //         $department = Department::where('manager_id', $user->id)->first();
    //         if ($department) {
    //             $query->whereHas('employee', fn($q) => $q->where('department_id', $department->id));
    //         } else {
    //             $query->whereNull('id');
    //         }
    //     }

    //     $employeeName = null;
    //     $departmentName = 'All Department';
    //     if ($this->department) {
    //         $department = Department::find($this->department);
    //         if ($department) {
    //             $departmentName = $department->name;
    //             $query->whereHas('employee', fn($q) => $q->where('department_id', $this->department));
    //         }
    //     }

    //     if ($this->dateRangeDepartment) {
    //         [$startRaw, $endRaw] = explode(' - ', $this->dateRangeDepartment);
    //         $startDate = Carbon::createFromFormat('d/m/Y', trim($startRaw))->startOfDay();
    //         $endDate = Carbon::createFromFormat('d/m/Y', trim($endRaw))->endOfDay();
    //     } elseif ($this->monthDepartment) {
    //         $startDate = Carbon::createFromFormat('Y-m', $this->monthDepartment)->startOfMonth();
    //         $endDate = Carbon::createFromFormat('Y-m', $this->monthDepartment)->endOfMonth();
    //     } else {
    //         $startDate = Carbon::now()->startOfMonth();
    //         $endDate = Carbon::now()->endOfMonth();
    //     }

    //     $query->whereBetween('submitted_at', [$startDate, $endDate]);

    //     $records = $query->get();

    //     $groupedRecords = $records->groupBy(fn($r) => $r->employee->user->name)
    //         ->map(
    //             fn($group) =>
    //             $group->flatMap(
    //                 fn($record) =>
    //                 $record->kpiEntryDetails->map(function ($detail) use ($record) {
    //                     $detail->record = $record;
    //                     return $detail;
    //                 })
    //             )->groupBy(fn($detail) => $detail->kpiMetric->name)
    //         );

    //     $pdf = Pdf::loadView('pdf.kpi_daily_entries_export', [
    //         'groupedRecords' => $groupedRecords,
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'departmentName' => $departmentName,
    //         'employeeName' => $employeeName,
    //     ])->setOptions([
    //                 'isRemoteEnabled' => true,
    //                 'isHtml5ParserEnabled' => true,
    //                 'isPhpEnabled' => true,
    //                 'defaultFont' => 'Arial',
    //                 'fontHeightRatio' => 0.9,
    //                 'dpi' => 96,
    //             ])->setPaper('tabloid', 'landscape');

    //     return response()->streamDownload(
    //         fn() => print ($pdf->output()),
    //         'KPI_' . str_replace(' ', '_', $departmentName) . '.pdf'
    //     );
    // }

    // protected function printByEmployee()
    // {
    //     if (!$this->dateRangeEmployee && !$this->monthEmployee) {
    //         Notification::make()
    //             ->title('Please select a date range or month')
    //             ->danger()
    //             ->send();
    //         return;
    //     }

    //     $this->validate([
    //         'dateRangeEmployee' => 'nullable|string',
    //         'monthEmployee' => 'nullable|string',
    //     ]);

    //     $user = Auth::user();
    //     $query = KpiDailyEntry::with(['employee.department', 'kpiEntryDetails.kpiMetric', 'employee']);

    //     if ($user->hasRole('manager')) {
    //         $department = Department::where('manager_id', $user->id)->first();
    //         if ($department) {
    //             $query->whereHas('employee', fn($q) => $q->where('department_id', $department->id));
    //         } else {
    //             $query->whereNull('id');
    //         }
    //     }

    //     $departmentName = null;
    //     $employeeName = 'All Employee';
    //     if ($this->employee) {
    //         $employee = Employee::find($this->employee);

    //         if ($employee) {
    //             $employeeName = $employee->user->name;

    //             $query->where('user_id', $employee->user_id);
    //         }
    //     }

    //     if ($this->dateRangeEmployee) {
    //         [$startRaw, $endRaw] = explode(' - ', $this->dateRangeEmployee);
    //         $startDate = Carbon::createFromFormat('d/m/Y', trim($startRaw))->startOfDay();
    //         $endDate = Carbon::createFromFormat('d/m/Y', trim($endRaw))->endOfDay();
    //     } elseif ($this->monthEmployee) {
    //         $startDate = Carbon::createFromFormat('Y-m', $this->monthEmployee)->startOfMonth();
    //         $endDate = Carbon::createFromFormat('Y-m', $this->monthEmployee)->endOfMonth();
    //     } else {
    //         $startDate = Carbon::now()->startOfMonth();
    //         $endDate = Carbon::now()->endOfMonth();
    //     }

    //     $query->whereBetween('submitted_at', [$startDate, $endDate]);

    //     $records = $query->get();

    //     $groupedRecords = $records->groupBy(fn($r) => $r->employee->user->name)
    //         ->map(
    //             fn($group) =>
    //             $group->flatMap(
    //                 fn($record) =>
    //                 $record->kpiEntryDetails->map(function ($detail) use ($record) {
    //                     $detail->record = $record;
    //                     return $detail;
    //                 })
    //             )->groupBy(fn($detail) => $detail->kpiMetric->name)
    //         );

    //     $pdf = Pdf::loadView('pdf.kpi_daily_entries_export', [
    //         'groupedRecords' => $groupedRecords,
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'employeeName' => $employeeName,
    //         'departmentName' => $departmentName,
    //     ])->setOptions([
    //                 'isRemoteEnabled' => true,
    //                 'isHtml5ParserEnabled' => true,
    //                 'isPhpEnabled' => true,
    //                 'defaultFont' => 'Arial',
    //                 'fontHeightRatio' => 0.9,
    //                 'dpi' => 96,
    //             ])->setPaper('tabloid', 'landscape');

    //     return response()->streamDownload(
    //         fn() => print ($pdf->output()),
    //         'KPI_' . str_replace(' ', '_', $employeeName) . '.pdf'
    //     );
    // }
}

<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Department;
use App\Models\KpiEntryDetail;
use App\Models\KpiMetric;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ChartLine extends ChartWidget
{
    use InteractsWithForms;

    protected static ?string $heading = 'Grafik Performa Departemen';
    protected static ?int $sort = 2;

    public ?string $departmentId = null;

    protected array $metricColors = [
        'rgb(54, 162, 235)',
        'rgb(255, 99, 132)',
        'rgb(75, 192, 192)',
        'rgb(255, 159, 64)',
        'rgb(153, 102, 255)',
        'rgb(255, 205, 86)',
        'rgb(201, 203, 207)',
        'rgb(255, 99, 71)',
        'rgb(46, 139, 87)',
        'rgb(106, 90, 205)'
    ];

    public function mount(): void
    {
        $user = Auth::user();
        $department = Department::where('manager_id', $user->id)->first()
            ?? $user->employee?->department;

        if ($department) {
            $this->departmentId = (string) $department->id;
        }
    }

    protected function getFormSchema(): array
    {
        $user = Auth::user();
        $departmentQuery = Department::query();

        if (!$user->hasRole('super_admin')) {
            $departmentQuery->where('manager_id', $user->id);
            if ($user->employee && $user->employee->department_id) {
                $departmentQuery->orWhere('id', $user->employee->department_id);
            }
        }

        return [
            Select::make('departmentId')
                ->label('Pilih Departemen')
                ->options($departmentQuery->pluck('name', 'id')->toArray())
                ->reactive()
                ->afterStateUpdated(fn() => $this->updateChartData()),
        ];
    }

    protected function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    protected function getData(): array
    {
        if (!$this->departmentId) {
            return ['labels' => [], 'datasets' => []];
        }

        $department = Department::find($this->departmentId);
        if (!$department) {
            return ['labels' => [], 'datasets' => []];
        }

        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $metrics = KpiMetric::whereHas('category', fn($query) => $query->where('is_active', true))
            ->where('is_active', true)->get();

        $labels = range(1, Carbon::now()->daysInMonth);
        $datasets = [];

        foreach ($metrics as $index => $metric) {
            $data = [];
            foreach ($labels as $day) {
                $date = Carbon::createFromDate($currentYear, $currentMonth, $day);
                $value = KpiEntryDetail::whereHas(
                    'kpiDailyEntry',
                    fn($query) =>
                    $query->whereDate('entry_date', $date)
                        ->whereHas(
                            'user.employee',
                            fn($userQuery) =>
                            $userQuery->where('department_id', $department->id)
                        )
                )->where('metric_id', $metric->id)->sum('value');
                $data[] = $value;
            }
            $datasets[] = [
                'label' => $metric->name,
                'data' => $data,
                'borderColor' => $this->metricColors[$index % count($this->metricColors)],
                'backgroundColor' => $this->metricColors[$index % count($this->metricColors)] . '0.1',
                'fill' => false,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'title' => ['display' => true, 'text' => 'Grafik Performa Departemen - ' . Carbon::now()->format('F Y')],
                'legend' => ['position' => 'bottom'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
                'subtitle' => ['display' => true, 'text' => 'Sumbu X: Hari dalam bulan | Sumbu Y: Nilai KPI']
            ],
            'scales' => [
                'x' => ['title' => ['display' => true, 'text' => 'Hari dalam Bulan']],
                'y' => ['title' => ['display' => true, 'text' => 'Nilai Metrik KPI'], 'beginAtZero' => true]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

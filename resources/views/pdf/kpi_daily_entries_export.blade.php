<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Data KPI Daily Entry - {{ $departmentName . ' Department' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 1cm;
        }

        .page-container {
            position: relative;
            min-height: 100vh;
        }

        .page-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-bottom: 2px solid #000;
            height: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            border: 1px solid black;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            font-size: 8px;
        }

        th {
            background-color: #f4f4f4;
        }

        tr {
            page-break-inside: avoid;
        }

        .core-row {
            border-bottom: none;
        }

        .complement-row {
            border-top: none;
            border-bottom: none;
        }

        .header-section {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="header-section">
            <h2>Data KPI Daily Entry - {{ $departmentName ? $departmentName . ' Department' : $employeeName }}</h2>
            <h3>Period: {{ Carbon\Carbon::parse($startDate)->format('Y-m-d') }} -
                {{ Carbon\Carbon::parse($endDate)->format('Y-m-d') }}</h3>
        </div>

        @if ($groupedRecords->isEmpty())
            <h2>No records for this month</h2>
        @else
            @php
                $start = $startDate ? \Carbon\Carbon::parse($startDate) : \Carbon\Carbon::now()->startOfMonth();
                $end = $endDate ? \Carbon\Carbon::parse($endDate) : \Carbon\Carbon::now()->endOfMonth();
                $dateRange = \Carbon\CarbonPeriod::create($start, $end);
            @endphp
            @foreach ($groupedRecords as $employeeName => $metrics)
                <h3>Employee: {{ $employeeName }}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>KPI'S</th>
                            <th>Unit</th>
                            <th>Daily Goal</th>
                            @foreach ($dateRange as $date)
                                <th style="width: 2.6%; {{ $date->isWeekend() ? 'background-color: #FCA5A5;' : '' }}">
                                    {{ $date->format('d') }}</th>
                            @endforeach
                            <th>Daily Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metrics as $metricName => $details)
                            @php
                                $firstDetail = $details->first();
                                $target = $firstDetail->kpiMetric->target_value;
                                $unit = $firstDetail->kpiMetric->unit;

                                $valuesByDay = collect($details)
                                    ->groupBy(function ($detail) {
                                        return \Carbon\Carbon::parse($detail->record->submitted_at)->day;
                                    })
                                    ->map(function ($dayGroup) {
                                        return $dayGroup->sum('value');
                                    });

                                $total = $valuesByDay->sum();
                                $dateCount = $dateRange->count();
                                $average = $dateCount > 0 ? $total / $valuesByDay->count() : 0;
                            @endphp
                            <tr>
                                <td>{{ $metricName }}</td>
                                <td>{{ $unit }}</td>
                                <td>{{ $target }}</td>
                                @foreach ($dateRange as $date)
                                    <td
                                        style="text-align: center; {{ $date->isWeekend() ? 'background-color: #FCA5A5;' : '' }}">
                                        {{ isset($valuesByDay[$date->day]) ? number_format($valuesByDay[$date->day], 1) : '-' }}
                                    </td>
                                @endforeach

                                <td>{{ number_format($average, 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </div>
</body>

</html>

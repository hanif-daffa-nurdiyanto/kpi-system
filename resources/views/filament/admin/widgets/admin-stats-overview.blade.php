{{-- resources/views/filament/admin/widgets/admin-stats-overview.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $stats = $this->getStatsData();
            $mainStats = $stats['main_stats'] ?? [];
            $performanceMetrics = $stats['performance_metrics'] ?? [];
            $trendData = $stats['trend_data'] ?? [];
            $departmentBreakdown = $stats['department_breakdown'] ?? [];
            $statusDistribution = $stats['status_distribution'] ?? [];
            $topPerformers = $stats['top_performers'] ?? [];
            $recentActivity = $stats['recent_activity'] ?? [];
        @endphp

        {{-- Main Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            {{-- Total Entries --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Entries</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($mainStats['total_entries'] ?? 0) }}</p>
                        @if(($mainStats['entries_change'] ?? 0) != 0)
                            <p class="text-xs flex items-center mt-1 {{ ($mainStats['entries_change'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                @if(($mainStats['entries_change'] ?? 0) >= 0)
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                {{ abs($mainStats['entries_change'] ?? 0) }}%
                            </p>
                        @endif
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Users --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($mainStats['total_users'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Active today: {{ $mainStats['active_users_today'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <x-heroicon-o-user-group class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            {{-- Completion Rate --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completion Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $mainStats['completion_rate'] ?? 0 }}%</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $mainStats['completion_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Success Rate --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $performanceMetrics['success_rate'] ?? 0 }}%</p>
                        @if(($performanceMetrics['success_rate_change'] ?? 0) != 0)
                            <p class="text-xs flex items-center mt-1 {{ ($performanceMetrics['success_rate_change'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                @if(($performanceMetrics['success_rate_change'] ?? 0) >= 0)
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                {{ abs($performanceMetrics['success_rate_change'] ?? 0) }}%
                            </p>
                        @endif
                    </div>
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Avg Response Time --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Response Time</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $performanceMetrics['avg_response_time'] ?? 0 }}<span class="text-sm font-normal">min</span></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Target: {{ $performanceMetrics['target_processing_time'] ?? 24 }}h</p>
                    </div>
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-full">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Pending Queue --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Queue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['pending_queue'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Awaiting review</p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts and Additional Info --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
            {{-- Trend Chart --}}
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Entry Trends</h3>
                @if(!empty($trendData))
                    <div class="h-64">
                        <canvas id="trendChart-{{ uniqid() }}" class="trend-chart w-full h-full"></canvas>
                    </div>
                @else
                    <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p>No trend data available</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Status Distribution --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Distribution</h3>
                @if(!empty($statusDistribution))
                    <div class="space-y-3">
                        @foreach($statusDistribution as $status)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full mr-3 {{
                                        $status['status'] === 'approved' ? 'bg-green-500' :
                                        ($status['status'] === 'submitted' ? 'bg-yellow-500' :
                                        ($status['status'] === 'rejected' ? 'bg-blue-500' : 'bg-red-500'))
                                    }}"></div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $status['status'] }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">{{ $status['count'] }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $status['percentage'] }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 {{
                                    $status['status'] === 'approved' ? 'bg-green-500' :
                                    ($status['status'] === 'submitted' ? 'bg-yellow-500' :
                                    ($status['status'] === 'rejected' ? 'bg-blue-500' : 'bg-red-500'))
                                }}" style="width: {{ $status['percentage'] }}%"></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-32 text-gray-500 dark:text-gray-400">
                        <p>No status data available</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Department Breakdown and Top Performers --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Department Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Department Breakdown</h3>
                @if(!empty($departmentBreakdown))
                    <div class="space-y-4">
                        @foreach($departmentBreakdown as $dept)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $dept['name'] }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $dept['count'] }} ({{ $dept['percentage'] }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $dept['percentage'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-32 text-gray-500 dark:text-gray-400">
                        <p>No department data available</p>
                    </div>
                @endif
            </div>

            {{-- Top Performers --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Performers</h3>
                @if(!empty($topPerformers))
                    <div class="space-y-4">
                        @foreach($topPerformers as $index => $performer)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $performer['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $performer['department'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $performer['entries_count'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">entries</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-32 text-gray-500 dark:text-gray-400">
                        <p>No performer data available</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
            @if(!empty($recentActivity))
                <div class="recent-activity-container">
                    <ul class="-mb-8">
                        @foreach($recentActivity as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if($index < count($recentActivity) - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3 activity-item">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 activity-status-badge {{
                                                $activity['status'] === 'approved' ? 'bg-green-500' :
                                                ($activity['status'] === 'submitted' ? 'bg-yellow-500' :
                                                ($activity['status'] === 'rejected' ? 'bg-blue-500' : 'bg-red-500'))
                                            }}">
                                                @if($activity['status'] === 'approved')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($activity['status'] === 'submitted')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($activity['status'] === 'rejected')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $activity['user_name'] }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400"> {{ $activity['action'] }}</span>
                                                </div>
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $activity['department'] }} • {{ $activity['time_ago'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="flex items-center justify-center h-32 text-gray-500 dark:text-gray-400">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>No recent activity</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Chart.js Script --}}
        @if(!empty($trendData))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Function to initialize chart
            function initializeTrendChart() {
                // Clear any existing charts first
                const existingCharts = Chart.getChart('trendChart') || [];
                if (existingCharts.length) {
                    existingCharts.forEach(chart => chart.destroy());
                }

                // Find the canvas element
                const canvas = document.querySelector('.trend-chart');
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                const trendData = @json($trendData);

                // Destroy existing chart instance if exists
                if (window.trendChartInstance) {
                    window.trendChartInstance.destroy();
                }

                // Create new chart
                window.trendChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendData.map(item => item.date),
                        datasets: [{
                            label: 'Entries',
                            data: trendData.map(item => item.count),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    color: 'rgb(107, 114, 128)'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    color: 'rgb(107, 114, 128)'
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        elements: {
                            point: {
                                hoverBackgroundColor: 'rgb(59, 130, 246)'
                            }
                        }
                    }
                });
            }

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeTrendChart);
            } else {
                initializeTrendChart();
            }

            // Re-initialize when Livewire updates (for SPA compatibility)
            document.addEventListener('livewire:navigated', function() {
                setTimeout(initializeTrendChart, 100);
            });

            // Also handle Livewire component updates
            document.addEventListener('livewire:load', function() {
                setTimeout(initializeTrendChart, 100);
            });

            // Handle Turbo/SPA navigation (if using Turbo)
            document.addEventListener('turbo:load', function() {
                setTimeout(initializeTrendChart, 100);
            });

            // Fallback: Use MutationObserver to detect when canvas is added to DOM
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        const canvas = document.querySelector('.trend-chart');
                        if (canvas && !window.trendChartInstance) {
                            setTimeout(initializeTrendChart, 100);
                        }
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        </script>
        @endif

        {{-- Custom Styles --}}
        <style>
            .recent-activity-container {
                max-height: 450px;
                overflow-y: auto;
                overflow-x: hidden;
                padding-right: 4px;
                margin-right: -4px;
            }

            .recent-activity-container::-webkit-scrollbar {
                width: 8px;
            }

            .recent-activity-container::-webkit-scrollbar-track {
                background: rgba(229, 231, 235, 0.3);
                border-radius: 10px;
                margin: 8px 0;
            }

            .recent-activity-container::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #6b7280, #9ca3af);
                border-radius: 10px;
                box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .recent-activity-container::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #4b5563, #6b7280);
                box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
                transform: scaleY(1.1);
            }

            /* Dark mode scrollbar */
            .dark .recent-activity-container::-webkit-scrollbar-track {
                background: rgba(55, 65, 81, 0.3);
            }

            .dark .recent-activity-container::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #374151, #4b5563);
            }

            .dark .recent-activity-container::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #4b5563, #6b7280);
            }

            .recent-activity-container::before {
                content: '';
                position: sticky;
                top: 0;
                left: 0;
                right: 0;
                height: 20px;
                background: linear-gradient(to bottom, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
                z-index: 1;
                pointer-events: none;
            }

            .dark .recent-activity-container::before {
                background: linear-gradient(to bottom, rgba(31, 41, 55, 1), rgba(31, 41, 55, 0));
            }

            .recent-activity-container::after {
                content: '';
                position: sticky;
                bottom: 0;
                left: 0;
                right: 0;
                height: 20px;
                background: linear-gradient(to top, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
                z-index: 1;
                pointer-events: none;
            }

            .dark .recent-activity-container::after {
                background: linear-gradient(to top, rgba(31, 41, 55, 1), rgba(31, 41, 55, 0));
            }

            /* Improved hover effect untuk activity items */
            .activity-item {
                padding: 8px 12px;
                margin: -8px -12px;
                border-radius: 8px;
                transition: all 0.2s ease;
                position: relative;
            }

            .activity-item:hover {
                background-color: rgba(59, 130, 246, 0.05);
                transform: translateX(4px);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .dark .activity-item:hover {
                background-color: rgba(59, 130, 246, 0.08);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }

            /* Enhanced status badge dengan subtle animation */
            .activity-status-badge {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
            }

            .activity-status-badge::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                transform: rotate(45deg);
                transition: all 0.6s ease;
                opacity: 0;
            }

            .activity-item:hover .activity-status-badge::before {
                animation: shimmer 0.6s ease-in-out;
            }

            @keyframes shimmer {
                0% {
                    transform: translateX(-200%) translateY(-200%) rotate(45deg);
                    opacity: 0;
                }
                50% {
                    opacity: 1;
                }
                100% {
                    transform: translateX(200%) translateY(200%) rotate(45deg);
                    opacity: 0;
                }
            }

            /* Enhanced pulse animation untuk pending status */
            .bg-yellow-500.activity-status-badge {
                animation: subtlePulse 2s ease-in-out infinite;
            }

            @keyframes subtlePulse {
                0%, 100% {
                    opacity: 1;
                    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
                }
                50% {
                    opacity: 0.9;
                    box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
                }
            }

            /* Smooth scrolling behavior */
            .recent-activity-container {
                scroll-behavior: smooth;
            }

            /* Better mobile responsiveness untuk recent activity */
            @media (max-width: 768px) {
                .recent-activity-container {
                    max-height: 350px;
                    padding-right: 2px;
                    margin-right: -2px;
                }

                .recent-activity-container::-webkit-scrollbar {
                    width: 6px;
                }

                .activity-item {
                    padding: 6px 8px;
                    margin: -6px -8px;
                }
            }

            /* Loading indicator untuk recent activity */
            .recent-activity-loading {
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                animation: shimmerLoad 1.5s infinite;
                border-radius: 8px;
                height: 60px;
                margin-bottom: 16px;
            }

            @keyframes shimmerLoad {
                0% {
                    background-position: -200% 0;
                }
                100% {
                    background-position: 200% 0;
                }
            }

            .dark .recent-activity-loading {
                background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
                background-size: 200% 100%;
            }

            /* Animation for cards */
            .bg-white, .dark .bg-gray-800 {
                transition: all 0.3s ease;
            }

            .bg-white:hover, .dark .bg-gray-800:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            .dark .bg-white:hover, .dark .bg-gray-800:hover {
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            }

            /* Gradient animations */
            @keyframes gradientShift {
                0% {
                    background-position: 0% 50%;
                }
                50% {
                    background-position: 100% 50%;
                }
                100% {
                    background-position: 0% 50%;
                }
            }

            @keyframes shimmer {
                0% {
                    background-position: -200% 0;
                }
                100% {
                    background-position: 200% 0;
                }
            }

            /* Responsive improvements */
            @media (max-width: 768px) {
                .grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-6 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .text-2xl {
                    font-size: 1.5rem;
                }

                .p-6 {
                    padding: 1rem;
                }

                .p-4 {
                    padding: 0.75rem;
                }
            }

            /* Chart container responsive */
            .h-64 {
                position: relative;
            }

            @media (max-width: 640px) {
                .h-64 {
                    height: 200px;
                }
            }

            /* Status badge improvements */
            .capitalize {
                position: relative;
            }

            .capitalize::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0;
                height: 2px;
                background: currentColor;
                transition: width 0.3s ease;
            }

            .capitalize:hover::after {
                width: 100%;
            }

            /* Top performers ranking badge */
            .flex-shrink-0.w-8.h-8 {
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .flex-shrink-0.w-8.h-8:hover {
                transform: scale(1.1);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            /* Progress bar animations */
            .h-2.rounded-full {
                overflow: hidden;
                position: relative;
            }

            .h-2.rounded-full::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
                animation: progressShine 2s infinite;
            }

            @keyframes progressShine {
                0% {
                    left: -100%;
                }
                100% {
                    left: 100%;
                }
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
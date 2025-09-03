<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section with Period Filter -->
        <div class="rounded-xl p-6 shadow-lg dark:shadow-2xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold mb-2 flex items-center">
                        <x-heroicon-s-trophy class="w-8 h-8 mr-3 text-yellow-300 dark:text-yellow-300 " />
                        {{ $departmentName }} Team Leaderboard
                    </h1>
                    <p class="text-gray-500 dark:text-white/70">
                        {{ $this->getPeriodLabel() }} • {{ $this->getDateRangeLabel() }}
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <div class="relative">
                        <select wire:model.live="selectedPeriod"
                                class="appearance-none bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-500 dark:border-white/20 text-black dark:text-white placeholder-white/70 rounded-lg px-8 py-3 pr-10 min-w-[180px] focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-white/40 focus:border-white/50 dark:focus:border-white/40 transition-all duration-200 cursor-pointer hover:bg-white/20 dark:hover:bg-white/10">
                            @foreach($periodOptions as $value => $label)
                                <option value="{{ $value }}" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-10 pointer-events-none">
                            {{-- <x-heroicon-s-chevron-down class="w-4 h-4 text-white/70 dark:text-white/50" /> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- Team Overview Stats -->
         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($teamStats as $stat)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                            {{ $stat['title'] }}
                        </div>
                        <x-dynamic-component :component="$stat['icon']" class="w-6 h-6 {{ $stat['color'] }}" />
                    </div>

                    <div class="text-3xl font-bold {{ $stat['color'] }} mb-2">
                        {{ $stat['value'] }}
                    </div>

                    @if(isset($stat['subtitle']))
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $stat['subtitle'] }}</div>
                    @endif

                    @if(isset($stat['description']))
                        <div class="text-xs {{ $stat['color'] }} font-medium">{{ $stat['description'] }}</div>
                    @endif

                    @if(str_contains($stat['value'], '%'))
                        @php
                            $percent = (float) str_replace('%', '', $stat['value']);
                            $progressColor = $percent >= 90 ? 'bg-green-500' : ($percent >= 75 ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $progressColor }} transition-all duration-700 ease-out"
                                    style="width: {{ min($percent, 100) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>


         <!-- Leaderboard Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-trophy class="w-6 h-6 text-yellow-600 mr-2" />
                    Team Performance Leaderboard
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Ranked by overall performance score</p>
            </div>

            @if(count($individualStats) > 0)
                <!-- Top 3 Podium (Desktop) -->
                <div class="hidden lg:block bg-gradient-to-r from-yellow-50 via-gray-50 to-orange-50 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 px-6 py-8">
                    <div class="flex justify-center items-end space-x-8">
                        @if(isset($individualStats[1]))
                            <div class="text-center">
                                <div class="w-20 h-20 bg-gradient-to-r from-gray-300 to-gray-500 rounded-full flex items-center justify-center text-white font-bold text-2xl mb-3 mx-auto shadow-lg">
                                    2
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 border-2 border-gray-300 dark:border-gray-600">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $individualStats[1]['name'] }}</div>
                                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-300 mt-1">{{ $individualStats[1]['overall_score'] }}%</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $individualStats[1]['rank_badge']['label'] }}</div>
                                </div>
                            </div>
                        @endif

                        @if(isset($individualStats[0]))
                            <div class="text-center">
                                <div class="w-24 h-24 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center text-white font-bold text-3xl mb-3 mx-auto shadow-xl">
                                    <x-heroicon-s-star class="w-12 h-12" />
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 border-4 border-yellow-400">
                                    <div class="font-bold text-gray-900 dark:text-white text-lg">{{ $individualStats[0]['name'] }}</div>
                                    <div class="text-3xl font-bold text-yellow-600 mt-2">{{ $individualStats[0]['overall_score'] }}%</div>
                                    <div class="text-sm text-yellow-600 font-medium">{{ $individualStats[0]['rank_badge']['label'] }}</div>
                                </div>
                            </div>
                        @endif

                        @if(isset($individualStats[2]))
                            <div class="text-center">
                                <div class="w-20 h-20 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mb-3 mx-auto shadow-lg">
                                    3
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 border-2 border-orange-300 dark:border-orange-500">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $individualStats[2]['name'] }}</div>
                                    <div class="text-2xl font-bold text-orange-600 mt-1">{{ $individualStats[2]['overall_score'] }}%</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $individualStats[2]['rank_badge']['label'] }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detailed Leaderboard Table -->
                <div class="overflow-x-auto">
                    <!-- Desktop Table -->
                    <div class="hidden md:block">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rank & Member</th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Overall Score</th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Performance</th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submissions</th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Streak</th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($individualStats as $index => $person)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 {{ $index < 3 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20' : '' }}">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <!-- Rank Badge -->
                                                <div class="w-10 h-10 rounded-full {{ $person['rank_badge']['color'] }} flex items-center justify-center text-white font-bold mr-4 shadow-md">
                                                    @if($person['rank'] <= 3)
                                                        <x-dynamic-component :component="$person['rank_badge']['icon']" class="w-5 h-5" />
                                                    @else
                                                        {{ $person['rank'] }}
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-white">{{ $person['name'] }}</div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $person['rank_badge']['label'] }} Level</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-2xl font-bold {{ $person['overall_score'] >= 90 ? 'text-green-600' : ($person['overall_score'] >= 75 ? 'text-blue-600' : 'text-gray-600') }}">
                                                {{ $person['overall_score'] }}%
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                                                <div class="h-2 rounded-full {{ $person['overall_score'] >= 90 ? 'bg-green-500' : ($person['overall_score'] >= 75 ? 'bg-blue-500' : 'bg-gray-500') }} transition-all duration-500"
                                                     style="width: {{ min($person['overall_score'], 100) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-lg font-bold {{ $person['avg_performance'] >= 90 ? 'text-green-600' : ($person['avg_performance'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $person['avg_performance'] }}%
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $person['submissions'] }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $person['approval_rate'] }}% approved</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($person['streak'] > 0)
                                                <div class="flex items-center justify-center space-x-1">
                                                    <x-heroicon-s-fire class="w-5 h-5 text-orange-500" />
                                                    <span class="text-lg font-bold text-orange-600">{{ $person['streak'] }}</span>
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">days</div>
                                            @else
                                                <div class="text-sm text-gray-400">No Streak</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center space-y-1">
                                                @if($person['submitted_today'])
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                        <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                                        Today
                                                    </span>
                                                @endif
                                                <span class="text-sm font-medium {{ $person['status']['color'] }}">
                                                    {{ $person['status']['text'] }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4 p-4">
                        @foreach($individualStats as $index => $person)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border {{ $index < 3 ? 'border-yellow-300 dark:border-yellow-600 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20' : 'border-gray-200 dark:border-gray-700' }} p-4">
                                <!-- Header with Rank and Name -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full {{ $person['rank_badge']['color'] }} flex items-center justify-center text-white font-bold mr-3 shadow-md">
                                            @if($person['rank'] <= 3)
                                                <x-dynamic-component :component="$person['rank_badge']['icon']" class="w-6 h-6" />
                                            @else
                                                {{ $person['rank'] }}
                                            @endif
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $person['name'] }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $person['rank_badge']['label'] }} Level</p>
                                        </div>
                                    </div>
                                    @if($person['submitted_today'])
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                            <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                            Today
                                        </span>
                                    @endif
                                </div>

                                <!-- Overall Score -->
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Overall Score</span>
                                        <span class="text-2xl font-bold {{ $person['overall_score'] >= 90 ? 'text-green-600' : ($person['overall_score'] >= 75 ? 'text-blue-600' : 'text-gray-600') }}">
                                            {{ $person['overall_score'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        <div class="h-3 rounded-full {{ $person['overall_score'] >= 90 ? 'bg-green-500' : ($person['overall_score'] >= 75 ? 'bg-blue-500' : 'bg-gray-500') }} transition-all duration-500"
                                             style="width: {{ min($person['overall_score'], 100) }}%"></div>
                                    </div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="text-lg font-bold {{ $person['avg_performance'] >= 90 ? 'text-green-600' : ($person['avg_performance'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $person['avg_performance'] }}%
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Performance</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $person['submissions'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Submissions</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="text-lg font-bold {{ $person['approval_rate'] >= 90 ? 'text-green-600' : ($person['approval_rate'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $person['approval_rate'] }}%
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Approval Rate</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                        @if($person['streak'] > 0)
                                            <div class="flex items-center justify-center space-x-1">
                                                <x-heroicon-s-fire class="w-4 h-4 text-orange-500" />
                                                <span class="text-lg font-bold text-orange-600">{{ $person['streak'] }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Day Streak</div>
                                        @else
                                            <div class="text-sm text-gray-400">No Streak</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mt-3 text-center">
                                    <span class="text-sm font-medium {{ $person['status']['color'] }}">
                                        {{ $person['status']['text'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-user-group class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
                    <p class="text-lg font-medium">No team members found</p>
                    <p class="text-sm">No data available for the selected period.</p>
                </div>
            @endif
        </div>

        <!-- Performance Insights -->
        @if(count($individualStats) > 0)
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Top Performers -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-xl p-6 border border-green-200 dark:border-green-700">
                    <h3 class="text-lg font-bold text-green-800 dark:text-green-300 mb-4 flex items-center">
                        <x-heroicon-o-star class="w-5 h-5 mr-2" />
                        Top Performers
                    </h3>
                    @php
                        $topPerformers = array_slice($individualStats, 0, 3);
                    @endphp
                    <div class="space-y-3">
                        @foreach($topPerformers as $performer)
                            <div class="flex justify-between items-center bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $performer['name'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $performer['rank_badge']['label'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-green-600">{{ $performer['overall_score'] }}%</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Overall Score</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Activity Leaders -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
                    <h3 class="text-lg font-bold text-blue-800 dark:text-blue-300 mb-4 flex items-center">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-2" />
                        Activity Leaders
                    </h3>
                    @php
                        $activityLeaders = collect($individualStats)->sortByDesc('submissions')->take(3);
                    @endphp
                    <div class="space-y-3">
                        @foreach($activityLeaders as $leader)
                            <div class="flex justify-between items-center bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $leader['name'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Most Active</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-blue-600">{{ $leader['submissions'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Submissions</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Streak Champions -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/30 dark:to-red-900/30 rounded-xl p-6 border border-orange-200 dark:border-orange-700">
                    <h3 class="text-lg font-bold text-orange-800 dark:text-orange-300 mb-4 flex items-center">
                        <x-heroicon-o-fire class="w-5 h-5 mr-2" />
                        Streak Champions
                    </h3>
                    @php
                        $streakLeaders = collect($individualStats)->sortByDesc('streak')->take(3);
                    @endphp
                    <div class="space-y-3">
                        @foreach($streakLeaders as $streaker)
                            <div class="flex justify-between items-center bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $streaker['name'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Consistent</div>
                                </div>
                                <div class="text-right">
                                    @if($streaker['streak'] > 0)
                                        <div class="flex items-center justify-end space-x-1">
                                            <x-heroicon-s-fire class="w-4 h-4 text-orange-500" />
                                            <span class="font-bold text-orange-600">{{ $streaker['streak'] }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Days</div>
                                    @else
                                        <div class="text-sm text-gray-400">No Streak</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Team Summary -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
                <h3 class="text-lg font-bold text-purple-800 dark:text-purple-300 mb-4 flex items-center">
                    <x-heroicon-o-chart-pie class="w-5 h-5 mr-2" />
                    Team Summary for {{ $this->getPeriodLabel() }}
                </h3>

                @php
                    $totalMembers = count($individualStats);
                    $activeToday = count(array_filter($individualStats, fn($p) => $p['submitted_today']));
                    $highPerformers = count(array_filter($individualStats, fn($p) => $p['overall_score'] >= 90));
                    $avgTeamScore = $totalMembers > 0 ? round(array_sum(array_column($individualStats, 'overall_score')) / $totalMembers, 1) : 0;
                    $withStreaks = count(array_filter($individualStats, fn($p) => $p['streak'] > 0));
                @endphp

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-2xl font-bold text-purple-600">{{ $totalMembers }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Members</div>
                    </div>
                    <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-2xl font-bold text-green-600">{{ $activeToday }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Active Today</div>
                    </div>
                    <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-2xl font-bold text-blue-600">{{ $highPerformers }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">High Performers (90%+)</div>
                    </div>
                    <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-2xl font-bold text-indigo-600">{{ $avgTeamScore }}%</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Avg Team Score</div>
                    </div>
                    <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-2xl font-bold text-orange-600">{{ $withStreaks }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">With Streaks</div>
                    </div>
                </div>

                <!-- Team Progress Bar -->
                <div class="mt-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Team Average Performance</span>
                        <span>{{ $avgTeamScore }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                        <div class="h-4 rounded-full {{ $avgTeamScore >= 90 ? 'bg-green-500' : ($avgTeamScore >= 75 ? 'bg-blue-500' : 'bg-yellow-500') }} transition-all duration-1000 ease-out"
                             style="width: {{ min($avgTeamScore, 100) }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
                        @if($avgTeamScore >= 90)
                            Excellent team performance! Keep up the great work!
                        @elseif($avgTeamScore >= 75)
                            Good team performance with room for improvement
                        @else
                            Team needs improvement - focus on consistency and quality
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
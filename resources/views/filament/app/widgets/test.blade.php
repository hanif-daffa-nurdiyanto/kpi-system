<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-blue-600" />
                <span class="text-xl font-bold text-gray-900">Manager Dashboard Overview</span>
            </div>
        </x-slot>

        <div class="space-y-6">
            <div class="overflow-x-auto">
                <div class="flex gap-4 px-2 py-2 min-w-fit">
                    @foreach($this->cards as $card)
                        <div class="relative flex-shrink-0 w-[260px] rounded-xl border border-gray-200 {{ $card['bg'] ?? 'bg-white' }} p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-10 pointer-events-none">
                                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full {{ str_replace('bg-', 'bg-', $card['bg'] ?? 'bg-gray-100') }}"></div>
                                <div class="absolute -bottom-2 -left-2 h-8 w-8 rounded-full {{ str_replace('bg-', 'bg-', $card['bg'] ?? 'bg-gray-100') }}"></div>
                            </div>

                            <!-- Content -->
                            <div class="relative">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-sm font-medium text-gray-600 leading-tight">
                                        {{ $card['title'] }}
                                    </h3>
                                    @if(isset($card['icon']))
                                        <div class="p-2 rounded-md {{ $card['bg'] ?? 'bg-gray-100' }}">
                                            <x-dynamic-component
                                                :component="$card['icon']"
                                                class="w-5 h-5 {{ $card['color'] ?? 'text-gray-600' }}"
                                            />
                                        </div>
                                    @endif
                                </div>

                                <!-- Main Value -->
                                <div class="mb-2">
                                    <div class="flex items-baseline space-x-2">
                                        <span class="text-xl font-bold {{ $card['color'] ?? 'text-gray-900' }}">
                                            {{ $card['value'] }}
                                        </span>
                                        @if(isset($card['subtitle']))
                                            <span class="text-sm text-gray-500 font-medium">
                                                {{ $card['subtitle'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Description -->
                                @if(isset($card['description']))
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        {{ $card['description'] }}
                                    </p>
                                @endif

                                <!-- Progress Bar (for percentage values) -->
                                @if(str_contains($card['value'], '%'))
                                    @php
                                        $percentage = (int) str_replace('%', '', $card['value']);
                                        $progressColor = $percentage >= 90 ? 'bg-green-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-red-500');
                                    @endphp
                                    <div class="mt-3">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-300"
                                                 style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Performance Insights Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <!-- Today's Performance Summary -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <div class="flex items-center mb-4">
                        <x-heroicon-o-clock class="w-6 h-6 text-blue-600 mr-2" />
                        <h3 class="text-lg font-semibold text-gray-900">Today's Overview</h3>
                    </div>

                    @php
                        $todaySubmissions = collect($this->cards)->firstWhere('title', "Today's Submissions");
                        $pendingApprovals = collect($this->cards)->firstWhere('title', 'Pending Approvals');
                        $todayPerformance = collect($this->cards)->firstWhere('title', "Today's Avg Performance");
                        $achievedTargets = collect($this->cards)->firstWhere('title', 'Targets Achieved Today');
                    @endphp

                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-sm text-gray-700">Team Submissions</span>
                            <span class="font-medium text-gray-900">{{ $todaySubmissions['value'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-sm text-gray-700">Avg Performance</span>
                            <span class="font-medium {{ $todayPerformance['color'] ?? 'text-gray-900' }}">
                                {{ $todayPerformance['value'] ?? '0%' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-sm text-gray-700">Targets Achieved</span>
                            <span class="font-medium text-green-600">{{ $achievedTargets['value'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-700">Pending Approvals</span>
                            <span class="font-medium {{ $pendingApprovals['color'] ?? 'text-gray-900' }}">
                                {{ $pendingApprovals['value'] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Items -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-6 border border-orange-200">
                    <div class="flex items-center mb-4">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-orange-600 mr-2" />
                        <h3 class="text-lg font-semibold text-gray-900">Action Required</h3>
                    </div>

                    @php
                        $pendingCount = collect($this->cards)->firstWhere('title', 'Pending Approvals')['value'] ?? 0;
                        $belowTarget = collect($this->cards)->firstWhere('title', 'Below Target Today')['value'] ?? 0;
                        $totalEmployees = collect($this->cards)->firstWhere('title', 'Total Team Members')['value'] ?? 0;
                        $submissionsToday = collect($this->cards)->firstWhere('title', "Today's Submissions")['value'] ?? 0;
                        $missingSubmissions = max(0, $totalEmployees - $submissionsToday);
                    @endphp

                    <div class="space-y-4">
                        @if($pendingCount > 0)
                            <div class="flex items-start space-x-3 p-3 bg-white rounded-lg">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Review Pending Approvals</p>
                                    <p class="text-xs text-gray-600">{{ $pendingCount }} entries need your approval</p>
                                </div>
                            </div>
                        @endif

                        @if($belowTarget > 0)
                            <div class="flex items-start space-x-3 p-3 bg-white rounded-lg">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Support Underperforming Team</p>
                                    <p class="text-xs text-gray-600">{{ $belowTarget }} entries below 70% target</p>
                                </div>
                            </div>
                        @endif

                        @if($missingSubmissions > 0)
                            <div class="flex items-start space-x-3 p-3 bg-white rounded-lg">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Follow Up Missing Submissions</p>
                                    <p class="text-xs text-gray-600">{{ $missingSubmissions }} team members haven't submitted today</p>
                                </div>
                            </div>
                        @endif

                        @if($pendingCount == 0 && $belowTarget == 0 && $missingSubmissions == 0)
                            <div class="flex items-center justify-center py-8">
                                <div class="text-center">
                                    <x-heroicon-o-check-circle class="w-12 h-12 text-green-500 mx-auto mb-2" />
                                    <p class="text-sm font-medium text-gray-900">Great job!</p>
                                    <p class="text-xs text-gray-600">No immediate actions needed</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Performance Trend Indicators -->
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center mb-6">
                    <x-heroicon-o-chart-bar-square class="w-6 h-6 text-purple-600 mr-2" />
                    <h3 class="text-lg font-semibold text-gray-900">Performance Indicators</h3>
                </div>

                @php
                    $monthlyPerf = collect($this->cards)->firstWhere('title', 'Monthly Avg Performance');
                    $todayPerf = collect($this->cards)->firstWhere('title', "Today's Avg Performance");
                    $weeklyProd = collect($this->cards)->firstWhere('title', 'Weekly Productivity');

                    $monthlyValue = (int) str_replace('%', '', $monthlyPerf['value'] ?? '0');
                    $todayValue = (int) str_replace('%', '', $todayPerf['value'] ?? '0');
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Monthly Performance -->
                    <div class="text-center">
                        <div class="relative w-24 h-24 mx-auto mb-3">
                            <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none"
                                        stroke="{{ $monthlyValue >= 90 ? '#10b981' : ($monthlyValue >= 70 ? '#f59e0b' : '#ef4444') }}"
                                        stroke-width="8"
                                        stroke-dasharray="{{ 2.51 * $monthlyValue }} 251"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-gray-900">{{ $monthlyValue }}%</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Monthly Average</p>
                        <p class="text-xs text-gray-600">Overall team performance</p>
                    </div>

                    <!-- Today Performance -->
                    <div class="text-center">
                        <div class="relative w-24 h-24 mx-auto mb-3">
                            <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none"
                                        stroke="{{ $todayValue >= 90 ? '#10b981' : ($todayValue >= 70 ? '#f59e0b' : '#ef4444') }}"
                                        stroke-width="8"
                                        stroke-dasharray="{{ 2.51 * $todayValue }} 251"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-gray-900">{{ $todayValue }}%</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Today's Average</p>
                        <p class="text-xs text-gray-600">Current day performance</p>
                    </div>

                    <!-- Weekly Activity -->
                    <div class="text-center">
                        <div class="relative w-24 h-24 mx-auto mb-3 flex items-center justify-center">
                            <div class="bg-purple-100 rounded-full w-24 h-24 flex items-center justify-center">
                                <span class="text-xl font-bold text-purple-600">{{ $weeklyProd['value'] ?? 0 }}</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Weekly Entries</p>
                        <p class="text-xs text-gray-600">7-day productivity</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
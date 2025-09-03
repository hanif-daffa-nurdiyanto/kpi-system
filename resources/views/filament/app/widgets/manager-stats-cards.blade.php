<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2 animate-fade-in">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100">Manager Dashboard Overview</span>
            </div>
        </x-slot>

        <div class="space-y-6">
            <div class="overflow-x-auto">
                <div class="flex gap-4 px-2 py-2 min-w-fit">
                    @foreach($this->cards as $index => $card)
                        @php
                            $bgClass = $card['bg'] ?? 'bg-white';
                            $darkBgClass = match($bgClass) {
                                'bg-blue-50' => 'bg-blue-50 dark:bg-blue-950/50',
                                'bg-green-50' => 'bg-green-50 dark:bg-green-950/50',
                                'bg-yellow-50' => 'bg-yellow-50 dark:bg-yellow-950/50',
                                'bg-red-50' => 'bg-red-50 dark:bg-red-950/50',
                                'bg-purple-50' => 'bg-purple-50 dark:bg-purple-950/50',
                                'bg-indigo-50' => 'bg-indigo-50 dark:bg-indigo-950/50',
                                'bg-gray-50' => 'bg-gray-50 dark:bg-gray-800',
                                default => 'bg-white dark:bg-gray-800'
                            };
                            $colorClass = $card['color'] ?? 'text-gray-900';
                            $darkColorClass = match($colorClass) {
                                'text-blue-600' => 'text-blue-600 dark:text-blue-400',
                                'text-green-600' => 'text-green-600 dark:text-green-400',
                                'text-yellow-600' => 'text-yellow-600 dark:text-yellow-400',
                                'text-red-600' => 'text-red-600 dark:text-red-400',
                                'text-purple-600' => 'text-purple-600 dark:text-purple-400',
                                'text-indigo-600' => 'text-indigo-600 dark:text-indigo-400',
                                'text-gray-600' => 'text-gray-600 dark:text-gray-400',
                                default => 'text-gray-900 dark:text-gray-100'
                            };
                            $iconBgClass = match($bgClass) {
                                'bg-blue-50' => 'bg-blue-100 dark:bg-blue-900/30',
                                'bg-green-50' => 'bg-green-100 dark:bg-green-900/30',
                                'bg-yellow-50' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                'bg-red-50' => 'bg-red-100 dark:bg-red-900/30',
                                'bg-purple-50' => 'bg-purple-100 dark:bg-purple-900/30',
                                'bg-indigo-50' => 'bg-indigo-100 dark:bg-indigo-900/30',
                                'bg-gray-50' => 'bg-gray-100 dark:bg-gray-700',
                                default => 'bg-gray-100 dark:bg-gray-700'
                            };
                        @endphp

                        <div class="relative flex-shrink-0 w-[260px] rounded-xl border border-gray-200 dark:border-gray-700 {{ $darkBgClass }} p-6 shadow-sm hover:shadow-md dark:shadow-gray-900/20 dark:hover:shadow-gray-900/40 transition-all duration-200 animate-slide-up"
                             style="animation-delay: {{ $index * 100 }}ms;">
                            <div class="absolute inset-0 opacity-10 dark:opacity-5 pointer-events-none">
                                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full {{ $iconBgClass }}"></div>
                                <div class="absolute -bottom-2 -left-2 h-8 w-8 rounded-full {{ $iconBgClass }}"></div>
                            </div>

                            <!-- Content -->
                            <div class="relative">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 leading-tight">
                                        {{ $card['title'] }}
                                    </h3>
                                    @if(isset($card['icon']))
                                        <div class="p-1 rounded-md {{ $iconBgClass }}">
                                            <x-dynamic-component
                                                :component="$card['icon']"
                                                class="w-5 h-5 {{ $darkColorClass }}"
                                            />
                                        </div>
                                    @endif
                                </div>

                                <!-- Main Value -->
                                <div class="mb-2">
                                    <div class="flex items-baseline space-x-2">
                                        <span class="text-xl font-bold {{ $darkColorClass }} animate-count-up">
                                            {{ $card['value'] }}
                                        </span>
                                        @if(isset($card['subtitle']))
                                            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                                {{ $card['subtitle'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Description -->
                                @if(isset($card['description']))
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
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

                                <!-- Priority Indicator -->
                                @if(isset($card['priority']))
                                    <div class="absolute top-2 right-2">
                                        <div class="w-2 h-2 rounded-full {{
                                            $card['priority'] === 'high' ? 'bg-red-500' :
                                            ($card['priority'] === 'medium' ? 'bg-yellow-500' : 'bg-green-500')
                                        }}"></div>
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
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700/50 hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center mb-4">
                        <x-heroicon-o-clock class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-2" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Today's Overview</h3>
                    </div>

                    @php
                        $todaySubmissions = collect($this->cards)->firstWhere('title', "Today's Submissions");
                        $pendingApprovals = collect($this->cards)->firstWhere('title', 'Pending Approvals');
                        $todayPerformance = collect($this->cards)->firstWhere('title', "Today's Avg Performance");
                        $achievedTargets = collect($this->cards)->firstWhere('title', 'Targets Achieved Today');
                    @endphp

                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-blue-700/50 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors duration-200">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Team Submissions</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $todaySubmissions['value'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-blue-700/50 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors duration-200">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Avg Performance</span>
                            <span class="font-medium {{ $todayPerformance['color'] ?? 'text-gray-900 dark:text-gray-100' }}">
                                {{ $todayPerformance['value'] ?? '0%' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-blue-700/50 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors duration-200">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Targets Achieved</span>
                            <span class="font-medium text-green-600 dark:text-green-400">{{ $achievedTargets['value'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors duration-200">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Pending Approvals</span>
                            <span class="font-medium {{ $pendingApprovals['color'] ?? 'text-gray-900 dark:text-gray-100' }}">
                                {{ $pendingApprovals['value'] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>


                <!-- Action Items -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl p-6 border border-orange-200 dark:border-orange-700/50 hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center mb-4">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-orange-600 dark:text-orange-400 mr-2" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Action Required</h3>
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
                            <div class="flex items-start space-x-3 p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-sm transition-shadow duration-200">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 dark:bg-red-400 mt-2 animate-pulse"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Review Pending Approvals</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $pendingCount }} entries need your approval</p>
                                </div>
                            </div>
                        @endif

                        @if($belowTarget > 0)
                            <div class="flex items-start space-x-3 p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-sm transition-shadow duration-200">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 dark:bg-orange-400 mt-2 animate-pulse"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Support Underperforming Team</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $belowTarget }} entries below 70% target</p>
                                </div>
                            </div>
                        @endif

                        @if($missingSubmissions > 0)
                            <div class="flex items-start space-x-3 p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-sm transition-shadow duration-200">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 dark:bg-yellow-400 mt-2 animate-pulse"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Follow Up Missing Submissions</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $missingSubmissions }} team members haven't submitted today</p>
                                </div>
                            </div>
                        @endif

                        @if($pendingCount == 0 && $belowTarget == 0 && $missingSubmissions == 0)
                            <div class="flex items-center justify-center py-8">
                                <div class="text-center animate-bounce-soft">
                                    <x-heroicon-o-check-circle class="w-12 h-12 text-green-500 dark:text-green-400 mx-auto mb-2" />
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Great job!</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">No immediate actions needed</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Performance Trend Indicators -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md dark:hover:shadow-lg transition-shadow duration-300 animate-fade-in-up" style="animation-delay: 0.5s;">
                <div class="flex items-center mb-6">
                    <x-heroicon-o-chart-bar-square class="w-6 h-6 text-purple-600 dark:text-purple-400 mr-2" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Performance Indicators</h3>
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
                    <div class="text-center animate-scale-in" style="animation-delay: 0.7s;">
                        <div class="relative w-24 h-24 mx-auto mb-3">
                            <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" class="dark:stroke-gray-600" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none"
                                        stroke="{{ $monthlyValue >= 90 ? '#10b981' : ($monthlyValue >= 70 ? '#f59e0b' : '#ef4444') }}"
                                        class="{{ $monthlyValue >= 90 ? 'dark:stroke-green-400' : ($monthlyValue >= 70 ? 'dark:stroke-yellow-400' : 'dark:stroke-red-400') }}"
                                        stroke-width="8"
                                        stroke-dasharray="{{ 2.51 * $monthlyValue }} 251"
                                        stroke-linecap="round"
                                        id="monthly-circle">
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $monthlyValue }}%</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Monthly Average</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Overall team performance</p>
                    </div>

                    <!-- Today Performance -->
                    <div class="text-center animate-scale-in" style="animation-delay: 0.9s;">
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
                                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $todayValue }}%</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Today's Average</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Current day performance</p>
                    </div>

                    <!-- Weekly Activity -->
                    <div class="text-center animate-scale-in" style="animation-delay: 1.1s;">
                        <div class="relative w-24 h-24 mx-auto mb-3 flex items-center justify-center">
                            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-full w-24 h-24 flex items-center justify-center hover:scale-105 transition-transform duration-300">
                                <span class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $weeklyProd['value'] ?? 0 }}</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Weekly Entries</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">7-day productivity</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scale-in {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes progress-fill {
            from { width: 0%; }
            to { width: var(--target-width); }
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-slide-up {
            animation: slide-up 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-scale-in {
            animation: scale-in 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-progress {
            animation: progress-fill 1s ease-out forwards;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(239, 68, 68, 0.5); }
            50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.8); }
        }

        .priority-high {
            animation: pulse-glow 2s infinite;
        }

        .trend-up {
            animation: bounce 1s infinite;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelectorAll('.animate-progress').forEach(bar => {
                    const width = bar.getAttribute('data-width');
                    bar.style.width = width;
                });
            }, 100);
            function animateCounter(element, target, duration = 1000) {
                let start = 0;
                const increment = target / (duration / 16);

                function updateCounter() {
                    start += increment;
                    if (start < target) {
                        element.textContent = Math.ceil(start);
                        requestAnimationFrame(updateCounter);
                    } else {
                        element.textContent = target;
                    }
                }
                updateCounter();
            }
            function animateCircle(circle, percentage) {
                const circumference = 2 * Math.PI * 40;
                const offset = circumference - (percentage / 100) * circumference;

                setTimeout(() => {
                    circle.style.strokeDasharray = `${circumference} ${circumference}`;
                    circle.style.strokeDashoffset = circumference;

                    setTimeout(() => {
                        circle.style.transition = 'stroke-dashoffset 1.5s ease-out';
                        circle.style.strokeDashoffset = offset;
                    }, 100);
                }, 100);
            }
            setTimeout(() => {
                document.querySelectorAll('.animate-count-up[data-target]').forEach(counter => {
                    const target = parseInt(counter.getAttribute('data-target'));
                    animateCounter(counter, target);
                });

                document.querySelectorAll('.animate-circle-fill').forEach(circle => {
                    const percentage = parseInt(circle.getAttribute('data-percentage'));
                    animateCircle(circle, percentage);
                });
            }, 800);
        });
    </script>
</x-filament-widgets::widget>
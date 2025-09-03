<div class="fi-wi-stats-overview h-full">
    <div class="w-full h-full bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" />
                Daily KPI Overview
            </h2>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ now()->format('M j, Y') }}
            </div>
        </div>

        <div class="w-full">
            <div class="flex overflow-x-auto gap-3 pb-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                @forelse ($cards as $card)
                    <div class="min-w-[160px] bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200 group">

                        <div class="flex items-start justify-between mb-3">
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200 leading-tight">
                                {{ $card['name'] }}
                            </div>
                            <div class="flex-shrink-0 ml-2">
                                <x-dynamic-component
                                    :component="$card['icon']"
                                    class="w-4 h-4 {{ $card['color'] }} dark:opacity-90"
                                />
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="flex items-baseline justify-between">
                                <span class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($card['value'], $card['value'] == floor($card['value']) ? 0 : 1) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                    {{ $card['unit'] }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                Target: {{ number_format($card['target'], $card['target'] == floor($card['target']) ? 0 : 1) }} {{ $card['unit'] }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ strpos($card['status'], 'Excellent') !== false ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' :
                                   (strpos($card['status'], 'Target Achieved') !== false ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                   (strpos($card['status'], 'Near Target') !== false ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                   (strpos($card['status'], 'Making Progress') !== false ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' :
                                   (strpos($card['status'], 'Needs Attention') !== false ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300')))) }}">
                                <x-dynamic-component
                                    :component="$card['icon']"
                                    class="w-3 h-3 mr-1"
                                />
                                {{ $card['status'] }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600 dark:text-gray-300 font-medium">Progress</span>
                                <span class="text-xs font-bold {{ $card['color'] }} dark:opacity-90">{{ $card['progress'] }}%</span>
                            </div>

                            <div class="relative">
                                <div class="w-full bg-gray-200 dark:bg-gray-600 h-2 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700 ease-out
                                        {{ $card['progress'] >= 100 ? 'bg-gradient-to-r from-green-500 to-emerald-500 dark:from-green-400 dark:to-emerald-400' :
                                           ($card['progress'] >= 85 ? 'bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-400 dark:to-blue-500' :
                                           ($card['progress'] >= 70 ? 'bg-gradient-to-r from-amber-500 to-yellow-500 dark:from-amber-400 dark:to-yellow-400' :
                                           ($card['progress'] >= 50 ? 'bg-gradient-to-r from-orange-500 to-red-500 dark:from-orange-400 dark:to-red-400' : 'bg-gradient-to-r from-red-500 to-red-600 dark:from-red-400 dark:to-red-500'))) }}"
                                        style="width: {{ min($card['progress'], 100) }}%">
                                    </div>
                                </div>

                                @if($card['progress'] < 100)
                                    <div class="absolute top-0 right-0 w-0.5 h-2 bg-gray-400 dark:bg-gray-500"></div>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Ratio</span>
                                <span class="font-mono font-medium text-gray-700 dark:text-gray-300">
                                    {{ $card['achievement_ratio'] }}
                                </span>
                            </div>
                        </div> --}}
                    </div>
                @empty
                    <div class="w-full p-6 text-center bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <x-heroicon-o-chart-bar-square class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-3" />
                        <span class="text-gray-600 dark:text-gray-300 text-sm font-medium">No KPI data for today</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Check back after data entry approval</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
    height: 6px;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 3px;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
    background-color: #4b5563;
    border-radius: 3px;
}

.scrollbar-track-gray-100::-webkit-scrollbar-track {
    background-color: #f3f4f6;
    border-radius: 3px;
}

.scrollbar-track-gray-800::-webkit-scrollbar-track {
    background-color: #1f2937;
    border-radius: 3px;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb:hover {
    background-color: #9ca3af;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb:hover {
    background-color: #6b7280;
}

.group:hover {
    transform: translateY(-1px);
}

@keyframes progressFill {
    from {
        width: 0%;
    }
    to {
        width: var(--progress-width);
    }
}

.fi-wi-stats-overview {
    height: 100%;
}

.fi-wi-stats-overview > div {
    height: 100%;
}
</style>
@props(['teamStats', 'individualStats', 'departmentName'])

<div class="fi-wi-stats-overview h-full">
    <div class="w-full h-full bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                <x-heroicon-o-user-group class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" />
                {{ $departmentName ? $departmentName . ' Team' : 'Team' }} Overview (MTD)
            </h2>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ now()->format('M j, Y') }}
            </div>
        </div>

        <div class="w-full">
            <div class="flex overflow-x-auto gap-4 pb-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                @forelse ($teamStats as $card)
                    <div class="min-w-[200px] bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200 group">

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <x-dynamic-component
                                    :component="$card['icon']"
                                    class="w-5 h-5 {{ $card['color'] }} dark:opacity-90"
                                />
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $card['name'] }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $card['unit'] === '%' ? $card['value'] : number_format($card['value'], $card['value'] == floor($card['value']) ? 0 : 1) }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                    {{ $card['unit'] === '%' ? '%' : $card['unit'] }}
                                </span>
                            </div>

                            {{-- Show progress bar only for percentage values --}}
                            @if($card['unit'] === '%' && isset($card['progress']))
                                <div class="w-full bg-gray-200 dark:bg-gray-600 h-2 rounded-full overflow-hidden mb-2">
                                    <div class="h-full rounded-full transition-all duration-700 ease-out
                                        {{ $card['progress'] >= 95 ? 'bg-gradient-to-r from-emerald-500 to-green-500 dark:from-emerald-400 dark:to-green-400' :
                                           ($card['progress'] >= 85 ? 'bg-gradient-to-r from-green-500 to-blue-500 dark:from-green-400 dark:to-blue-400' :
                                           ($card['progress'] >= 75 ? 'bg-gradient-to-r from-blue-500 to-indigo-500 dark:from-blue-400 dark:to-indigo-400' :
                                           ($card['progress'] >= 60 ? 'bg-gradient-to-r from-amber-500 to-orange-500 dark:from-amber-400 dark:to-orange-400' : 'bg-gradient-to-r from-orange-500 to-red-500 dark:from-orange-400 dark:to-red-400'))) }}"
                                        style="width: {{ min($card['progress'], 100) }}%">
                                    </div>
                                </div>
                            @endif

                            @if(isset($card['subtitle']))
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $card['subtitle'] }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                                {{ strpos($card['status'], 'Outstanding') !== false || strpos($card['status'], 'Excellent') !== false ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' :
                                   (strpos($card['status'], 'Target Achieved') !== false || strpos($card['status'], 'Very Good') !== false ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                   (strpos($card['status'], 'Near Target') !== false || strpos($card['status'], 'Good') !== false ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                   (strpos($card['status'], 'Making Progress') !== false || strpos($card['status'], 'Fair') !== false ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' :
                                   (strpos($card['status'], 'Needs') !== false ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300')))) }}">
                                {{ $card['status'] }}
                            </div>

                            {{-- Show target only for non-percentage values --}}
                            @if($card['unit'] !== '%')
                                <div class="text-xs text-gray-600 dark:text-gray-300 font-medium">
                                    Em: {{ number_format($card['target'], $card['target'] == floor($card['target']) ? 0 : 1) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="w-full p-6 text-center bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-3" />
                        <span class="text-gray-600 dark:text-gray-300 text-sm font-medium">No team data available</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Team statistics will appear here once data is available</p>
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

.fi-wi-stats-overview {
    height: 100%;
}

.fi-wi-stats-overview > div {
    height: 100%;
}
</style>
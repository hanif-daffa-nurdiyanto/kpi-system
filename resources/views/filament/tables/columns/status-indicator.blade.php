@php
    $state = $getState();

    $percentage = 0;
    $statusKey = 'unknown';

    if (is_array($state)) {
        $percentage = $state['percentage'] ?? 0;
        $statusKey = $state['status'] ?? 'unknown';
    } elseif (is_string($state)) {
        $statusKey = $state;
        $percentage = match ($statusKey) {
            'above-target' => 100,
            'on-track' => 85,
            'needs-improvement' => 65,
            'below-target' => 30,
            'needs-attention' => 0,
            default => 0,
        };
    }

    $displayPercentage = number_format($percentage, 1);

    $data = match ($statusKey) {
        'above-target' => [
            'textClass' => 'text-green-700',
            'barClass' => 'bg-primary-500',
            'label' => 'Above Target',
            'icon' => 'heroicon-o-arrow-trending-up',
            'tooltip' => "Exceeding target ({$displayPercentage}%)"
        ],
        'on-track' => [
            'textClass' => 'text-blue-700',
            'barClass' => 'bg-primary-500',
            'label' => 'On Track',
            'icon' => 'heroicon-o-check-circle',
            'tooltip' => "On target ({$displayPercentage}%)"
        ],
        'needs-improvement' => [
            'textClass' => 'text-yellow-700',
            'barClass' => 'bg-primary-500',
            'label' => 'Needs Improvement',
            'icon' => 'heroicon-o-exclamation-triangle',
            'tooltip' => "Below target ({$displayPercentage}%)"
        ],
        'below-target' => [
            'textClass' => 'text-red-700',
            'barClass' => 'bg-primary-500',
            'label' => 'Below Target',
            'icon' => 'heroicon-o-arrow-trending-down',
            'tooltip' => "Significantly below target ({$displayPercentage}%)"
        ],
        'needs-attention' => [
            'textClass' => 'text-gray-700 dark:text-gray-300',
            'barClass' => 'bg-gray-400',
            'label' => 'Needs Attention',
            'icon' => 'heroicon-o-exclamation-circle',
            'tooltip' => 'No target or metric set'
        ],
        default => [
            'textClass' => 'text-gray-500 dark:text-gray-400',
            'barClass' => 'bg-gray-300',
            'label' => 'Unknown',
            'icon' => 'heroicon-o-question-mark-circle',
            'tooltip' => 'Status unknown'
        ],
    };

    $barWidth = $percentage > 0 ? max(5, min(100, $percentage)) : 0;
@endphp

<div x-data="{ showTooltip: false }"
     @mouseenter="showTooltip = true"
     @mouseleave="showTooltip = false"
     class="w-full relative space-y-1 px-2 py-1">

    {{-- Tooltip --}}
    <div x-show="showTooltip"
         x-transition
         class="absolute bottom-full left-0 mb-2 z-10 px-3 py-1.5 text-xs font-medium text-white bg-gray-900 rounded shadow"
         style="min-width: max-content">
        {{ $data['tooltip'] }}
        <div class="absolute w-3 h-3 bg-gray-900 transform rotate-45 left-3 top-full -mt-1.5"></div>
    </div>

    {{-- Bar --}}
    <div class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden">
        <div class="{{ $data['barClass'] }} h-full rounded-full transition-all duration-500 ease-in-out"
             style="width: {{ $barWidth }}%; min-width: 0.25rem;"></div>
    </div>

    {{-- Label --}}
    <div class="flex items-center gap-1 text-xs">
        @if (isset($data['icon']))
            @svg($data['icon'], "h-4 w-4 shrink-0 {$data['textClass']}")
        @endif
        <span class="{{ $data['textClass'] }} font-medium truncate">{{ $data['label'] }}</span>
        <span class="ml-auto text-gray-500 dark:text-gray-400">{{ $displayPercentage }}%</span>
    </div>
</div>

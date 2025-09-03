<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-semibold">Filter by Date</h2>
        <form wire:submit="save">
            {{ $this->form }}
        </form>
    </x-filament::section>
</x-filament-widgets::widget>

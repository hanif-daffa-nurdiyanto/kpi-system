<div class="flex flex-row gap-6 items-start">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium">Date Filter</h3>
    </div>

    <div class="w-full max-w-[400px]">
        <form wire:submit.prevent="updateFilter" class="space-y-3">
            {{ $form }}

            <button type="submit" class="hidden"></button>
        </form>
    </div>
</div>
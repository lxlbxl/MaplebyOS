<x-filament::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Widgets will be placed here --}}
        @livewire(\Modules\Reports\Filament\Widgets\JobsByStatusChart::class)
    </div>
</x-filament::page>
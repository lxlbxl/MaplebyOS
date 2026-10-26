<x-filament::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Widgets will be placed here --}}
        @livewire(\Modules\Reports\Filament\Widgets\RevenueTrendChart::class)
        @livewire(\Modules\Reports\Filament\Widgets\SalesByRepChart::class)
    </div>

    <div class="mt-4">
        @livewire(\Modules\Reports\Filament\Widgets\LeadConversionChart::class)
    </div>
</x-filament::page>
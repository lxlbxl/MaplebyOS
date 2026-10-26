<x-filament::widget class="fi-my-tasks-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3 mb-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Active Shipments</h2>
            <div class="ml-auto">
                <button
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-50 flex items-center gap-1">
                    <x-heroicon-m-plus class="w-3 h-3" />
                    New
                </button>
            </div>
        </div>

        <div class="space-y-5">
            @foreach($shipments as $shipment)
                <div class="flex items-start gap-4 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <!-- Icon Box -->
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center {{ $shipment['color'] }}">
                        @svg($shipment['icon'], 'w-5 h-5')
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-none truncate">
                            {{ $shipment['title'] }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-500 truncate">
                            {{ $shipment['subtitle'] }}
                        </p>
                    </div>

                    <div class="flex-shrink-0">
                        <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400" />
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::widget>
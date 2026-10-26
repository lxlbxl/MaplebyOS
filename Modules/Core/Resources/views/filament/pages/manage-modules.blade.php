<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($modules as $module)
            <div
                class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 flex justify-between items-center transition hover:shadow-lg">
                <div class="flex items-center space-x-3">
                    <div
                        class="p-2 rounded-full {{ $module['enabled'] ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-500' }}">
                        <x-heroicon-o-cube class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $module['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $module['enabled'] ? 'Active' : 'Inactive' }}</p>
                    </div>
                </div>

                <x-filament::button size="sm" color="{{ $module['enabled'] ? 'danger' : 'success' }}"
                    wire:click="toggleModule('{{ $module['name'] }}')">
                    {{ $module['enabled'] ? 'Disable' : 'Enable' }}
                </x-filament::button>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
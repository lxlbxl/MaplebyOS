<x-filament::widget class="fi-latest-activities-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3 mb-4">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                <x-heroicon-o-clock class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Live Activity</h2>
            <div class="ml-auto">
                <button
                    class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">View
                    Log</button>
            </div>
        </div>

        <div class="relative pl-4 border-l border-gray-200 dark:border-gray-700 space-y-6">
            @foreach($this->getActivities() as $activity)
                <div class="relative">
                    <!-- Dot -->
                    <div
                        class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-900 ring-1 ring-gray-300 dark:ring-gray-600">
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $activity->causer->name ?? 'System' }}
                            </span>
                            {{ $activity->description }}
                        </p>
                        <time class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $activity->created_at->diffForHumans() }}
                        </time>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::widget>
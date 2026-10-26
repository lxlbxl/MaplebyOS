@php
    use Filament\Support\Facades\FilamentView;
@endphp

<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
    <!-- Menu -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="absolute bottom-16 right-0 mb-2 w-56 rounded-xl shadow-2xl overflow-hidden ring-1 ring-black/5 flex flex-col items-stretch py-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-white/20 dark:border-gray-700/50"
        style="display: none;">

        <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Quick Actions
        </div>

        <a href="{{ route('filament.admin.resources.leads.create') }}"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100/50 dark:hover:bg-white/10 transition-colors text-sm text-gray-700 dark:text-gray-200">
            <div class="p-1.5 bg-blue-100 dark:bg-blue-900/50 rounded-lg text-blue-600 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                    <path
                        d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                </svg>
            </div>
            New Lead
        </a>

        <a href="{{ route('filament.admin.resources.quotes.create') }}"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100/50 dark:hover:bg-white/10 transition-colors text-sm text-gray-700 dark:text-gray-200">
            <div class="p-1.5 bg-purple-100 dark:bg-purple-900/50 rounded-lg text-purple-600 dark:text-purple-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                    <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                    <path
                        d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                </svg>
            </div>
            New Quote
        </a>

        {{-- <a href="{{ route('filament.admin.resources.jobs.create') }}"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100/50 dark:hover:bg-white/10 transition-colors text-sm text-gray-700 dark:text-gray-200">
            <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                    <path fill-rule="evenodd"
                        d="M6 3.75A2.75 2.75 0 018.75 1h2.5A2.75 2.75 0 0114 3.75v.443c.572.055 1.14.122 1.706.2C17.053 4.582 18 5.75 18 7.07V16a2 2 0 01-2 2H4a2 2 0 01-2-2V7.07c0-1.32.947-2.488 2.294-2.676A41.047 41.047 0 016 4.193V3.75zm1.5 0V4.3a50.33 50.33 0 015 0V3.75A1.25 1.25 0 0011.25 2.5h-2.5A1.25 1.25 0 007.5 3.75z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            New Job
        </a> --}}

        <a href="{{ route('filament.admin.resources.vehicles.create') }}"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100/50 dark:hover:bg-white/10 transition-colors text-sm text-gray-700 dark:text-gray-200">
            <div class="p-1.5 bg-orange-100 dark:bg-orange-900/50 rounded-lg text-orange-600 dark:text-orange-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                    <path
                        d="M6.5 3c-1.051 0-2.093.04-3.125.117A1.49 1.49 0 002 4.607V10.5h9V4.606c0-.771-.59-1.43-1.375-1.489A41.568 41.568 0 006.5 3zM11 5v5.5h2V4.606c0-.771-.59-1.43-1.375-1.489A41.568 41.568 0 0011 3v2zm-5 7a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z" />
                </svg>
            </div>
            New Vehicle
        </a>
    </div>

    <!-- Main Button -->
    <button @click="open = !open"
        class="group w-14 h-14 flex items-center justify-center rounded-full shadow-xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white hover:scale-110 active:scale-95 transition-all duration-300 relative overflow-hidden"
        :class="{ 'rotate-45': open }">
        <div
            class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
            class="w-8 h-8 relative z-10">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
    </button>
</div>
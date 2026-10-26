<x-filament::widget class="fi-team-collaboration-widget">
    <x-filament::section class="!p-0 overflow-hidden">
        <div class="px-6 py-5 flex items-center justify-between border-b border-gray-50">
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Team Collaboration</h2>
            <button
                class="flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-full hover:bg-gray-50 transition-colors shadow-sm">
                <x-heroicon-m-plus class="w-3.5 h-3.5" />
                Add Member
            </button>
        </div>

        <div class="p-6 space-y-7">
            @foreach($items as $item)
                <div class="flex items-center gap-4 group">
                    <div class="relative">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                            class="w-11 h-11 rounded-full bg-slate-100 object-cover border-2 border-white shadow-sm" />
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full">
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[15px] font-bold text-gray-900 truncate tracking-tight">
                                {{ $item['name'] }}
                            </h3>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase {{ $item['status_color'] }}">
                                {{ $item['status'] }}
                            </span>
                        </div>
                        <p class="text-[13px] text-gray-500 truncate mt-0.5">
                            {{ $item['detail'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::widget>
<x-filament-panels::page>
    <div class="mb-10">
        <h1 class="dashboard-header-title text-4xl font-extrabold tracking-tight text-slate-900">Dashboard</h1>
        <p class="dashboard-header-subtitle mt-2 text-lg text-slate-500">Plan, prioritize, and accomplish your tasks
            with ease.</p>
    </div>

    {{-- Stats Overview --}}
    <div class="fi-wi-stats-overview mb-10">
        @livewire(\App\Filament\Widgets\StatsOverview::class)
    </div>

    {{-- Widgets Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-8">
            @livewire(\Modules\Tasks\Filament\Widgets\MyTasksWidget::class)
            @livewire(\App\Filament\Widgets\TeamCollaborationWidget::class)
        </div>
        <div class="space-y-8">
            @livewire(\App\Filament\Widgets\ProjectProgressChart::class)
            {{-- Placeholder for future widgets like Reminders or Time Tracker --}}
        </div>
    </div>
</x-filament-panels::page>
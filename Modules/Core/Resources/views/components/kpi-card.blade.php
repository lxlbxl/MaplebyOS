@props(['title', 'value', 'trend' => null, 'trendColor' => 'success'])

<div {{ $attributes->merge(['class' => 'fi-card p-6 rounded-xl shadow-sm bg-white/50 dark:bg-gray-900/50 backdrop-blur-md border border-gray-200 dark:border-white/5']) }}>
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</span>
        @if($attributes->has('icon'))
            <x-icon :name="$icon" class="w-5 h-5 text-gray-400" />
        @endif
    </div>

    <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $value }}</span>
        @if($trend)
            <span @class([
                'text-sm font-medium',
                'text-green-600 dark:text-green-400' => $trendColor === 'success',
                'text-red-600 dark:text-red-400' => $trendColor === 'danger',
                'text-gray-500' => $trendColor === 'neutral',
            ])>
                {{ $trend }}
            </span>
        @endif
    </div>
</div>
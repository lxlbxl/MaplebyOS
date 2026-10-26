<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="text-sm font-medium text-gray-500">User</span>
            <p class="mt-1 text-sm text-gray-900">{{ $record->causer?->name ?? 'System' }}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-500">Date</span>
            <p class="mt-1 text-sm text-gray-900">{{ $record->created_at->format('M d, Y H:i:s') }}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-500">Action</span>
            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($record->description) }}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-500">Entity</span>
            <p class="mt-1 text-sm text-gray-900">{{ class_basename($record->subject_type) }} #{{ $record->subject_id }}
            </p>
        </div>
    </div>

    @if($record->properties->has('old'))
        <div>
            <span class="text-sm font-medium text-gray-500">Old Values</span>
            <pre
                class="mt-1 p-3 bg-red-50 rounded-lg text-xs overflow-x-auto">{{ json_encode($record->properties['old'], JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    @if($record->properties->has('attributes'))
        <div>
            <span class="text-sm font-medium text-gray-500">New Values</span>
            <pre
                class="mt-1 p-3 bg-green-50 rounded-lg text-xs overflow-x-auto">{{ json_encode($record->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
</div>
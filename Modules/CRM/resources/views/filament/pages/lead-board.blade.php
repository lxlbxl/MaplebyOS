<x-filament-panels::page>
    <style>
        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            min-height: 70vh;
        }

        .kanban-column {
            min-width: 280px;
            max-width: 320px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .kanban-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            background: inherit;
            border-radius: 1rem 1rem 0 0;
        }

        .kanban-cards {
            padding: 0.75rem;
            min-height: 400px;
        }

        .kanban-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            cursor: grab;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .kanban-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .kanban-card.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }

        .kanban-column.drag-over {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .status-badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-gray {
            background: #f3f4f6;
            color: #374151;
        }

        .status-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .status-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-green {
            background: #d1fae5;
            color: #065f46;
        }

        .status-red {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>

    <div class="kanban-board" x-data="{
        draggedId: null,
        handleDragStart(e, id) {
            this.draggedId = id;
            e.target.classList.add('dragging');
        },
        handleDragEnd(e) {
            e.target.classList.remove('dragging');
            this.draggedId = null;
        },
        handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('drag-over');
        },
        handleDragLeave(e) {
            e.currentTarget.classList.remove('drag-over');
        },
        handleDrop(e, status) {
            e.currentTarget.classList.remove('drag-over');
            if (this.draggedId) {
                $wire.dispatch('updateLeadStatus', { leadId: this.draggedId, newStatus: status });
            }
        }
    }">
        @foreach($this->statuses as $status => $config)
            <div class="kanban-column" @dragover="handleDragOver" @dragleave="handleDragLeave"
                @drop="handleDrop($event, '{{ $status }}')">
                <div class="kanban-header">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="status-badge status-{{ $config['color'] }}">{{ $config['label'] }}</span>
                            <span class="ml-2 text-sm text-gray-500">({{ $this->getStatusCount($status) }})</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        ${{ number_format($this->getStatusValue($status), 0) }} total
                    </p>
                </div>
                <div class="kanban-cards">
                    @foreach($this->getLeadsByStatus($status) as $lead)
                        <div class="kanban-card" draggable="true" @dragstart="handleDragStart($event, {{ $lead->id }})"
                            @dragend="handleDragEnd">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-sm text-gray-900 truncate" title="{{ $lead->title }}">
                                    {{ Str::limit($lead->title, 25) }}
                                </h4>
                                <a href="{{ \Modules\CRM\Filament\Resources\LeadResource::getUrl('edit', ['record' => $lead]) }}"
                                    class="text-gray-400 hover:text-gray-600">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </a>
                            </div>
                            @if($lead->client)
                                <p class="text-xs text-gray-500 mb-2">
                                    <x-heroicon-s-building-office class="w-3 h-3 inline" />
                                    {{ $lead->client->company_name }}
                                </p>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-emerald-600">
                                    ${{ number_format($lead->estimated_value ?? 0, 0) }}
                                </span>
                                @if($lead->assignee)
                                    <span class="text-xs text-gray-500">
                                        {{ $lead->assignee->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
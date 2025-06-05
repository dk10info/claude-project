<x-filament-panels::page>
    <div class="jira-board">
        <!-- Board Header -->
        <div class="board-header">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <button class="board-view-btn active">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                            </svg>
                            Board
                        </button>
                    </div>
                </div>

                <!-- Date Filter -->
                <div class="bg-gray-50 rounded-md px-3 py-2">
                    <form wire:submit.prevent="submit" class="flex items-center space-x-3">
                        {{ $this->form }}
                    </form>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-container" wire:poll.10s>
            <div class="kanban-scroll">
                <div class="kanban-columns">
                    @php
                    $tasksByStatus = $this->getTasksByStatus();
                    $statuses = [
                    'pending' => ['label' => 'TO DO', 'color' => '#42526E'],
                    'in_progress' => ['label' => 'IN PROGRESS', 'color' => '#0052CC'],
                    'in_review' => ['label' => 'In REVIEW', 'color' => '#FF991F'],
                    'completed' => ['label' => 'DONE', 'color' => '#00875A'],
                    'cancelled' => ['label' => 'CANCELLED', 'color' => '#DE350B']
                    ];
                    @endphp

                    @foreach($statuses as $statusKey => $statusData)
                    <div class="kanban-column">
                        <div class="column-header">
                            <h3 class="column-title" style="color: {{ $statusData['color'] }}">
                                {{ $statusData['label'] }}
                                <span class="task-count">{{ $tasksByStatus[$statusKey]->count() }}</span>
                            </h3>
                        </div>

                        <div class="column-content"
                            data-status="{{ $statusKey }}"
                            @drop="$wire.updateTaskStatus($event.dataTransfer.getData('taskId'), '{{ $statusKey }}')"
                            @drop.prevent="handleDrop"
                            @dragover.prevent
                            @dragenter.prevent="handleDragEnter"
                            @dragleave.prevent="handleDragLeave">

                            @forelse($tasksByStatus[$statusKey] as $task)
                            <div class="task-card"
                                draggable="true"
                                data-task-id="{{ $task->id }}"
                                @dragstart="handleDragStart"
                                @dragend="handleDragEnd"
                                wire:key="task-{{ $task->id }}">

                                <!-- Task Content -->
                                <div class="task-content">
                                    <div class="task-header">
                                        <span class="task-type-icon">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-1a1 1 0 100-2h1a4 4 0 014 4v6a4 4 0 01-4 4H6a4 4 0 01-4-4V7a4 4 0 014-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                        <span class="task-key">TASK-{{ $task->id }}</span>
                                    </div>

                                    <h4 class="task-title">{{ $task->title }}</h4>

                                    @if($task->description)
                                    <p class="task-description">{{ Str::limit($task->description, 100) }}</p>
                                    @endif
                                </div>

                                <!-- Task Footer -->
                                <div class="task-footer">
                                    <div class="task-meta">
                                        <!-- Priority -->
                                        @php
                                        $priorityIcons = [
                                        'urgent' => ['icon' => '↑', 'color' => '#FF5630'],
                                        'high' => ['icon' => '↑', 'color' => '#FF7452'],
                                        'medium' => ['icon' => '=', 'color' => '#FFAB00'],
                                        'low' => ['icon' => '↓', 'color' => '#36B37E']
                                        ];
                                        $priority = $priorityIcons[$task->priority] ?? $priorityIcons['medium'];
                                        @endphp
                                        <span class="priority-icon" style="color: {{ $priority['color'] }}" title="{{ ucfirst($task->priority) }} priority">
                                            {{ $priority['icon'] }}
                                        </span>

                                        <!-- Due Date -->
                                        @if($task->due_date)
                                        <span class="due-date {{ $task->due_date->isPast() ? 'overdue' : '' }}">
                                            {{ $task->due_date->format('M d') }}
                                        </span>
                                        @endif
                                    </div>

                                    <!-- Assignee Avatars -->
                                    <div class="assignee-avatars">
                                        @foreach($task->assignedUsers->take(3) as $user)
                                            <div class="assignee-avatar" title="{{ $user->name }}" style="margin-left: {{ $loop->index > 0 ? '-8px' : '0' }};">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                        @if($task->assignedUsers->count() > 3)
                                            <div class="assignee-avatar more" title="{{ $task->assignedUsers->count() - 3 }} more assignees" style="margin-left: -8px;">
                                                +{{ $task->assignedUsers->count() - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="empty-column">
                                <p>No issues</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        .jira-board {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
        }

        .board-header {
            padding: 0 24px;
        }

        .board-view-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border: none;
            background: transparent;
            color: #42526E;
            font-size: 14px;
            font-weight: 500;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .board-view-btn:hover {
            background: #EBECF0;
        }

        .board-view-btn.active {
            background: #E4E6EA;
            color: #0052CC;
        }

        .kanban-container {
            padding: 0 12px;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .kanban-scroll {
            padding-bottom: 8px;
        }

        .kanban-columns {
            display: flex;
            gap: 12px;
            min-width: fit-content;
            padding: 0 12px;
        }

        .kanban-column {
            flex: 0 0 280px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 250px);
        }

        .column-header {
            padding: 12px 8px 8px;
        }

        .column-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .task-count {
            font-size: 12px;
            font-weight: 400;
            color: #5E6C84;
        }

        .column-content {
            flex: 1;
            overflow-y: auto;
            padding: 0 4px 8px;
            border-radius: 3px;
            min-height: 100px;
            transition: background-color 0.2s;
        }

        .column-content.drag-over {
            background-color: #EBECF0;
        }

        .task-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 2px rgba(9, 30, 66, 0.25);
            padding: 12px;
            margin-bottom: 8px;
            cursor: grab;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .task-card:hover {
            box-shadow: 0 3px 5px rgba(9, 30, 66, 0.2);
        }

        .task-card.dragging {
            opacity: 0.5;
            cursor: grabbing;
            transform: rotate(3deg);
        }

        .task-content {
            margin-bottom: 8px;
        }

        .task-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .task-type-icon {
            color: #5E6C84;
            display: flex;
            align-items: center;
        }

        .task-key {
            font-size: 11px;
            color: #5E6C84;
            font-weight: 500;
        }

        .task-title {
            font-size: 14px;
            font-weight: 400;
            color: #172B4D;
            line-height: 1.4;
            margin: 0 0 4px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .task-description {
            font-size: 13px;
            color: #5E6C84;
            margin: 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .task-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
        }

        .task-meta {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .priority-icon {
            font-size: 16px;
            font-weight: bold;
            line-height: 1;
        }

        .due-date {
            font-size: 11px;
            color: #5E6C84;
            background: #F4F5F7;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .due-date.overdue {
            background: #FFEBE6;
            color: #DE350B;
        }

        .assignee-avatars {
            display: flex;
            align-items: center;
        }

        .assignee-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #0052CC;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 500;
            border: 2px solid white;
            position: relative;
            z-index: 1;
        }

        .assignee-avatar.more {
            background: #5E6C84;
            font-size: 10px;
        }

        .empty-column {
            padding: 24px;
            text-align: center;
            color: #5E6C84;
            font-size: 14px;
        }

        /* Scrollbar styling */
        .column-content::-webkit-scrollbar,
        .kanban-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .column-content::-webkit-scrollbar-track,
        .kanban-container::-webkit-scrollbar-track {
            background: #F4F5F7;
            border-radius: 4px;
        }

        .column-content::-webkit-scrollbar-thumb,
        .kanban-container::-webkit-scrollbar-thumb {
            background: #C1C7D0;
            border-radius: 4px;
        }

        .column-content::-webkit-scrollbar-thumb:hover,
        .kanban-container::-webkit-scrollbar-thumb:hover {
            background: #A5ADBA;
        }

        /* Form styling adjustments */
        .fi-fo-field-wrp {
            margin-bottom: 0 !important;
        }

        .fi-fo-field-wrp label {
            display: none !important;
        }

        .fi-input {
            min-height: 32px !important;
            font-size: 14px !important;
        }
    </style>

    @push('scripts')
    <script>
        let draggedElement = null;

        function handleDragStart(e) {
            draggedElement = e.target;
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('taskId', e.target.dataset.taskId);
        }

        function handleDragEnd(e) {
            e.target.classList.remove('dragging');
        }

        function handleDragEnter(e) {
            const column = e.currentTarget;
            if (column.classList.contains('column-content')) {
                column.classList.add('drag-over');
            }
        }

        function handleDragLeave(e) {
            const column = e.currentTarget;
            if (column.classList.contains('column-content')) {
                column.classList.remove('drag-over');
            }
        }

        function handleDrop(e) {
            const column = e.currentTarget;
            column.classList.remove('drag-over');
        }

        // Initialize drag and drop
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to all task cards
            document.querySelectorAll('.task-card').forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            // Add event listeners to all columns
            document.querySelectorAll('.column-content').forEach(column => {
                column.addEventListener('dragenter', handleDragEnter);
                column.addEventListener('dragleave', handleDragLeave);
                column.addEventListener('drop', handleDrop);
            });
        });

        // Re-initialize after Livewire updates
        document.addEventListener('livewire:update', function() {
            document.querySelectorAll('.task-card').forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
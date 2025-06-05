<x-filament-widgets::widget>
    <style>
        /* Timer animations - placed here for immediate loading */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
        
        @keyframes glow {
            0% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 0 30px rgba(255, 255, 255, 0.4); }
            100% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
        }
        
        .timer-colon {
            animation: blink 1s ease-in-out infinite;
        }
               
        .seconds-glow {
            animation: glow 1s ease-in-out;
        }
    </style>

    @if($activeTimer)
        <!-- Active Timer Card -->
        <div style="background: linear-gradient(135deg, #059669 0%, #10b981 50%, #14b8a6 100%); border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid rgba(5, 150, 105, 0.3);" 
             wire:poll.1000ms="loadActiveTimer">
            <div style="padding: 32px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <!-- Header -->
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                            <div style="padding: 8px; background: rgba(255, 255, 255, 0.2); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: 700; color: white; margin: 0;">Timer Running</h3>
                                <p style="font-size: 12px; color: rgba(255, 255, 255, 0.9); margin: 0; display: flex; align-items: center; gap: 4px;">
                                    <span style="display: inline-block; width: 8px; height: 8px; background: white; border-radius: 50%; animation: pulse 2s infinite;"></span>
                                    Recording time
                                </p>
                            </div>
                        </div>
                        
                        <!-- Timer Display -->
                        <div style="margin-bottom: 24px;" 
                             x-data="timerWidget{{ $activeTimer->id }}"
                             x-init="init()">
                            
                            <!-- Main Timer Display -->
                            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 8px; margin-bottom: 32px;">
                                <!-- Hours -->
                                <div style="position: relative;">
                                    <div style="background: rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 8px 12px; border: 2px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                        <span x-text="hours" style="font-size: 48px; font-weight: 900; color: white; font-family: monospace; display: block; line-height: 1; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">00</span>
                                    </div>
                                    <span style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 11px; color: rgba(255, 255, 255, 0.9); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap;">Hours</span>
                                </div>
                                
                                <!-- Separator -->
                                <div class="timer-colon" style="font-size: 36px; font-weight: 700; color: white; line-height: 1; padding: 0 4px;">:</div>
                                
                                <!-- Minutes -->
                                <div style="position: relative;">
                                    <div style="background: rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 8px 12px; border: 2px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                        <span x-text="minutes" style="font-size: 48px; font-weight: 900; color: white; font-family: monospace; display: block; line-height: 1; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">00</span>
                                    </div>
                                    <span style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 11px; color: rgba(255, 255, 255, 0.9); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap;">Minutes</span>
                                </div>
                                
                                <!-- Separator -->
                                <div class="timer-colon" style="font-size: 36px; font-weight: 700; color: white; line-height: 1; padding: 0 4px;">:</div>
                                
                                <!-- Seconds -->
                                <div style="position: relative;">
                                    <div x-ref="secondsBox" style="background: rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 8px 12px; border: 2px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                        <span x-text="seconds" style="font-size: 48px; font-weight: 900; color: white; font-family: monospace; display: block; line-height: 1; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">00</span>
                                    </div>
                                    <span style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 11px; color: rgba(255, 255, 255, 0.9); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap;">Seconds</span>
                                </div>
                               
                            </div>
                        </div>
                        
                        <script>
                            window.timerWidget{{ $activeTimer->id }} = function() {
                                return {
                                    hours: '00',
                                    minutes: '00',
                                    seconds: '00',
                                    startTime: new Date({{ $activeTimer->started_at->timestamp * 1000 }}),
                                    interval: null,
                                    
                                    init() {
                                        this.updateTimer();
                                        this.interval = setInterval(() => this.updateTimer(), 1000);
                                    },
                                    
                                    updateTimer() {
                                        const now = new Date();
                                        const elapsed = Math.floor((now - this.startTime) / 1000);
                                        
                                        const newHours = String(Math.floor(elapsed / 3600)).padStart(2, '0');
                                        const newMinutes = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
                                        const newSeconds = String(elapsed % 60).padStart(2, '0');
                                        
                                        this.hours = newHours;
                                        this.minutes = newMinutes;
                                        
                                        // Animate seconds change
                                        if (this.seconds !== newSeconds && this.$refs.secondsBox) {
                                            this.$refs.secondsBox.classList.add('seconds-glow');
                                            setTimeout(() => {
                                                this.$refs.secondsBox.classList.remove('seconds-glow');
                                            }, 500);
                                        }
                                        
                                        this.seconds = newSeconds;
                                    }
                                }
                            };
                        </script>
                        
                        <!-- Task Info -->
                        <div style="background: rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <p style="font-size: 12px; color: rgba(255, 255, 255, 0.9); margin: 0 0 4px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Working on</p>
                            <a href="{{ route('filament.employee.resources.tasks.view', $activeTimer->task_id) }}" 
                               style="color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <span style="overflow: hidden; text-overflow: ellipsis;">{{ Str::limit($activeTimer->task->title, 50) }}</span>
                                <svg style="width: 16px; height: 16px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                            @if($activeTimer->task->priority)
                                <div style="margin-top: 8px;">
                                    <span style="font-size: 12px; color: rgba(255, 255, 255, 0.9); font-weight: 500;">Priority: </span>
                                    <span style="display: inline-block; padding: 2px 8px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; color: white; font-weight: 700; font-size: 12px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        {{ ucfirst($activeTimer->task->priority) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Stop Button -->
                    <div style="display: flex; flex-direction: column; gap: 8px; align-items: center;">
                        <x-filament::button
                            wire:click="stopTimer"
                            color="danger"
                            size="lg"
                            icon="heroicon-o-stop"
                            style="box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);"
                        >
                            Stop Timer
                        </x-filament::button>
                        <a href="{{ route('filament.employee.resources.tasks.view', $activeTimer->task_id) }}" 
                           style="color: white; font-size: 14px; font-weight: 500; text-decoration: none;">
                            View Task →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Active Timer Card -->
        <div style="background: white; border-radius: 16px; border: 2px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" class="dark:bg-gray-800 dark:border-gray-600">
            <div style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                    <div style="padding: 16px; background: #f3f4f6; border-radius: 50%; border: 2px solid #e5e7eb;" class="dark:bg-gray-700 dark:border-gray-600">
                        <svg style="width: 32px; height: 32px; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0;" class="dark:text-gray-100">No Active Timer</h3>
                        <p style="color: #6b7280; font-weight: 500; margin: 0;" class="dark:text-gray-300">Start tracking time from any of your assigned tasks</p>
                        <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px; font-size: 14px; color: #374151;" class="dark:text-gray-300">
                            <svg style="width: 16px; height: 16px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <span style="font-weight: 500;">Click the "Start Timer" button on any task to begin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <style>
        /* Blinking colon animation */
        @keyframes colonBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.2; }
        }
        
        .timer-colon {
            animation: colonBlink 1s ease-in-out infinite;
        }
        
        /* Pulsing dots animation */
        @keyframes pulseDot {
            0%, 100% { 
                opacity: 0.2;
                transform: scale(0.8);
            }
            50% { 
                opacity: 1;
                transform: scale(1.2);
            }
        }
        
        
        /* Seconds box glow effect */
        #seconds-box-{{ $activeTimer->id ?? '0' }} {
            transition: all 0.3s ease;
        }
    </style>

    @push('scripts')
    <script>
        // Request notification permission for timer milestones
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    </script>
    @endpush
</x-filament-widgets::widget>
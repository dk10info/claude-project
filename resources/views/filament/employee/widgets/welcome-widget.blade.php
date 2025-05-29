<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Welcome, {{ auth()->user()->name }}!
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                You are logged in as an Employee. Use the navigation menu to access your resources.
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
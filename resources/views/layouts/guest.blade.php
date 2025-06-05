<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
        darkMode: {{ 
            request()->cookie('filament_theme') === 'dark' || 
            (request()->cookie('filament_theme') !== 'light' && request()->cookie('darkMode', 'false') === 'true') 
            ? 'true' : 'false' 
        }} 
      }"
      x-init="
        $watch('darkMode', value => {
            if (value) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
        if (darkMode) {
            document.documentElement.classList.add('dark');
        }
      "
      @dark-mode-toggled.window="darkMode = $event.detail.isDark"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased">
    {{ $slot }}
    
    @livewireScripts
    <script>
        // Initialize dark mode from cookies and localStorage on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check Filament theme cookie first, then our custom cookie
            const filamentTheme = document.cookie.match(/filament_theme=([^;]+)/)?.[1];
            const darkModeCookie = document.cookie.includes('darkMode=true');
            const localStorageTheme = localStorage.getItem('theme');
            
            // Determine if dark mode should be enabled
            let isDark = false;
            if (filamentTheme === 'dark') {
                isDark = true;
            } else if (filamentTheme === 'light') {
                isDark = false;
            } else if (localStorageTheme === 'dark') {
                isDark = true;
            } else if (localStorageTheme === 'light') {
                isDark = false;
            } else {
                isDark = darkModeCookie;
            }
            
            // Apply theme
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            // Sync with Alpine.js data
            if (window.Alpine && Alpine.store) {
                Alpine.store('darkMode', isDark);
            }
        });
        
        // Listen for theme changes from Filament panels
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme') {
                const isDark = e.newValue === 'dark';
                document.documentElement.classList.toggle('dark', isDark);
                // Update our custom cookie
                document.cookie = 'darkMode=' + (isDark ? 'true' : 'false') + '; path=/; max-age=' + (365 * 24 * 60 * 60);
                // Trigger Livewire update
                if (window.Livewire) {
                    Livewire.dispatch('dark-mode-toggled', { isDark: isDark });
                }
            }
        });
    </script>
</body>
</html>
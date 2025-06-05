<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Http\Request;

class ApplyFilamentDarkMode
{
    public function handle(Request $request, Closure $next)
    {
        // Get dark mode preference from our custom cookie
        $isDarkMode = $request->cookie('darkMode', 'false') === 'true';

        // Get Filament's theme preference
        $filamentTheme = $request->cookie('filament_theme', 'system');

        // If our darkMode cookie is set and Filament theme is not explicitly set to opposite,
        // sync Filament's theme with our preference
        if ($filamentTheme === 'system' ||
            ($isDarkMode && $filamentTheme !== 'light') ||
            (! $isDarkMode && $filamentTheme !== 'dark')) {
            cookie()->queue('filament_theme', $isDarkMode ? 'dark' : 'light', 60 * 24 * 365);
        }

        // Register a script to sync themes
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => '
                <script>
                    // Sync dark mode between landing page and Filament
                    (function() {
                        // Function to sync theme from our cookie to Filament
                        function syncThemeFromCookie() {
                            const darkModeCookie = document.cookie
                                .split("; ")
                                .find(row => row.startsWith("darkMode="));
                            
                            const isDark = darkModeCookie && darkModeCookie.split("=")[1] === "true";
                            
                            // Update Filament theme
                            if (window.filament) {
                                const currentTheme = localStorage.getItem("theme");
                                const newTheme = isDark ? "dark" : "light";
                                
                                if (currentTheme !== newTheme) {
                                    localStorage.setItem("theme", newTheme);
                                    document.documentElement.classList.toggle("dark", isDark);
                                    
                                    // Update Filament\'s Alpine store if available
                                    if (window.Alpine && Alpine.store("theme")) {
                                        Alpine.store("theme", newTheme);
                                    }
                                }
                            }
                        }
                        
                        // Initial sync on page load
                        document.addEventListener("DOMContentLoaded", function() {
                            syncThemeFromCookie();
                            
                            // Watch for Filament\'s theme toggle button
                            const observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.type === "attributes" && 
                                        mutation.attributeName === "class" && 
                                        mutation.target === document.documentElement) {
                                        // Filament theme was changed
                                        const isDark = document.documentElement.classList.contains("dark");
                                        document.cookie = "darkMode=" + (isDark ? "true" : "false") + "; path=/; max-age=" + (365 * 24 * 60 * 60);
                                        document.cookie = "filament_theme=" + (isDark ? "dark" : "light") + "; path=/; max-age=" + (365 * 24 * 60 * 60);
                                    }
                                });
                            });
                            
                            observer.observe(document.documentElement, {
                                attributes: true,
                                attributeFilter: ["class"]
                            });
                        });
                        
                        // Listen for storage events from other tabs/windows
                        window.addEventListener("storage", function(e) {
                            if (e.key === "theme") {
                                const isDark = e.newValue === "dark";
                                document.cookie = "darkMode=" + (isDark ? "true" : "false") + "; path=/; max-age=" + (365 * 24 * 60 * 60);
                                syncThemeFromCookie();
                            }
                        });
                        
                        // Listen for broadcast channel messages from landing page
                        const bc = new BroadcastChannel("theme_sync");
                        bc.onmessage = function(event) {
                            if (event.data.action === "refresh_if_filament") {
                                // Give time for cookies to be set
                                setTimeout(() => {
                                    window.location.reload();
                                }, 200);
                            }
                        };
                        
                        // Check for theme changes periodically (as backup)
                        setInterval(syncThemeFromCookie, 1000);
                    })();
                </script>
            ',
        );

        return $next($request);
    }
}

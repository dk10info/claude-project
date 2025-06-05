<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ config('app.name', 'Task Manager') }}</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button 
                        wire:click="toggleTheme" 
                        x-on:click="
                            // Send message to Filament panels to refresh
                            setTimeout(() => {
                                // Broadcast to all open windows/tabs
                                const bc = new BroadcastChannel('theme_sync');
                                bc.postMessage({ action: 'refresh_if_filament' });
                                bc.close();
                            }, 100);
                        "
                        class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @if($isDarkMode)
                            <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                        @endif
                    </button>
                    <!-- Login Button -->
                    <button wire:click="openLoginModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900 overflow-hidden transition-colors duration-200">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-600 dark:from-blue-600 dark:to-indigo-800 opacity-10"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
                <div class="mb-12 lg:mb-0">
                    <div class="relative">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl lg:text-6xl">
                            <span class="block">Manage Tasks</span>
                            <span class="block mt-2">
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">Effortlessly</span>
                            </span>
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 dark:text-gray-300 sm:text-xl max-w-3xl">
                            Streamline your workflow with our intuitive task management system. Collaborate with your team, track progress in real-time, and achieve your goals faster.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4">
                            <button wire:click="openLoginModal" class="inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Get Started
                            </button>
                            <button class="inline-flex items-center justify-center px-6 py-3 text-base font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                Learn More
                            </button>
                        </div>
                    </div>
                </div>
                <div class="relative lg:ml-10">
                    <div class="relative mx-auto w-full rounded-lg shadow-2xl lg:max-w-md">
                        <div class="relative block w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                                <h3 class="text-white font-semibold text-lg">Task Overview</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg transform transition-all duration-200 hover:scale-105">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">Design System Update</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Due in 2 days</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 dark:text-yellow-200 dark:bg-yellow-800 rounded-full">In Progress</span>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg transform transition-all duration-200 hover:scale-105">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">API Integration</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Completed today</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 dark:text-green-200 dark:bg-green-800 rounded-full">Completed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-16 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-blue-600 dark:text-blue-400 font-semibold tracking-wide uppercase">Features</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Everything you need to manage tasks
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-400 lg:mx-auto">
                    Our comprehensive suite of features helps teams collaborate effectively and deliver projects on time
                </p>
            </div>

            <div class="mt-16">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-2">
                    <!-- Role-Based Access Feature -->
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Role-Based Access</h3>
                            </div>
                            <p class="text-base text-gray-600 dark:text-gray-300">
                                Multiple roles including Admin and Employee with specific permissions for each role.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    Admin
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                    Employee
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Kanban Board Feature -->
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Kanban Board</h3>
                            </div>
                            <p class="text-base text-gray-600 dark:text-gray-300">
                                Jira-style Kanban board with drag-and-drop functionality to manage task workflow efficiently.
                            </p>
                            <div class="mt-4">
                                <div class="flex space-x-2">
                                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                                        <div class="h-2 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Drag & Drop Enabled</p>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Updates Feature -->
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-br from-green-500 to-teal-600 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Real-time Updates</h3>
                            </div>
                            <p class="text-base text-gray-600 dark:text-gray-300">
                                Live dashboard statistics and auto-refreshing task boards to keep everyone in sync.
                            </p>
                            <div class="mt-4 flex items-center">
                                <div class="flex -space-x-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-teal-500 rounded-full ring-2 ring-white dark:ring-gray-800"></div>
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full ring-2 ring-white dark:ring-gray-800"></div>
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full ring-2 ring-white dark:ring-gray-800"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Live collaboration</span>
                            </div>
                        </div>
                    </div>

                    <!-- Task Assignment Feature -->
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Task Assignment</h3>
                            </div>
                            <p class="text-base text-gray-600 dark:text-gray-300">
                                Admin can assign tasks to employees with priority levels and due dates.
                            </p>
                            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">High</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Priority</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">Med</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Priority</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">Low</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Priority</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    @if($showLoginModal)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white" id="modal-title">
                                Welcome Back
                            </h3>
                        </div>
                        <button wire:click="closeLoginModal" type="button" class="text-white hover:text-gray-200 focus:outline-none transition-colors duration-200">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-blue-100">Sign in to access your dashboard</p>
                </div>

                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <form wire:submit.prevent="login">
                        @if($loginError)
                        <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm flex items-start">
                            <svg class="flex-shrink-0 h-4 w-4 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ $loginError }}
                        </div>
                        @endif

                        <div class="mb-5">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input wire:model.defer="email"
                                    type="email"
                                    id="email"
                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('email') border-red-500 dark:border-red-500 @enderror"
                                    placeholder="Enter your email">
                            </div>
                            @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input wire:model.defer="password"
                                    type="password"
                                    id="password"
                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('password') border-red-500 dark:border-red-500 @enderror"
                                    placeholder="Enter your password">
                            </div>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <input wire:model.defer="remember"
                                    type="checkbox"
                                    id="remember"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700">
                                <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Remember me
                                </label>
                            </div>
                            <!-- <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                                Forgot password?
                            </a> -->
                        </div>

                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span wire:loading.remove wire:target="login" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Sign In
                            </span>
                            <span wire:loading wire:target="login" class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing in...
                            </span>
                        </button>

                        <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Demo Credentials</p>
                            </div>
                            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <p class="flex items-center">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 w-20">Admin:</span>
                                    <span class="ml-2">admin@example.com / password</span>
                                </p>
                                <p class="flex items-center">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 w-20">Employee:</span>
                                    <span class="ml-2">employee@example.com / password</span>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    /* Additional modal styles to ensure proper display */
    .fixed {
        position: fixed;
    }

    .inset-0 {
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    .z-50 {
        z-index: 50;
    }
</style>
@endpush
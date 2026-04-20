<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        @stack('head')
    </head>
    <body
        x-data="{
            mobileSidebarOpen: false,
            desktopSidebarCollapsed: localStorage.getItem('desktopSidebarCollapsed') === '1'
        }"
        x-init="$watch('desktopSidebarCollapsed', value => localStorage.setItem('desktopSidebarCollapsed', value ? '1' : '0'))"
        class="font-sans antialiased sidebar-fixed {{ (auth()->user()?->theme_mode ?? 'light') === 'dark' ? 'theme-dark' : 'theme-light' }}"
    >
        <div class="min-h-screen bg-gray-100">
            <button
                type="button"
                class="app-mobile-menu-button xl:hidden"
                @click="mobileSidebarOpen = true"
                aria-label="Otwórz menu"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            @include('layouts.navigation')

            <div class="app-shell" :class="{ 'is-sidebar-collapsed': desktopSidebarCollapsed }">
                <main class="min-w-0 overflow-x-hidden px-4 py-20 md:px-6 md:py-24 xl:px-6 xl:py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>

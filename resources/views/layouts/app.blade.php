<!DOCTYPE html>
<html lang="es" x-data="{
    theme: localStorage.getItem('theme') || 'corporate',
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
}" x-init="$watch('theme', t => localStorage.setItem('theme', t));
$watch('sidebarCollapsed', s => localStorage.setItem('sidebarCollapsed', s));" :data-theme="theme">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema de Asistencia') }} - @yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Sistema de temas corporativos */
        [data-theme="corporate"] {
            --primary: 59 130 246;
            --primary-dark: 37 99 235;
            --accent: 99 102 241;
            --sidebar-from: 37 99 235;
            --sidebar-to: 59 130 246;
        }

        [data-theme="emerald"] {
            --primary: 16 185 129;
            --primary-dark: 5 150 105;
            --accent: 52 211 153;
            --sidebar-from: 5 150 105;
            --sidebar-to: 16 185 129;
        }

        [data-theme="violet"] {
            --primary: 139 92 246;
            --primary-dark: 124 58 237;
            --accent: 167 139 250;
            --sidebar-from: 124 58 237;
            --sidebar-to: 139 92 246;
        }

        [data-theme="amber"] {
            --primary: 245 158 11;
            --primary-dark: 217 119 6;
            --accent: 251 191 36;
            --sidebar-from: 217 119 6;
            --sidebar-to: 245 158 11;
        }

        [data-theme="slate"] {
            --primary: 71 85 105;
            --primary-dark: 51 65 85;
            --accent: 100 116 139;
            --sidebar-from: 51 65 85;
            --sidebar-to: 71 85 105;
        }

        .sidebar-transition {
            transition: width 300ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item {
            position: relative;
            transition: all 0.2s ease;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: rgb(var(--primary));
            transition: height 0.2s ease;
            border-radius: 0 3px 3px 0;
        }

        .nav-item:hover::before,
        .nav-item.active::before {
            height: 70%;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.3);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.5);
        }

        .alert-enter {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-badge {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .gradient-header {
            background: linear-gradient(135deg, rgb(var(--sidebar-from)), rgb(var(--sidebar-to)));
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div x-data="{
        sidebarOpen: false,
        notificationsOpen: false
    }" class="flex h-screen overflow-hidden bg-gray-50">

        <!-- Overlay móvil -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
            x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-20 bg-gray-900 bg-opacity-50 lg:hidden"></div>

        <!-- Sidebar -->
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-72'
            ]"
            class="fixed inset-y-0 left-0 z-30 w-72 bg-white border-r border-gray-200 transition-all duration-300 ease-in-out lg:translate-x-0 lg:static sidebar-transition shadow-xl lg:shadow-none">

            <!-- Logo Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 gradient-header">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="text-lg font-bold text-white">AsistPro</span>
                </div>

                <div class="flex items-center space-x-2">
                    <button @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                    <button @click="sidebarOpen = false"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar">
                @role('Administrator')
                    <div class="mb-6">
                        <p x-show="!sidebarCollapsed"
                            class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administración
                        </p>

                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-item flex items-center px-3 py-3 text-sm font-medium rounded-lg group {{ request()->routeIs('admin.dashboard') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="flex-1">Dashboard</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}"
                            class="nav-item flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg group {{ request()->routeIs('admin.users.*') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span x-show="!sidebarCollapsed">Usuarios</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}"
                            class="nav-item flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg group {{ request()->routeIs('admin.reports.*') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="!sidebarCollapsed">Reportes</span>
                        </a>

                        <a href="{{ route('admin.documents.index') }}"
                            class="nav-item flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg group {{ request()->routeIs('admin.documents.*') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span x-show="!sidebarCollapsed">Documentos</span>
                        </a>

                    </div>
                @endrole

                <div>
                    <p x-show="!sidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Gestión</p>

                    <a href="{{ route('meetings.index') }}"
                        class="nav-item flex items-center px-3 py-3 text-sm font-medium rounded-lg group {{ request()->routeIs('meetings.*') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed">Reuniones</span>
                    </a>

                    <a href="{{ route('participants.index') }}"
                        class="nav-item flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg group {{ request()->routeIs('participants.*') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed">Participantes</span>
                    </a>

                    <a href="{{ route('attendance.register') }}"
                        class="nav-item flex items-center px-3 py-3 mt-1 text-sm font-medium rounded-lg group {{ request()->routeIs('attendance.*') ? 'active text-white bg-gradient-to-r from-blue-600 to-blue-500 shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span x-show="!sidebarCollapsed">Registrar Asistencia</span>
                    </a>


                </div>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Header -->
            <header class="h-16 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between h-full px-6">
                    <!-- Sección Izquierda -->
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true"
                            class="p-2 text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Barra de búsqueda -->
                        <div class="relative hidden md:block">
                            <input type="text" placeholder="Buscar..."
                                class="w-64 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Sección Derecha -->
                    <div class="flex items-center space-x-4">
                        <!-- Selector de tema -->
                        <div class="relative hidden sm:block" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="p-2 text-gray-500 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Tema de color</p>
                                <button @click="theme = 'corporate'; open = false"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center space-x-3">
                                    <span class="w-4 h-4 rounded-full bg-blue-600"></span>
                                    <span>Corporate (Azul)</span>
                                </button>
                                <button @click="theme = 'emerald'; open = false"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center space-x-3">
                                    <span class="w-4 h-4 rounded-full bg-emerald-600"></span>
                                    <span>Emerald (Verde)</span>
                                </button>
                                <button @click="theme = 'violet'; open = false"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center space-x-3">
                                    <span class="w-4 h-4 rounded-full bg-violet-600"></span>
                                    <span>Violet (Morado)</span>
                                </button>
                                <button @click="theme = 'amber'; open = false"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center space-x-3">
                                    <span class="w-4 h-4 rounded-full bg-amber-600"></span>
                                    <span>Amber (Ámbar)</span>
                                </button>
                                <button @click="theme = 'slate'; open = false"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center space-x-3">
                                    <span class="w-4 h-4 rounded-full bg-slate-600"></span>
                                    <span>Slate (Neutro)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Notificaciones -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="relative p-2 text-gray-500 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span
                                    class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full notification-badge"></span>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak x-transition
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>

                                <div class="py-2">
                                    <a href="#"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Mi Perfil
                                    </a>
                                    <a href="#"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Configuración
                                    </a>
                                </div>

                                <div class="border-t border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg transition-colors">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- User Card al final -->
                        <div x-show="!sidebarCollapsed" x-transition
                            class=" m-3 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-full">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex items-center justify-center w-10 h-10 text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full font-semibold text-sm shadow-md">
                                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                </div>
                                <div class="pr-4">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido de la Página -->
            <main class="flex-1 overflow-y-auto bg-gray-50 custom-scrollbar">
                <div class="container px-6 py-8 mx-auto max-w-7xl">
                    <!-- Alertas de éxito -->
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-90"
                            class="mb-6 overflow-hidden bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-sm alert-enter">
                            <div class="flex items-center p-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                                <button @click="show = false"
                                    class="ml-auto flex-shrink-0 text-green-600 hover:text-green-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Alertas de error -->
                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-90"
                            class="mb-6 overflow-hidden bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-lg shadow-sm alert-enter">
                            <div class="flex items-center p-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                </div>
                                <button @click="show = false"
                                    class="ml-auto flex-shrink-0 text-red-600 hover:text-red-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Alertas de validación -->
                    @if ($errors->any())
                        <div x-data="{ show: true }" x-show="show"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-90"
                            class="mb-6 overflow-hidden bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-lg shadow-sm alert-enter">
                            <div class="p-4">
                                <div class="flex items-center mb-2">
                                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="text-sm font-semibold text-red-800">Se encontraron los siguientes
                                        errores:</h3>
                                    <button @click="show = false"
                                        class="ml-auto text-red-600 hover:text-red-800 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <ul class="ml-9 mt-2 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li class="text-sm text-red-700">• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Contenido dinámico -->
                    @yield('content')
                </div>
            </main>

            <!-- Footer (opcional) -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
                    <div class="flex items-center space-x-4">
                        <a href="#" class="hover:text-gray-900 transition-colors">Soporte</a>
                        <a href="#" class="hover:text-gray-900 transition-colors">Privacidad</a>
                        <a href="#" class="hover:text-gray-900 transition-colors">Términos</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>

</html>

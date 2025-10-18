@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Bienvenido de nuevo, {{ auth()->user()->name }}</p>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Usuarios -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalUsers ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('participants.index') }}"
                        class="text-sm font-medium text-green-600 hover:text-green-800">
                        Ver todos →
                    </a>
                </div>
            </div>
        </div>

        <!-- Reuniones Activas -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Reuniones Hoy</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $todayMeetings ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('meetings.index') }}"
                        class="text-sm font-medium text-purple-600 hover:text-purple-800">
                        Ver todas →
                    </a>
                </div>
            </div>
        </div>

        <!-- Asistencias Hoy -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Asistencias Hoy</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $todayAttendances ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('attendance.register') }}"
                        class="text-sm font-medium text-orange-600 hover:text-orange-800">
                        Registrar →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Reuniones Recientes -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Reuniones Recientes</h3>
                    <a href="{{ route('meetings.index') }}"
                        class="text-sm font-medium text-blue-600 hover:text-blue-800">
                        Ver todas
                    </a>
                </div>

                @if (isset($recentMeetings) && $recentMeetings->count() > 0)
                    <div class="space-y-3">
                        @foreach ($recentMeetings as $meeting)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $meeting->title }}</h4>
                                    <p class="text-sm text-gray-600">{{ $meeting->date->format('d/m/Y') }} -
                                        {{ $meeting->opening_time }}</p>
                                </div>
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                                @if ($meeting->status === 'open') bg-green-100 text-green-800
                                @elseif($meeting->status === 'closed') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($meeting->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">No hay reuniones registradas</p>
                        <a href="{{ route('meetings.create') }}"
                            class="inline-block px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Crear primera reunión
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.users.create') }}"
                        class="flex flex-col items-center p-6 text-center transition bg-blue-50 rounded-lg hover:bg-blue-100">
                        <svg class="w-8 h-8 mb-2 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Crear Usuario</span>
                    </a>

                    <a href="{{ route('meetings.create') }}"
                        class="flex flex-col items-center p-6 text-center transition bg-green-50 rounded-lg hover:bg-green-100">
                        <svg class="w-8 h-8 mb-2 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Nueva Reunión</span>
                    </a>

                    <a href="{{ route('participants.create') }}"
                        class="flex flex-col items-center p-6 text-center transition bg-purple-50 rounded-lg hover:bg-purple-100">
                        <svg class="w-8 h-8 mb-2 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Agregar Participante</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}"
                        class="flex flex-col items-center p-6 text-center transition bg-orange-50 rounded-lg hover:bg-orange-100">
                        <svg class="w-8 h-8 mb-2 text-orange-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Ver Reportes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

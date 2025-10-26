@extends('layouts.app')

@section('title', 'Reuniones')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Reuniones</h1>
                <p class="mt-1 text-sm text-gray-600">Gestiona las reuniones y controla la asistencia</p>
            </div>
            <a href="{{ route('meetings.create') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nueva Reunión
            </a>
        </div>

        <!-- Filters -->
        <div class="p-6 bg-white rounded-lg shadow">
            <form method="GET" action="{{ route('meetings.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Título de la reunión..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Abierta</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Filtrar
                    </button>
                    <a href="{{ route('meetings.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Meetings Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($meetings as $meeting)
                <div class="overflow-hidden bg-white rounded-lg shadow hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ $meeting->title }}</h3>
                                <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $meeting->description }}</p>
                            </div>
                            <span
                                class="ml-2 px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap
                            @if ($meeting->status === 'open') bg-green-100 text-green-800
                            @elseif($meeting->status === 'closed') bg-gray-100 text-gray-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $meeting->status_label }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $meeting->date->format('d/m/Y') }}
                            </div>

                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ \Carbon\Carbon::parse($meeting->opening_time)->format('h:i A') }} -
                                {{ \Carbon\Carbon::parse($meeting->closing_time)->format('h:i A') }}
                            </div>

                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ $meeting->attendances_count ?? 0 }} asistentes
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200">
                            <a href="{{ route('meetings.show', $meeting) }}"
                                class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                Ver detalles →
                            </a>

                            <div class="flex items-center space-x-2">
                                @if ($meeting->status === 'draft')
                                    <form method="POST" action="{{ route('meetings.open', $meeting) }}">
                                        @csrf
                                        <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Abrir reunión">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                @if ($meeting->status === 'open')
                                    <form method="POST" action="{{ route('meetings.close', $meeting) }}" x-data
                                        @submit.prevent="if(confirm('¿Cerrar esta reunión? No se podrán registrar más asistencias.')) $el.submit()">
                                        @csrf
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                            title="Cerrar reunión">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                @if ($meeting->status === 'closed')
                                    <span class="p-2 text-gray-400 bg-gray-50 rounded-lg cursor-not-allowed"
                                        title="Edición deshabilitada (reunión cerrada)" aria-disabled="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </span>
                                @else
                                    <a href="{{ route('meetings.edit', $meeting) }}"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="py-12 text-center bg-white rounded-lg shadow">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-4 text-lg font-medium text-gray-900">No hay reuniones</p>
                        <p class="mt-1 text-sm text-gray-600">Comienza creando tu primera reunión</p>
                        <a href="{{ route('meetings.create') }}"
                            class="inline-block px-6 py-3 mt-6 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Crear primera reunión
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($meetings->hasPages())
            <div class="px-6 py-4 bg-white rounded-lg shadow">
                {{ $meetings->links() }}
            </div>
        @endif
    </div>
@endsection

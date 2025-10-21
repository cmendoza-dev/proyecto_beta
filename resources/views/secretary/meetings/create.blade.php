@extends('layouts.app')

@section('title', isset($meeting) ? 'Editar Reunión' : 'Nueva Reunión')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('meetings.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a reuniones
            </a>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ isset($meeting) ? 'Editar Reunión' : 'Nueva Reunión' }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ isset($meeting) ? 'Modifica los datos de la reunión' : 'Configura los detalles de la nueva reunión' }}
                </p>
            </div>

            <form method="POST"
                action="{{ isset($meeting) ? route('meetings.update', $meeting) : route('meetings.store') }}"
                class="p-6">
                @csrf
                @if (isset($meeting))
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <!-- Título -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Título de la Reunión <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                            value="{{ old('title', $meeting->title ?? '') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                            placeholder="Ejemplo: Reunión Mensual de Coordinación">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción <span class="text-gray-500">(opcional)</span>
                        </label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                            placeholder="Describe el propósito y agenda de la reunión...">{{ old('description', $meeting->description ?? '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Lugar -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                Lugar <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="location" name="location"
                                value="{{ old('location', $meeting->location ?? '') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('location') border-red-500 @enderror"
                                placeholder="Ejemplo: Sala de Conferencias A">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Tipo de Reunión -->
                        <div>
                            <label for="meeting_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Reunión <span class="text-red-500">*</span>
                            </label>
                            <select id="meeting_type" name="meeting_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('meeting_type') border-red-500 @enderror">
                                <option value="">Seleccione un tipo de reunión</option>
                                <option value="presencial"
                                    {{ old('meeting_type', $meeting->meeting_type ?? '') === 'presencial' ? 'selected' : '' }}>
                                    Presencial</option>
                                <option value="virtual"
                                    {{ old('meeting_type', $meeting->meeting_type ?? '') === 'virtual' ? 'selected' : '' }}>
                                    Virtual</option>
                            </select>
                            @error('meeting_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Fecha -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date" name="date"
                            value="{{ old('date', isset($meeting) ? $meeting->date->format('Y-m-d') : '') }}" required
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Hora de Apertura -->
                        <div>
                            <label for="opening_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Hora de Apertura <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="opening_time" name="opening_time"
                                value="{{ old('opening_time', $meeting->opening_time ?? '') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('opening_time') border-red-500 @enderror">
                            <p class="mt-1 text-sm text-gray-500">Inicio del registro de asistencia</p>
                            @error('opening_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hora de Cierre -->
                        <div>
                            <label for="closing_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Hora de Cierre <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="closing_time" name="closing_time"
                                value="{{ old('closing_time', $meeting->closing_time ?? '') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('closing_time') border-red-500 @enderror">
                            <p class="mt-1 text-sm text-gray-500">Fin del registro de asistencia</p>
                            @error('closing_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    @if (isset($meeting))
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado
                            </label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="draft" {{ old('status', $meeting->status) === 'draft' ? 'selected' : '' }}>
                                    Borrador</option>
                                <option value="open" {{ old('status', $meeting->status) === 'open' ? 'selected' : '' }}>
                                    Abierta</option>
                                <option value="closed"
                                    {{ old('status', $meeting->status) === 'closed' ? 'selected' : '' }}>Cerrada</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">
                                <strong>Borrador:</strong> En preparación.
                                <strong>Abierta:</strong> Aceptando asistencias.
                                <strong>Cerrada:</strong> Finalizada.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end pt-6 mt-6 space-x-3 border-t border-gray-200">
                    <a href="{{ route('meetings.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" name="action" value="save"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        {{ isset($meeting) ? 'Actualizar Reunión' : 'Crear Reunión' }}
                    </button>
                    @if (!isset($meeting))
                        <button type="submit" name="action" value="save_and_open"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Crear y Abrir
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="p-4 mt-6 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Importante</h3>
                    <ul class="mt-1 text-sm text-blue-700 list-disc list-inside">
                        <li>Las reuniones se crean en estado <strong>Borrador</strong> por defecto</li>
                        <li>Debes <strong>abrir</strong> la reunión para permitir el registro de asistencias</li>
                        <li>Solo se pueden registrar asistencias dentro del horario configurado</li>
                        <li>Una vez <strong>cerrada</strong>, no se podrán registrar más asistencias</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

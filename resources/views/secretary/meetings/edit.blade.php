@extends('layouts.app')

@section('title', 'Editar Reunión')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('meetings.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a reuniones
        </a>
    </div>

    <div class="overflow-hidden bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Editar Reunión</h2>
            <p class="mt-1 text-sm text-gray-600">Modifica la información y guarda los cambios.</p>
        </div>

        <form method="POST" action="{{ route('meetings.update', $meeting) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Título -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $meeting->title) }}"
                        required
                        maxlength="255"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                        placeholder="Nombre de la reunión"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                        placeholder="Detalles de la reunión"
                    >{{ old('description', $meeting->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Fecha -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="date"
                            name="date"
                            value="{{ old('date', \Illuminate\Support\Carbon::parse($meeting->date)->format('Y-m-d')) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date') border-red-500 @enderror"
                        >
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hora de apertura -->
                    <div>
                        <label for="opening_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Hora de apertura <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="time"
                            id="opening_time"
                            name="opening_time"
                            value="{{ old('opening_time', substr($meeting->opening_time, 0, 5)) }}"
                            step="60"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('opening_time') border-red-500 @enderror"
                        >
                        @error('opening_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hora de cierre -->
                    <div>
                        <label for="closing_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Hora de cierre <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="time"
                            id="closing_time"
                            name="closing_time"
                            value="{{ old('closing_time', substr($meeting->closing_time, 0, 5)) }}"
                            step="60"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('closing_time') border-red-500 @enderror"
                        >
                        @error('closing_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Ubicación -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                            Ubicación
                        </label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="{{ old('location', $meeting->location) }}"
                            maxlength="255"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('location') border-red-500 @enderror"
                            placeholder="Sala / Dirección / Enlace"
                        >
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror"
                        >
                            @php
                                $current = old('status', $meeting->status);
                            @endphp
                            <option value="draft" {{ $current === 'draft' ? 'selected' : '' }}>Borrador</option>
                            <option value="open" {{ $current === 'open' ? 'selected' : '' }}>Abierta</option>
                            <option value="closed" {{ $current === 'closed' ? 'selected' : '' }}>Cerrada</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Solo abre/cierra si corresponde al flujo de la reunión.</p>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-200">
                <a href="{{ route('meetings.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>

                <div class="flex items-center gap-3">
                    <form action="{{ route('meetings.destroy', $meeting) }}" method="POST" onsubmit="return confirm('¿Eliminar esta reunión?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Eliminar
                        </button>
                    </form>

                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

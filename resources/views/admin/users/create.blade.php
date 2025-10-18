@extends('layouts.app')

@section('title', isset($user) ? 'Editar Usuario' : 'Crear Usuario')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a usuarios
        </a>
    </div>

    <div class="overflow-hidden bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">
                {{ isset($user) ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ isset($user) ? 'Modifica la información del usuario' : 'Complete los datos del nuevo usuario del sistema' }}
            </p>
        </div>

        <form
            method="POST"
            action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}"
            class="p-6"
        >
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="space-y-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name ?? '') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Juan Pérez García"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email ?? '') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="usuario@ejemplo.com"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror"
                    >
                        <option value="">Seleccione un rol</option>
                        <option value="Administrator" {{ old('role', isset($user) && $user->hasRole('Administrator') ? 'Administrator' : '') === 'Administrator' ? 'selected' : '' }}>
                            Administrador
                        </option>
                        <option value="Secretary" {{ old('role', isset($user) && $user->hasRole('Secretary') ? 'Secretary' : '') === 'Secretary' ? 'selected' : '' }}>
                            Secretario
                        </option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">
                        <strong>Administrador:</strong> Acceso completo al sistema.
                        <strong>Secretario:</strong> Solo gestiona asistencias.
                    </p>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                        @if(!isset($user))
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-gray-500">(dejar en blanco para mantener actual)</span>
                        @endif
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        {{ !isset($user) ? 'required' : '' }}
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                        placeholder="Mínimo 8 caracteres"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmar Contraseña
                        @if(!isset($user))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        {{ !isset($user) ? 'required' : '' }}
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Repita la contraseña"
                    >
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end pt-6 mt-6 space-x-3 border-t border-gray-200">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                    {{ isset($user) ? 'Actualizar Usuario' : 'Crear Usuario' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

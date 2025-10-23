@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mi Perfil</h1>
        <p class="mt-2 text-sm text-gray-600">Administra tu información personal y configuración de cuenta</p>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center alert-enter">
        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-green-800 font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid gap-6">
        <!-- Información del Perfil -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Información del Perfil</h2>
                <p class="mt-1 text-sm text-gray-600">Actualiza tu nombre y correo electrónico</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="p-6">
                @csrf
                @method('PATCH')

                <div class="grid gap-6">
                    <!-- Avatar -->
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center justify-center w-20 h-20 text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full font-bold text-2xl shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            <p class="text-xs text-gray-400 mt-1">Miembro desde {{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $user->name) }}"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $user->email) }}"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rol (solo lectura) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rol
                        </label>
                        <input type="text"
                               value="{{ $user->getRoleNames()->first() ?? 'Usuario' }}"
                               disabled
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Cambiar Contraseña</h2>
                <p class="mt-1 text-sm text-gray-600">Asegúrate de usar una contraseña segura</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="p-6" x-data="{ showPasswords: false }">
                @csrf
                @method('PUT')

                <div class="grid gap-6">
                    <!-- Contraseña Actual -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña Actual <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPasswords ? 'text' : 'password'"
                                   name="current_password"
                                   id="current_password"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('current_password', 'updatePassword') border-red-500 @enderror">
                        </div>
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nueva Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input :type="showPasswords ? 'text' : 'password'"
                               name="password"
                               id="password"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('password', 'updatePassword') border-red-500 @enderror">
                        @error('password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Nueva Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input :type="showPasswords ? 'text' : 'password'"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>

                    <!-- Mostrar Contraseñas -->
                    <div class="flex items-center">
                        <input type="checkbox"
                               id="show_passwords"
                               x-model="showPasswords"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="show_passwords" class="ml-2 text-sm text-gray-700 cursor-pointer">
                            Mostrar contraseñas
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>

        <!-- Eliminar Cuenta -->
        <div class="bg-white rounded-lg shadow-sm border border-red-200">
            <div class="px-6 py-5 border-b border-red-200">
                <h2 class="text-lg font-semibold text-red-900">Zona de Peligro</h2>
                <p class="mt-1 text-sm text-red-600">Acciones irreversibles con tu cuenta</p>
            </div>

            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-900">Eliminar Cuenta</h3>
                        <p class="mt-1 text-sm text-gray-600">Una vez eliminada tu cuenta, todos tus datos serán permanentemente borrados.</p>
                    </div>
                    <button type="button"
                            @click="$dispatch('open-modal', 'delete-account')"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        Eliminar Cuenta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div x-data="{ show: false }"
     @open-modal.window="show = ($event.detail === 'delete-account')"
     @close-modal.window="show = false"
     @keydown.escape.window="show = false"
     x-show="show"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             @click="show = false"></div>

        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">

            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-medium text-center text-gray-900">¿Eliminar tu cuenta?</h3>
            <p class="mt-2 text-sm text-center text-gray-600">Esta acción no se puede deshacer. Todos tus datos serán eliminados permanentemente.</p>

            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6">
                @csrf
                @method('DELETE')

                <div>
                    <label for="password_delete" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirma tu contraseña
                    </label>
                    <input type="password"
                           name="password"
                           id="password_delete"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('password', 'userDeletion') border-red-500 @enderror">
                    @error('password', 'userDeletion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button"
                            @click="show = false"
                            class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Eliminar Cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- filepath: c:\xampp\htdocs\proyecto_beta\resources\views\admin\users\edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center mb-4 text-sm font-medium text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a usuarios
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Editar Usuario</h1>
            <p class="mt-2 text-sm text-gray-600">Actualiza la información del usuario</p>
        </div>

        <!-- Formulario -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Información Personal -->
                <div class="pb-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Personal</h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $user->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror"
                                required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $user->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 @enderror"
                                required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- DNI -->
                        <div>
                            <label for="dni" class="block text-sm font-medium text-gray-700">
                                DNI <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                name="dni"
                                id="dni"
                                value="{{ old('dni', $user->dni) }}"
                                maxlength="8"
                                pattern="[0-9]{8}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dni') border-red-300 @enderror"
                                required>
                            @error('dni')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">8 dígitos numéricos</p>
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Teléfono
                            </label>
                            <input type="text"
                                name="phone"
                                id="phone"
                                value="{{ old('phone', $user->phone) }}"
                                maxlength="9"
                                pattern="[0-9]{9}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">9 dígitos numéricos (opcional)</p>
                        </div>
                    </div>
                </div>

                <!-- Rol y Permisos -->
                <div class="pb-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rol y Permisos</h3>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">
                            Rol <span class="text-red-500">*</span>
                        </label>
                        <select name="role"
                            id="role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('role') border-red-300 @enderror"
                            required>
                            <option value="">Seleccionar rol...</option>
                            <option value="Administrator" {{ old('role', $user->role) === 'Administrator' ? 'selected' : '' }}>
                                Administrador
                            </option>
                            <option value="Secretary" {{ old('role', $user->role) === 'Secretary' ? 'selected' : '' }}>
                                Secretario
                            </option>
                            <option value="Participant" {{ old('role', $user->role) === 'Participant' ? 'selected' : '' }}>
                                Participante
                            </option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Descripción de roles -->
                        <div class="mt-3 p-3 bg-blue-50 rounded-md" x-data="{ role: '{{ old('role', $user->role) }}' }" x-init="$watch('role', value => role = value)">
                            <p class="text-sm text-blue-800" x-show="role === 'Administrator'">
                                <strong>Administrador:</strong> Acceso completo al sistema. Puede gestionar usuarios, ver todos los reportes y configurar el sistema.
                            </p>
                            <p class="text-sm text-blue-800" x-show="role === 'Secretary'">
                                <strong>Secretario:</strong> Puede crear reuniones, registrar asistencia y generar reportes.
                            </p>
                            <p class="text-sm text-blue-800" x-show="role === 'Participant'">
                                <strong>Participante:</strong> Puede ver las reuniones a las que asiste y descargar documentos.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cambiar Contraseña (Opcional) -->
                <div class="pb-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cambiar Contraseña</h3>
                    <p class="text-sm text-gray-600 mb-4">Deja estos campos en blanco si no deseas cambiar la contraseña</p>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Nueva Contraseña -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Nueva Contraseña
                            </label>
                            <input type="password"
                                name="password"
                                id="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-300 @enderror">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirmar Nueva Contraseña
                            </label>
                            <input type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Debe coincidir con la contraseña</p>
                        </div>
                    </div>
                </div>

                <!-- Estado del Usuario -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado</h3>

                    <div class="flex items-center">
                        <input type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                            Usuario activo
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Los usuarios inactivos no podrán iniciar sesión</p>
                </div>

                <!-- Información adicional -->
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Importante:</strong> Los cambios se aplicarán inmediatamente. Si cambias el rol, los permisos del usuario se actualizarán de acuerdo al nuevo rol asignado.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Metadatos -->
                @if($user->created_at)
                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-xs text-gray-500">
                            <strong>Creado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}
                        </p>
                        @if($user->updated_at && $user->updated_at != $user->created_at)
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Última actualización:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                @endif

                <!-- Botones -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.users.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancelar
                    </a>

                    <div class="flex space-x-3">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Actualizar descripción de rol cuando cambia el select
        document.getElementById('role').addEventListener('change', function(e) {
            const event = new CustomEvent('role-changed', { detail: e.target.value });
            document.dispatchEvent(event);
        });
    </script>
    @endpush
@endsection

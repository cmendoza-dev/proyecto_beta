@extends('layouts.app')

@section('title', isset($participant) ? 'Editar Participante' : 'Crear Participante')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('participants.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a participantes
            </a>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ isset($participant) ? 'Editar Participante' : 'Nuevo Participante' }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ isset($participant) ? 'Modifica la información del participante' : 'Registra los datos del participante que podrá asistir a reuniones' }}
                </p>
            </div>

            <form method="POST"
                action="{{ isset($participant) ? route('participants.update', $participant) : route('participants.store') }}"
                class="p-6" x-data="{
                    dni: '{{ old('dni', $participant->dni ?? '') }}',
                    firstName: '{{ old('first_name', $participant->first_name ?? '') }}',
                    lastName: '{{ old('last_name', $participant->last_name ?? '') }}',
                    searchingReniec: false,
                    reniecMessage: '',
                    reniecMessageType: '',
                    async searchReniec() {
                        if (this.dni.length !== 8) {
                            this.showMessage('El DNI debe tener 8 dígitos', 'error');
                            return;
                        }

                        this.searchingReniec = true;
                        this.reniecMessage = '';

                        try {
                            const response = await fetch('{{ route('reniec.search') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ dni: this.dni })
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.firstName = data.data.first_name;
                                this.lastName = data.data.last_name;
                                this.showMessage('Datos encontrados en RENIEC', 'success');
                            } else {
                                this.showMessage(data.message || 'No se encontraron datos', 'error');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            this.showMessage('Error al consultar RENIEC. Ingrese los datos manualmente.', 'error');
                        } finally {
                            this.searchingReniec = false;
                        }
                    },
                    showMessage(message, type) {
                        this.reniecMessage = message;
                        this.reniecMessageType = type;
                        setTimeout(() => {
                            this.reniecMessage = '';
                        }, 5000);
                    }
                }">
                @csrf
                @if (isset($participant))
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <!-- Mensaje de RENIEC -->
                    <div x-show="reniecMessage" x-transition class="p-4 rounded-lg"
                        :class="{
                            'bg-green-50 border border-green-200': reniecMessageType === 'success',
                            'bg-red-50 border border-red-200': reniecMessageType === 'error'
                        }">
                        <p class="text-sm"
                            :class="{
                                'text-green-800': reniecMessageType === 'success',
                                'text-red-800': reniecMessageType === 'error'
                            }"
                            x-text="reniecMessage"></p>
                    </div>

                    <!-- DNI -->
                    <div>
                        <label for="dni" class="block text-sm font-medium text-gray-700 mb-2">
                            DNI <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <input type="text" id="dni" name="dni" x-model="dni" maxlength="8"
                                pattern="\d{8}" required {{ isset($participant) ? 'readonly' : '' }}
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('dni') border-red-500 @enderror {{ isset($participant) ? 'bg-gray-100' : '' }}"
                                placeholder="12345678">
                            @if (!isset($participant))
                                <button type="button" @click="searchReniec()"
                                    :disabled="dni.length !== 8 || searchingReniec"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed inline-flex items-center">
                                    <svg x-show="searchingReniec" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span x-show="!searchingReniec">Buscar RENIEC</span>
                                    <span x-show="searchingReniec">Buscando...</span>
                                </button>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Ingrese el DNI de 8 dígitos</p>
                        @error('dni')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Nombres -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombres <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="first_name" name="first_name" x-model="firstName" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror"
                                placeholder="Juan Carlos">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Apellidos -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name" x-model="lastName" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-500 @enderror"
                                placeholder="Pérez García">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Teléfono -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone"
                                value="{{ old('phone', $participant->phone ?? '') }}" required maxlength="15"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                                placeholder="987654321">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $participant->email ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                placeholder="correo@ejemplo.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Organización -->
                        <div>
                            <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">
                                Organización <span class="text-red-500">*</span>
                            </label>
                            <select id="organization" name="organization" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('organization') border-red-500 @enderror">
                                <option value="" disabled selected>Seleccione una organización</option>
                                <option value="Organización 1" {{ old('organization', $participant->organization ?? '') == 'Organización 1' ? 'selected' : '' }}>Organización 1</option>
                                <option value="Organización 2" {{ old('organization', $participant->organization ?? '') == 'Organización 2' ? 'selected' : '' }}>Organización 2</option>
                                <option value="Organización 3" {{ old('organization', $participant->organization ?? '') == 'Organización 3' ? 'selected' : '' }}>Organización 3</option>
                                <!-- Agregar más opciones según sea necesario -->
                            </select>
                            @error('organization')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cargo -->
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-2">
                                Cargo <span class="text-red-500">*</span>
                            </label>
                            <select id="position" name="position" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('position') border-red-500 @enderror">
                                <option value="" disabled selected>Seleccione un cargo</option>
                                <option value="Gerente" {{ old('position', $participant->position ?? '') == 'Gerente' ? 'selected' : '' }}>Gerente</option>
                                <option value="Supervisor" {{ old('position', $participant->position ?? '') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                                <option value="Coordinador" {{ old('position', $participant->position ?? '') == 'Coordinador' ? 'selected' : '' }}>Coordinador</option>
                                <option value="Analista" {{ old('position', $participant->position ?? '') == 'Analista' ? 'selected' : '' }}>Analista</option>
                                <option value="Asistente" {{ old('position', $participant->position ?? '') == 'Asistente' ? 'selected' : '' }}>Asistente</option>
                            </select>
                            @error('position')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $participant->is_active ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Participante activo</span>
                        </label>
                        <p class="mt-1 ml-6 text-sm text-gray-500">Los participantes inactivos no podrán registrar
                            asistencia</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end pt-6 mt-6 space-x-3 border-t border-gray-200">
                    <a href="{{ route('participants.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        {{ isset($participant) ? 'Actualizar Participante' : 'Crear Participante' }}
                    </button>
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
                    <h3 class="text-sm font-medium text-blue-800">Integración RENIEC</h3>
                    <p class="mt-1 text-sm text-blue-700">
                        Si la API no responde, puede ingresar los datos manualmente. Los datos se autocompletarán si se
                        encuentran en RENIEC.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

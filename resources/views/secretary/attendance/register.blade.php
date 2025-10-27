{{-- filepath: resources\views\secretary\attendance\register.blade.php --}}
@extends('layouts.app')

@section('title', 'Registrar Asistencia')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Registrar Asistencia</h1>
            <p class="mt-1 text-sm text-gray-600">Escanea el DNI o ingresa manualmente para registrar la asistencia</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Panel: Registration Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Meeting Selection -->
                <div class="p-6 bg-white rounded-lg shadow">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Seleccionar Reunión</h3>
                    <form method="GET" action="{{ route('attendance.register') }}">
                        <select name="meeting" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccione una reunión</option>
                            @foreach ($openMeetings as $meet)
                                <option value="{{ $meet->id }}" {{ request('meeting') == $meet->id ? 'selected' : '' }}>
                                    {{ $meet->title }} - {{ $meet->date->format('d/m/Y') }}
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $meet->opening_time)->format('h:i A') }}

                                </option>
                            @endforeach
                        </select>
                    </form>

                    @if ($selectedMeeting)
                        <div class="p-4 mt-4 rounded-lg bg-green-50">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ $selectedMeeting->title }}</p>
                                    <p class="text-sm text-green-700">
                                        {{ $selectedMeeting->date->format('d/m/Y') }} |
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $selectedMeeting->opening_time)->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $selectedMeeting->closing_time)->format('h:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($selectedMeeting)
                    <!-- Scanner & Manual Input -->
                    <div class="p-6 bg-white rounded-lg shadow" x-data="attendanceScanner()">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Método de Registro</h3>
                            <button type="button" @click="toggleScanner()"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition"
                                :class="scannerActive ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span x-text="scannerActive ? 'Detener Escáner' : 'Activar Escáner'"></span>
                            </button>
                        </div>

                        <!-- Scanner Container -->
                        <div x-show="scannerActive" x-cloak class="mb-6">
                            <div id="scanner-container"
                                class="relative overflow-hidden border-4 border-blue-500 rounded-lg transition-all"
                                style="height: 400px;">
                                <div class="absolute inset-0 flex items-center justify-center bg-gray-900">
                                    <p class="text-white">Iniciando cámara...</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center mt-3 space-x-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></div>
                                    <p class="text-sm text-gray-600">Escáner activo</p>
                                </div>
                                <div class="text-sm text-gray-600">
                                    Detecciones: <span x-text="scanCount" class="font-bold text-blue-600">0</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-center text-gray-600">
                                📱 Coloca el código de barras del DNI frente a la cámara (parte posterior del DNI)
                            </p>
                        </div>

                        <!-- Manual Input Form -->
                        <form method="POST" action="{{ route('attendance.store') }}" id="attendance-form">
                            @csrf
                            <input type="hidden" name="meeting_id" value="{{ $selectedMeeting->id }}">

                            <div class="space-y-4">
                                <div>
                                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-2">
                                        DNI del Participante <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="dni" name="dni" x-model="dniValue" maxlength="8"
                                        pattern="\d{8}" required autofocus
                                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('dni') border-red-500 @enderror"
                                        placeholder="12345678">
                                    @error('dni')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">O usa el escáner para capturar automáticamente</p>
                                </div>

                                <button type="submit"
                                    class="w-full px-4 py-3 text-base font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Registrar Asistencia
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-12 text-center bg-white rounded-lg shadow">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-4 text-lg font-medium text-gray-900">No hay reuniones abiertas</p>
                        <p class="mt-1 text-sm text-gray-600">Selecciona o abre una reunión para comenzar a registrar
                            asistencias</p>
                        <a href="{{ route('meetings.index') }}"
                            class="inline-block px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Ver reuniones
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right Panel: Recent Attendances -->
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <div class="p-6 bg-white rounded-lg shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Últimas Asistencias</h3>

                        @if ($selectedMeeting && $recentAttendances->count() > 0)
                            <div class="space-y-3">
                                @foreach ($recentAttendances as $attendance)
                                    <div class="flex items-start p-3 rounded-lg bg-gray-50">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-white bg-green-600 rounded-full">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $attendance->participant->first_name }}
                                                {{ $attendance->participant->last_name }}</p>
                                            <p class="text-xs text-gray-600">DNI: {{ $attendance->participant->dni }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $attendance->registered_at->timezone('America/Lima')->format('h:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-4 mt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">Total registrados:</span>
                                    <span class="text-2xl font-bold text-blue-600">{{ $totalAttendances }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">Sin asistencias aún</p>
                            </div>
                        @endif
                    </div>

                    <!-- Info Card -->
                    <div class="p-4 mt-6 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Consejos para escanear</h3>
                                <ul class="mt-1 text-sm text-blue-700 space-y-1">
                                    <li>✓ Buena iluminación</li>
                                    <li>✓ Mantén el DNI estable</li>
                                    <li>✓ Código de barras visible y completo</li>
                                    <li>✓ Distancia: 10-20 cm</li>
                                    <li>✓ Usar parte posterior del DNI</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Quagga.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    @push('scripts')
        <script>
            // Mostrar alertas de sesión
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626'
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    html: '@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
                    confirmButtonColor: '#dc2626'
                });
            @endif
        </script>
    @endpush
@endsection

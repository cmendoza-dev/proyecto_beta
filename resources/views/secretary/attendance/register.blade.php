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
                    <select
                        name="meeting"
                        onchange="this.form.submit()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Seleccione una reunión</option>
                        @foreach($openMeetings as $meet)
                            <option value="{{ $meet->id }}" {{ request('meeting') == $meet->id ? 'selected' : '' }}>
                                {{ $meet->title }} - {{ $meet->date->format('d/m/Y') }} {{ \Carbon\Carbon::createFromFormat('H:i:s', $meet->opening_time)->format('h:i A') }}

                            </option>
                        @endforeach
                    </select>
                </form>

                @if($selectedMeeting)
                    <div class="p-4 mt-4 rounded-lg bg-green-50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

            @if($selectedMeeting)
                <!-- Scanner & Manual Input -->
                <div class="p-6 bg-white rounded-lg shadow" x-data="attendanceScanner()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Método de Registro</h3>
                        <button
                            type="button"
                            @click="toggleScanner()"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition"
                            :class="scannerActive ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span x-text="scannerActive ? 'Detener Escáner' : 'Activar Escáner'"></span>
                        </button>
                    </div>

                    <!-- Scanner Container -->
                    <div x-show="scannerActive" x-cloak class="mb-6">
                        <div id="scanner-container" class="relative overflow-hidden border-4 border-blue-500 rounded-lg transition-all" style="height: 400px;">
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
                                <input
                                    type="text"
                                    id="dni"
                                    name="dni"
                                    x-model="dniValue"
                                    maxlength="8"
                                    pattern="\d{8}"
                                    required
                                    autofocus
                                    class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('dni') border-red-500 @enderror"
                                    placeholder="12345678"
                                >
                                @error('dni')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">O usa el escáner para capturar automáticamente</p>
                            </div>

                            <button
                                type="submit"
                                class="w-full px-4 py-3 text-base font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition"
                            >
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Registrar Asistencia
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-12 text-center bg-white rounded-lg shadow">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-900">No hay reuniones abiertas</p>
                    <p class="mt-1 text-sm text-gray-600">Selecciona o abre una reunión para comenzar a registrar asistencias</p>
                    <a href="{{ route('meetings.index') }}" class="inline-block px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
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

                    @if($selectedMeeting && $recentAttendances->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentAttendances as $attendance)
                                <div class="flex items-start p-3 rounded-lg bg-gray-50">
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-white bg-green-600 rounded-full">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $attendance->participant->first_name }} {{ $attendance->participant->last_name }}</p>
                                        <p class="text-xs text-gray-600">DNI: {{ $attendance->participant->dni }}</p>
                                        <p class="text-xs text-gray-500">{{ $attendance->registered_at->timezone('America/Lima')->format('h:i A') }}</p>
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
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">Sin asistencias aún</p>
                        </div>
                    @endif
                </div>

                <!-- Info Card -->
                <div class="p-4 mt-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

<!-- Alpine.js Scanner Component - DEBE IR ANTES DE ALPINE.JS -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendanceScanner', () => ({
            scannerActive: false,
            dniValue: '',
            scanning: false,
            lastScan: '',
            scanCount: 0,
            detectedCodes: [],
            stream: null,

            init() {
                this.$watch('scannerActive', value => {
                    if (!value) {
                        this.stopScanner();
                    }
                });

                window.addEventListener('beforeunload', () => {
                    if (this.scannerActive) {
                        this.stopScanner();
                    }
                });
            },

            toggleScanner() {
                this.scannerActive = !this.scannerActive;
                if (this.scannerActive) {
                    this.$nextTick(() => {
                        this.initScanner();
                    });
                } else {
                    this.stopScanner();
                }
            },

            initScanner() {
                console.log('Inicializando escáner...');

                // Verificar si estamos en HTTPS o localhost
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Conexión no segura',
                        html: 'La cámara solo funciona en:<br>• HTTPS (conexión segura)<br>• localhost<br><br>URL actual: ' + location.protocol + '//' + location.hostname,
                        confirmButtonText: 'Entendido'
                    });
                    this.scannerActive = false;
                    return;
                }

                if (typeof Quagga === 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El lector de códigos de barras no está disponible. Recarga la página e intenta nuevamente.'
                    });
                    this.scannerActive = false;
                    return;
                }

                const container = document.querySelector('#scanner-container');
                if (!container) {
                    console.error('Contenedor del escáner no encontrado');
                    this.scannerActive = false;
                    return;
                }

                container.innerHTML = '<div class="absolute inset-0 flex items-center justify-center bg-gray-900"><p class="text-white">Iniciando cámara...</p></div>';

                // Solicitar permisos de cámara primero
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                })
                .then(stream => {
                    console.log('Permisos de cámara concedidos');
                    // Detener el stream temporal
                    stream.getTracks().forEach(track => track.stop());

                    // Ahora iniciar Quagga
                    this.startQuagga(container);
                })
                .catch(err => {
                    console.error('Error al solicitar permisos de cámara:', err);
                    let errorMsg = 'No se pudo acceder a la cámara.';

                    if (err.name === 'NotAllowedError') {
                        errorMsg = 'Permisos de cámara denegados. Por favor, permite el acceso a la cámara en la configuración de tu navegador.';
                    } else if (err.name === 'NotFoundError') {
                        errorMsg = 'No se encontró ninguna cámara en tu dispositivo.';
                    } else if (err.name === 'NotReadableError') {
                        errorMsg = 'La cámara está siendo usada por otra aplicación.';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de cámara',
                        html: errorMsg + '<br><br>Error: ' + err.message,
                        confirmButtonText: 'Entendido'
                    });

                    this.scannerActive = false;
                    container.innerHTML = '';
                });
            },

            startQuagga(container) {

                Quagga.init({
                    inputStream: {
                        name: 'Live',
                        type: 'LiveStream',
                        target: container,
                        constraints: {
                            width: { min: 640, ideal: 1280, max: 1920 },
                            height: { min: 480, ideal: 720, max: 1080 },
                            facingMode: 'environment',
                            aspectRatio: { min: 1, max: 2 }
                        },
                        area: {
                            top: '25%',
                            right: '15%',
                            left: '15%',
                            bottom: '25%'
                        }
                    },
                    locator: {
                        patchSize: 'medium',
                        halfSample: true
                    },
                    numOfWorkers: navigator.hardwareConcurrency || 4,
                    frequency: 10,
                    decoder: {
                        readers: [
                            'code_39_reader',
                            'code_128_reader',
                            'ean_reader',
                            'ean_8_reader',
                            'codabar_reader'
                        ],
                        debug: {
                            drawBoundingBox: true,
                            showFrequency: false,
                            drawScanline: true,
                            showPattern: false
                        }
                    },
                    locate: true
                }, (err) => {
                    if (err) {
                        console.error('Error inicializando Quagga:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al iniciar escáner',
                            html: err.message + '<br><br>Asegúrate de:<br>• Permitir acceso a la cámara<br>• Usar HTTPS o localhost<br>• Tener buena iluminación',
                            confirmButtonText: 'Entendido'
                        });
                        this.scannerActive = false;
                        return;
                    }

                    console.log('Quagga iniciado correctamente');
                    Quagga.start();

                    this.stream = Quagga.CameraAccess.getActiveStreamLabel();

                    Swal.fire({
                        icon: 'success',
                        title: 'Escáner activo',
                        text: 'Coloca el código de barras del DNI frente a la cámara',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

                Quagga.onProcessed((result) => {
                    const drawingCtx = Quagga.canvas.ctx.overlay;
                    const drawingCanvas = Quagga.canvas.dom.overlay;

                    if (result) {
                        drawingCtx.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);

                        if (result.boxes) {
                            result.boxes.filter(box => box !== result.box).forEach(box => {
                                Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {
                                    color: 'rgba(0, 255, 0, 0.5)',
                                    lineWidth: 2
                                });
                            });
                        }

                        if (result.box) {
                            Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {
                                color: '#00F',
                                lineWidth: 3
                            });
                        }

                        if (result.codeResult && result.codeResult.code) {
                            Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {
                                color: 'red',
                                lineWidth: 3
                            });
                        }
                    }
                });

                Quagga.onDetected((result) => {
                    if (this.scanning) return;

                    const code = result.codeResult.code;
                    console.log('Código detectado:', code, 'Formato:', result.codeResult.format);

                    const cleanCode = code.replace(/[^0-9]/g, '');

                    if (cleanCode.length === 7 || cleanCode.length === 8) {
                        this.detectedCodes.push(cleanCode);
                        this.scanCount++;

                        console.log('Código válido agregado:', cleanCode, 'Total detectados:', this.detectedCodes.length);

                        if (this.detectedCodes.length >= 2) {
                            const lastTwo = this.detectedCodes.slice(-2);
                            if (lastTwo[0] === lastTwo[1]) {
                                this.processCode(lastTwo[0]);
                            }
                        }
                    } else {
                        console.log('Código ignorado (longitud incorrecta):', cleanCode);
                    }

                    if (this.detectedCodes.length > 10) {
                        this.detectedCodes = this.detectedCodes.slice(-5);
                    }
                });
            },

            processCode(code) {
                if (this.scanning) return;

                this.scanning = true;
                this.dniValue = code.length === 7 ? '0' + code : code;
                this.lastScan = this.dniValue;

                console.log('DNI procesado:', this.dniValue);

                const container = document.querySelector('#scanner-container');
                if (container) {
                    container.style.borderColor = '#10b981';
                    container.style.borderWidth = '6px';
                }

                this.playBeep();

                Swal.fire({
                    icon: 'success',
                    title: 'DNI Escaneado',
                    html: '<strong style="font-size: 24px;">DNI: ' + this.dniValue + '</strong><br><br>Registrando asistencia...',
                    timer: 2000,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                this.stopScanner();

                setTimeout(() => {
                    document.getElementById('attendance-form').submit();
                }, 2000);
            },

            playBeep() {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.5);
                } catch (error) {
                    console.log('No se pudo reproducir el sonido:', error);
                }
            },

            stopScanner() {
                console.log('Deteniendo escáner...');

                try {
                    if (typeof Quagga !== 'undefined') {
                        Quagga.stop();
                        Quagga.offDetected();
                        Quagga.offProcessed();
                    }
                } catch (error) {
                    console.error('Error deteniendo Quagga:', error);
                }

                this.scannerActive = false;
                this.scanning = false;
                this.detectedCodes = [];
                this.scanCount = 0;

                const container = document.querySelector('#scanner-container');
                if (container) {
                    container.style.borderColor = '#3b82f6';
                    container.style.borderWidth = '4px';
                    container.innerHTML = '';
                }
            }
        }));
    });
</script>

@push('scripts')
<script>
    // Mostrar alertas de sesión
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc2626'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            html: '@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
            confirmButtonColor: '#dc2626'
        });
    @endif
</script>
@endpush
@endsection

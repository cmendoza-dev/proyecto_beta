@extends('layouts.app')

@section('title', 'Documentos')

@push('head')
<!-- EmailJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
<script>
    emailjs.init('mpe9oN39k_jL28HiD');
</script>
@endpush

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Documentos de Reuniones</h1>
        <p class="mt-2 text-sm text-gray-600">Visualiza y descarga los archivos asociados a cada reunión</p>

        <!-- Filtros -->
        <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Título</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por título"
                           class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de reunión</label>
                    <select name="type_meeting" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Todas</option>
                        <option value="virtual" {{ request('type_meeting') === 'virtual' ? 'selected' : '' }}>Virtual</option>
                        <option value="presencial" {{ request('type_meeting') === 'presencial' ? 'selected' : '' }}>Presencial</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-3 flex items-center gap-2 justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Aplicar filtros
                    </button>
                    <a href="{{ url()->current() }}"

                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-200">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        @forelse($meetings as $meeting)
            <div class="p-6 border-b border-gray-200 last:border-b-0">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $meeting->title }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }} -
                            {{ $meeting->location ?? 'Sin ubicación' }}
                        </p>
                        @if($meeting->description)
                            <p class="mt-1 text-sm text-gray-600">{{ Str::limit($meeting->description, 100) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 text-xs font-medium rounded-full
                            {{ $meeting->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $meeting->status === 'open' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $meeting->status === 'closed' ? 'bg-green-100 text-green-800' : '' }}">
                              {{ $meeting->status_label }}
                        </span>
                    </div>
                </div>

                @if($meeting->documents->count() > 0)
                    <!-- Botón de Envío Masivo -->
                    @if($meeting->participants->count() > 0 && (auth()->user()->role === 'Secretary' || auth()->user()->role === 'Administrator'))
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm text-blue-800">
                                    <strong>{{ $meeting->participants->count() }}</strong> participante(s) registrado(s)
                                </span>
                            </div>
                            <button onclick="prepararEnvioMasivo({{ $meeting->id }})"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Enviar Documentos por Email
                            </button>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($meeting->documents as $document)
                            @php
                                $filename = $document->original_name;
                                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                $iconColor = match($extension) {
                                    'pdf' => 'text-red-600',
                                    'doc', 'docx' => 'text-blue-600',
                                    'xls', 'xlsx' => 'text-green-600',
                                    'jpg', 'jpeg', 'png', 'gif' => 'text-purple-600',
                                    'zip', 'rar' => 'text-yellow-600',
                                    default => 'text-gray-600'
                                };
                            @endphp
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <svg class="w-8 h-8 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate" title="{{ $filename }}">
                                        {{ Str::limit($filename, 25) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ strtoupper($extension) }} • {{ number_format($document->file_size / 1024, 2) }} KB
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Por {{ $document->uploader->name }}
                                    </p>
                                </div>
                                <div class="flex items-center ml-3 space-x-2">
                                    <!-- Botón de descarga -->
                                    <a href="{{ route('documents.download', ['meeting' => $meeting->id, 'filename' => basename($document->file_path)]) }}"
                                       class="text-green-600 hover:text-green-800"
                                       title="Descargar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>

                                    @if(auth()->user()->role === 'Secretary' || auth()->user()->role === 'Administrator')
                                        <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800"
                                                    onclick="return confirm('¿Eliminar este documento?')"
                                                    title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        Total de documentos: {{ $meeting->documents->count() }}
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">No hay documentos adjuntos para esta reunión</p>
                @endif
            </div>
        @empty
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No hay reuniones con documentos</h3>
                <p class="mt-2 text-sm text-gray-500">Los documentos aparecerán aquí cuando se suban archivos a las reuniones</p>
                <a href="{{ route('meetings.index') }}" class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ir a Reuniones
                </a>
            </div>
        @endforelse
    </div>

    @if($meetings->hasPages())
        <div class="mt-6">
            {{ $meetings->appends(request()->query())->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
// ============================================
// CONFIGURACIÓN DE EMAILJS
// ============================================
const EMAILJS_CONFIG = {
    serviceId: 'service_39ah582',      // Cambiar por tu Service ID
    templateId: 'template_u6cp1np',  // Cambiar por tu Template ID
    publicKey: 'mpe9oN39k_jL28HiD'       // Ya inicializado en el head
};

// ============================================
// FUNCIÓN PRINCIPAL PARA ENVÍO MASIVO
// ============================================
async function enviarDocumentosMasivo(meetingId, participantes, documentos) {
    const resultados = {
        exitosos: [],
        fallidos: []
    };

    mostrarLoading(true);

    for (const participante of participantes) {
        try {
            await enviarEmailParticipante(meetingId, participante, documentos);
            resultados.exitosos.push(participante.email);
            await delay(500); // Pausa para evitar rate limiting
        } catch (error) {
            console.error(`Error enviando a ${participante.email}:`, error);
            resultados.fallidos.push({
                email: participante.email,
                error: error.message
            });
        }
    }

    // Registrar resultados en el servidor
    await registrarEnvios(meetingId, resultados);

    mostrarLoading(false);
    mostrarResultados(resultados);

    return resultados;
}

// ============================================
// ENVÍO INDIVIDUAL CON EMAILJS
// ============================================
async function enviarEmailParticipante(meetingId, participante, documentos) {
    const templateParams = {
        to_email: participante.email,
        to_name: participante.name,
        meeting_title: documentos.meeting.title,
        meeting_date: formatearFecha(documentos.meeting.date),
        meeting_location: documentos.meeting.location || 'Sin ubicación',
        meeting_description: documentos.meeting.description || '',
        documents_list: generarListaDocumentos(documentos.files),
        documents_count: documentos.files.length,
        download_links: generarEnlacesDescarga(meetingId, documentos.files),
        sent_by: documentos.sender.name,
        sent_date: new Date().toLocaleDateString('es-ES'),
        system_url: window.location.origin,
        meeting_url: `${window.location.origin}/meetings/${meetingId}`
    };

    return emailjs.send(
        EMAILJS_CONFIG.serviceId,
        EMAILJS_CONFIG.templateId,
        templateParams
    );
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
function generarListaDocumentos(files) {
    return files.map((doc, index) => {
        const extension = doc.original_name.split('.').pop().toUpperCase();
        const size = (doc.file_size / 1024).toFixed(2);
        return `${index + 1}. ${doc.original_name} (${extension} - ${size} KB)`;
    }).join('\n');
}

function generarEnlacesDescarga(meetingId, files) {
    const baseUrl = window.location.origin;
    return files.map((doc, index) => {
        const filename = doc.file_path.split('/').pop();
        const url = `${baseUrl}/documents/download/${meetingId}/${filename}`;
        return `${index + 1}. ${doc.original_name}:\n   ${url}`;
    }).join('\n\n');
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// ============================================
// UI - MODAL Y FEEDBACK
// ============================================
function mostrarLoading(show) {
    const existingModal = document.getElementById('email-loading-modal');

    if (show) {
        const modal = `
            <div id="email-loading-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-8 max-w-md">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
                        <h3 class="mt-4 text-lg font-semibold">Enviando correos...</h3>
                        <p class="mt-2 text-sm text-gray-600">Por favor espera, esto puede tomar unos momentos</p>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modal);
    } else if (existingModal) {
        existingModal.remove();
    }
}

function mostrarResultados(resultados) {
    const total = resultados.exitosos.length + resultados.fallidos.length;
    const exitosos = resultados.exitosos.length;

    let mensaje = `
        <div class="bg-white rounded-lg p-6 max-w-2xl max-h-96 overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">Resultado del Envío</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                    <span class="text-green-800 font-medium">Enviados correctamente</span>
                    <span class="text-2xl font-bold text-green-600">${exitosos}/${total}</span>
                </div>
    `;

    if (resultados.fallidos.length > 0) {
        mensaje += `
            <div class="p-4 bg-red-50 rounded-lg">
                <h4 class="font-semibold text-red-800 mb-2">Fallidos (${resultados.fallidos.length}):</h4>
                <ul class="text-sm text-red-700 space-y-1">
                    ${resultados.fallidos.map(f => `<li>• ${f.email}: ${f.error}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    mensaje += `
            </div>
            <button onclick="cerrarModalResultados()" class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Cerrar
            </button>
        </div>
    `;

    const modal = `
        <div id="resultados-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            ${mensaje}
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modal);
}

function cerrarModalResultados() {
    document.getElementById('resultados-modal')?.remove();
}

// ============================================
// PREPARAR Y EJECUTAR ENVÍO
// ============================================
async function prepararEnvioMasivo(meetingId) {
    try {
        await ensureEmailJS();
        const response = await fetch(`/meetings/${meetingId}/email-data`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Error al obtener datos del servidor');
        }

        const data = await response.json();

        if (!data.participantes || data.participantes.length === 0) {
            alert('No hay participantes registrados para enviar correos');
            return;
        }

        if (!data.documentos || data.documentos.length === 0) {
            alert('No hay documentos para enviar');
            return;
        }

        const confirmar = confirm(
            `¿Confirmas el envío de ${data.documentos.length} documento(s) a ${data.participantes.length} participante(s)?`
        );

        if (confirmar) {
            await enviarDocumentosMasivo(meetingId, data.participantes, {
                meeting: data.meeting,
                files: data.documentos,
                sender: data.sender
            });
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al preparar el envío: ' + error.message);
    }
}

// ============================================
// REGISTRAR ENVÍOS EN EL SERVIDOR
// ============================================
async function registrarEnvios(meetingId, resultados) {
    try {
        const recipients = [
            ...resultados.exitosos.map(email => ({ email, status: 'success' })),
            ...resultados.fallidos.map(f => ({ email: f.email, status: 'failed', error: f.error }))
        ];

        await fetch(`/api/meetings/${meetingId}/email-log`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ recipients })
        });
    } catch (error) {
        console.error('Error registrando envíos:', error);
    }
}

async function ensureEmailJS() {
    if (window.emailjs) {
        if (!window.__emailjsInitialized) {
            emailjs.init(EMAILJS_CONFIG.publicKey);
            window.__emailjsInitialized = true;
        }
        return;
    }
    await new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js';
        s.onload = () => {
            try {
                emailjs.init(EMAILJS_CONFIG.publicKey);
                window.__emailjsInitialized = true;
                resolve();
            } catch (err) {
                reject(err);
            }
        };
        s.onerror = () => reject(new Error('No se pudo cargar EmailJS'));
        document.head.appendChild(s);
    });
}
</script>
@endpush

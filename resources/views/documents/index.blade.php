@extends('layouts.app')

@section('title', 'Documentos')

@section('content')
    <div class="space-y-6">
        <div class="">
            <h1 class="text-3xl font-bold text-gray-900">Documentos de Reuniones</h1>
            <p class="mt-1 text-sm text-gray-600">Visualiza y descarga los archivos asociados a cada reunión</p>
        </div>

        <!-- Filtros -->
        <div class="p-6 bg-white rounded-lg shadow">
            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Título</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Título de reunión..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Filtrar
                    </button>
                    <a href="{{ url()->current() }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow-sm">
        @forelse($meetings as $meeting)
            <div class="p-6 border-b border-gray-200 last:border-b-0">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $meeting->title }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }} -
                            {{ $meeting->location ?? 'Sin ubicación' }}
                        </p>
                        @if ($meeting->description)
                            <p class="mt-1 text-sm text-gray-600">{{ Str::limit($meeting->description, 100) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-3 py-1 text-xs font-medium rounded-full
                            {{ $meeting->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $meeting->status === 'open' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $meeting->status === 'closed' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ $meeting->status_label }}
                        </span>
                    </div>
                </div>

                @if ($meeting->documents->count() > 0)
                    <!-- Botón de Envío Masivo -->
                    <!-- Añadir el botón de compartir por WhatsApp -->
                    @if ($meeting->participants->count() > 0)
                        <div
                            class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-blue-800">
                                    <strong>{{ $meeting->participants->count() }}</strong> participante(s) registrado(s)
                                </span>
                            </div>
                            <div class="">
                                <button onclick="prepararEnvioMasivo({{ $meeting->id }})"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Correo electrónico
                                </button>
                                <button onclick="compartirPorWhatsApp({{ $meeting->id }})"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                    WhatsApp
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($meeting->documents as $document)
                            @php
                                $filename = $document->original_name;
                                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                $iconColor = match ($extension) {
                                    'pdf' => 'text-red-600',
                                    'doc', 'docx' => 'text-blue-600',
                                    'xls', 'xlsx' => 'text-green-600',
                                    'jpg', 'jpeg', 'png', 'gif' => 'text-purple-600',
                                    'zip', 'rar' => 'text-yellow-600',
                                    default => 'text-gray-600',
                                };
                            @endphp
                            <div
                                class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <svg class="w-8 h-8 {{ $iconColor }}" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate" title="{{ $filename }}">
                                        {{ Str::limit($filename, 25) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ strtoupper($extension) }} • {{ number_format($document->file_size / 1024, 2) }}
                                        KB
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Por {{ $document->uploader->name }}
                                    </p>
                                </div>
                                <div class="flex items-center ml-3 space-x-2">
                                    <!-- Botón de descarga -->
                                    <a href="{{ route('documents.download', ['meeting' => $meeting->id, 'filename' => basename($document->file_path)]) }}"
                                        class="text-green-600 hover:text-green-800" title="Descargar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>

                                    @if (auth()->user()->role === 'Secretary' || auth()->user()->role === 'Administrator')
                                        <form action="{{ route('documents.destroy', $document) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800"
                                                onclick="return confirm('¿Eliminar este documento?')" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
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
                <p class="mt-2 text-sm text-gray-500">Los documentos aparecerán aquí cuando se suban archivos a las
                    reuniones</p>
                <a href="{{ route('meetings.index') }}"
                    class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ir a Reuniones
                </a>
            </div>
        @endforelse
    </div>

    @if ($meetings->hasPages())
        <div class="mt-6">
            {{ $meetings->appends(request()->query())->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script type="module">
        // Ya no necesitas todo el código aquí, solo se importa desde los módulos
        console.log('Servicios de documentos cargados');
    </script>

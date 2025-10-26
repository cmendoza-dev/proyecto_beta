@extends('layouts.app')

@section('title', 'Documentos de la Reunión')

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ auth()->user()->role === 'Administrator' ? route('admin.documents.index') : '#' }}"
                class="inline-flex items-center text-sm {{ auth()->user()->role === 'Administrator' ? 'text-gray-600 hover:text-gray-900' : 'text-gray-400 cursor-not-allowed pointer-events-none' }}"
                @if (auth()->user()->role !== 'Administrator') aria-disabled="true" title="Sin acceso" @endif>
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a Documentos
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $meeting->title }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }} -
                        {{ $meeting->location ?? 'Sin ubicación' }}
                    </p>
                    @if ($meeting->description)
                        <p class="mt-1 text-sm text-gray-600">{{ $meeting->description }}</p>
                    @endif
                </div>
                <span
                    class="px-3 py-1 text-xs font-medium rounded-full
                    {{ $meeting->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ $meeting->status === 'open' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $meeting->status === 'closed' ? 'bg-green-100 text-green-800' : '' }}">
                    {{ $meeting->status_label }}
                </span>
            </div>

            {{-- @if ($meeting->documents->count() > 0)
                <!-- Botón de Envío Masivo -->
                @if (
                    $meeting->participants->count() > 0 &&
                        (auth()->user()->role === 'Secretary' || auth()->user()->role === 'Administrator'))
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
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
                        <button onclick="prepararEnvioMasivo({{ $meeting->id }})"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Enviar Documentos por Email
                        </button>
                    </div>
                @endif --}}

                @if ($meeting->documents->count() > 0)
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($meeting->documents as $document)
                            @php
                                $filename = $document->original_name ?? $document->name;
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
                                        {{ \Illuminate\Support\Str::limit($filename, 28) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ strtoupper($extension) }} • {{ number_format($document->file_size / 1024, 2) }}
                                        KB
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Por {{ optional($document->uploader)->name ?? '—' }} •
                                        {{ $document->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="flex items-center ml-3 space-x-2">
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
    </div>
@endsection

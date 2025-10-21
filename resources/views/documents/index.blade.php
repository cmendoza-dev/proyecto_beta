@extends('layouts.app')

@section('title', 'Documentos')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Documentos de Reuniones</h1>
        <p class="mt-2 text-sm text-gray-600">Visualiza y descarga los archivos asociados a cada reunión</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        @forelse($meetings as $meeting)
            <div class="p-6 border-b border-gray-200 last:border-b-0">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $meeting->title }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d/m/Y') }} -
                            {{ $meeting->location }}
                        </p>
                        @if($meeting->description)
                            <p class="mt-1 text-sm text-gray-600">{{ Str::limit($meeting->description, 100) }}</p>
                        @endif
                    </div>
                    <span class="px-3 py-1 text-xs font-medium rounded-full
                        {{ $meeting->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $meeting->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $meeting->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($meeting->status) }}
                    </span>
                </div>

                @if($meeting->attachments && count($meeting->attachments) > 0)
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($meeting->attachments as $attachment)
                            @php
                                $filename = basename($attachment);
                                $extension = pathinfo($attachment, PATHINFO_EXTENSION);
                                $iconColor = match(strtolower($extension)) {
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
                                        {{ $filename }}
                                    </p>
                                    <p class="text-xs text-gray-500 uppercase">{{ $extension }}</p>
                                </div>
                                <div class="flex items-center ml-3 space-x-2">
                                    <!-- Botón de vista previa (usa el modal) -->
                                    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'pdf']))
                                        <button
                                            onclick="openPreview('{{ asset('storage/' . $attachment) }}', '{{ $filename }}', '{{ $extension }}')"
                                            class="text-blue-600 hover:text-blue-800"
                                            title="Ver">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    @endif
                                    <!-- Botón de descarga -->
                                    <a href="{{ route('documents.download', ['meeting' => $meeting->id, 'filename' => $filename]) }}"
                                       class="text-green-600 hover:text-green-800"
                                       title="Descargar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        Total de documentos: {{ count($meeting->attachments) }}
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

    <!-- Incluir el modal -->
    @include('documents.download')
@endsection

@section('scripts')
    <script>
        function openPreview(url, filename, extension) {
            const modal = document.getElementById('previewModal');
            const modalTitle = document.getElementById('previewModalLabel');
            const modalBody = document.getElementById('previewModalBody');
            const modalFooter = document.getElementById('previewModalFooter');

            modalTitle.innerText = filename;
            modalBody.innerHTML = '';

            if (['jpg', 'jpeg', 'png', 'gif'].includes(extension.toLowerCase())) {
                // Para imágenes, mostrar en un elemento <img>
                modalBody.innerHTML = `<img src="${url}" class="w-full h-auto" />`;
            } else if (extension.toLowerCase() === 'pdf') {
                // Para PDFs, usar PDF.js
                modalBody.innerHTML = `<iframe src="${url}" class="w-full h-96" frameborder="0"></iframe>`;
            }

            // Mostrar el modal
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        }
    </script>
@endsection

@extends('layouts.app')

@section('title', $meeting->title)

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('meetings.index') }}"
                    class="inline-flex items-center mb-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a reuniones
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $meeting->title }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $meeting->description }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if ($meeting->status === 'draft')
                    <form method="POST" action="{{ route('meetings.open', $meeting) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                            Abrir Reunión
                        </button>
                    </form>
                @endif

                @if ($meeting->status === 'open')
                    <a href="{{ route('attendance.register', ['meeting' => $meeting->id]) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Registrar Asistencia
                    </a>
                    <form method="POST" action="{{ route('meetings.close', $meeting) }}" x-data
                        @submit.prevent="if(confirm('¿Cerrar esta reunión? No se podrán registrar más asistencias.')) $el.submit()">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Cerrar Reunión
                        </button>
                    </form>
                @endif

                @if ($meeting->status === 'closed')
                    <a href="{{ route('meetings.report', $meeting) }}" targer="_blank"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Descargar Reporte PDF
                    </a>
                @endif

                @if ($meeting->status === 'closed')
                    <div x-data="{
                        open: false,
                        files: [],
                        uploading: false,
                        uploadProgress: 0,
                        dragActive: false,

                        addFiles(newFiles) {
                            Array.from(newFiles).forEach(file => {
                                if (this.validateFile(file)) {
                                    this.files.push({
                                        file: file,
                                        name: file.name,
                                        size: this.formatFileSize(file.size),
                                        type: file.type,
                                        uploaded: false,
                                        url: null,
                                        uploading: false,
                                        progress: 0,
                                        error: null
                                    });
                                }
                            });
                        },

                        validateFile(file) {
                            const maxSize = 10 * 1024 * 1024; // 10MB
                            const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg', 'image/png', 'image/gif', 'application/zip'
                            ];

                            if (file.size > maxSize) {
                                alert('El archivo ' + file.name + ' excede el tamaño máximo de 10MB');
                                return false;
                            }

                            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|docx|xlsx|jpg|jpeg|png|gif|zip)$/i)) {
                                alert('El archivo ' + file.name + ' no tiene un formato permitido');
                                return false;
                            }

                            return true;
                        },

                        removeFile(index) {
                            this.files.splice(index, 1);
                        },

                        async uploadFiles() {
                            this.uploading = true;

                            for (let i = 0; i < this.files.length; i++) {
                                if (!this.files[i].uploaded) {
                                    await this.uploadSingleFile(i);
                                }
                            }

                            this.uploading = false;
                        },

                        async uploadSingleFile(index) {
                            const fileData = this.files[index];
                            fileData.uploading = true;

                            const formData = new FormData();
                            formData.append('document', fileData.file);
                            {{-- {{ route(\'meetings.documents.upload\', $meeting) }} --}}
                            try {
                                const response = await fetch('', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (response.ok && data.file_url) {
                                    fileData.uploaded = true;
                                    fileData.url = data.file_url;
                                    fileData.uploading = false;
                                } else {
                                    fileData.error = 'Error al subir el archivo';
                                    fileData.uploading = false;
                                }
                            } catch (error) {
                                fileData.error = 'Error de conexión';
                                fileData.uploading = false;
                            }
                        },

                        formatFileSize(bytes) {
                            if (bytes === 0) return '0 Bytes';
                            const k = 1024;
                            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                            const i = Math.floor(Math.log(bytes) / Math.log(k));
                            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                        },

                        getFileIcon(type) {
                            if (type.includes('pdf')) return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
                            if (type.includes('word')) return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
                            if (type.includes('sheet') || type.includes('excel')) return 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z';
                            if (type.includes('image')) return 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z';
                            return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
                        },

                        resetModal() {
                            this.files = [];
                            this.uploading = false;
                            this.uploadProgress = 0;
                            this.dragActive = false;
                        }
                    }">
                        <!-- Botón para abrir modal -->
                        <button type="button" @click="open = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg hover:from-indigo-700 hover:to-indigo-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span>Subir Documentos</span>
                        </button>

                        <!-- Modal -->
                        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false; resetModal()" class="fixed inset-0 z-50 overflow-y-auto"
                            style="display: none;">

                            <!-- Overlay -->
                            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"
                                @click="open = false; resetModal()"></div>

                            <!-- Contenedor del modal -->
                            <div class="flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-8">
                                <div x-show="open" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all"
                                    @click.away="open = false; resetModal()">

                                    <!-- Header -->
                                    <div
                                        class="relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 px-6 sm:px-8 py-6">
                                        <div class="absolute inset-0 opacity-10">
                                            <div class="absolute -top-20 -right-20 w-40 h-40 bg-white rounded-full"></div>
                                            <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-white rounded-full"></div>
                                        </div>

                                        <div class="relative flex items-start justify-between gap-4">
                                            <div class="flex items-center gap-3 sm:gap-4">
                                                <div
                                                    class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <h3 class="text-xl sm:text-2xl font-bold text-white truncate">
                                                        Documentos de Acuerdos</h3>
                                                    <p class="text-sm text-indigo-100 mt-1 truncate">{{ $meeting->title }}
                                                    </p>
                                                </div>
                                            </div>
                                            <button @click="open = false; resetModal()"
                                                class="flex-shrink-0 p-2 text-white hover:bg-white/20 rounded-lg transition-colors">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Body -->
                                    <div class="p-6 sm:p-8 max-h-[calc(100vh-300px)] overflow-y-auto">

                                        <!-- Área de carga (solo si no hay archivos subidos) -->
                                        <div x-show="files.length === 0">
                                            <div class="mb-6">
                                                <!-- Zona de drag & drop -->
                                                <div @dragover.prevent="dragActive = true"
                                                    @dragleave.prevent="dragActive = false"
                                                    @drop.prevent="dragActive = false; addFiles($event.dataTransfer.files)"
                                                    :class="dragActive ? 'border-indigo-500 bg-indigo-50' :
                                                        'border-gray-300 bg-gray-50'"
                                                    class="relative border-2 border-dashed rounded-xl p-8 sm:p-12 text-center transition-all duration-200 hover:border-indigo-400 hover:bg-indigo-50/50 cursor-pointer">

                                                    <input type="file" id="fileInput" multiple
                                                        @change="addFiles($event.target.files)" class="hidden"
                                                        accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png,.gif,.zip">

                                                    <label for="fileInput" class="cursor-pointer">
                                                        <div
                                                            class="mx-auto w-16 h-16 sm:w-20 sm:h-20 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-indigo-600"
                                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                            </svg>
                                                        </div>
                                                        <h4 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">
                                                            Arrastra tus archivos aquí
                                                        </h4>
                                                        <p class="text-sm text-gray-600 mb-4">
                                                            o haz clic para seleccionar
                                                        </p>
                                                        <div
                                                            class="inline-flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                            </svg>
                                                            <span>Seleccionar Archivos</span>
                                                        </div>
                                                    </label>
                                                </div>

                                                <!-- Información -->
                                                <div
                                                    class="mt-4 flex items-start gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div class="text-xs sm:text-sm text-blue-800">
                                                        <p class="font-semibold mb-1">Formatos: PDF, DOCX, XLSX, JPG, PNG,
                                                            GIF, ZIP</p>
                                                        <p>Tamaño máximo: 10MB por archivo</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lista de archivos -->
                                        <div x-show="files.length > 0" class="space-y-4">
                                            <div class="flex items-center justify-between mb-4">
                                                <h4 class="text-base sm:text-lg font-semibold text-gray-900">
                                                    Archivos (<span x-text="files.length"></span>)
                                                </h4>
                                                <button type="button"
                                                    @click="document.getElementById('fileInput').click()"
                                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                                    + Agregar más
                                                </button>
                                            </div>

                                            <!-- Archivos individuales -->
                                            <template x-for="(fileData, index) in files" :key="index">
                                                <div class="border-2 rounded-xl p-4 transition-all"
                                                    :class="fileData.uploaded ? 'border-green-200 bg-green-50' : fileData
                                                        .error ? 'border-red-200 bg-red-50' :
                                                        'border-gray-200 bg-white hover:border-gray-300'">

                                                    <div class="flex items-start gap-3 sm:gap-4">
                                                        <!-- Icono del archivo -->
                                                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center"
                                                            :class="fileData.uploaded ? 'bg-green-100' : fileData.error ?
                                                                'bg-red-100' : 'bg-gray-100'">
                                                            <svg class="w-6 h-6"
                                                                :class="fileData.uploaded ? 'text-green-600' : fileData.error ?
                                                                    'text-red-600' : 'text-gray-600'"
                                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" :d="getFileIcon(fileData.type)" />
                                                            </svg>
                                                        </div>

                                                        <!-- Info del archivo -->
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="text-sm font-medium text-gray-900 truncate"
                                                                        x-text="fileData.name"></p>
                                                                    <p class="text-xs text-gray-500 mt-0.5"
                                                                        x-text="fileData.size"></p>
                                                                </div>

                                                                <!-- Botón eliminar -->
                                                                <button type="button" @click="removeFile(index)"
                                                                    x-show="!fileData.uploading"
                                                                    class="flex-shrink-0 p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                                    <svg class="w-5 h-5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>

                                                            <!-- Estados -->
                                                            <div class="mt-2">
                                                                <!-- Subiendo -->
                                                                <div x-show="fileData.uploading"
                                                                    class="flex items-center gap-2 text-xs text-indigo-600">
                                                                    <svg class="animate-spin w-4 h-4" fill="none"
                                                                        viewBox="0 0 24 24">
                                                                        <circle class="opacity-25" cx="12"
                                                                            cy="12" r="10" stroke="currentColor"
                                                                            stroke-width="4"></circle>
                                                                        <path class="opacity-75" fill="currentColor"
                                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                        </path>
                                                                    </svg>
                                                                    <span>Subiendo...</span>
                                                                </div>

                                                                <!-- Subido -->
                                                                <div x-show="fileData.uploaded"
                                                                    class="flex items-center gap-2">
                                                                    <div
                                                                        class="flex items-center gap-1.5 text-xs text-green-700 font-medium">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                        <span>Subido correctamente</span>
                                                                    </div>
                                                                    <a :href="fileData.url" target="_blank"
                                                                        class="ml-auto text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                                                        Ver →
                                                                    </a>
                                                                </div>

                                                                <!-- Error -->
                                                                <div x-show="fileData.error"
                                                                    class="flex items-center gap-1.5 text-xs text-red-700">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                    <span x-text="fileData.error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Botón subir todos -->
                                            <div x-show="files.some(f => !f.uploaded && !f.uploading)">
                                                <button type="button" @click="uploadFiles()" :disabled="uploading"
                                                    class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                                                    <span x-show="!uploading">Subir Archivos</span>
                                                    <span x-show="uploading"
                                                        class="flex items-center justify-center gap-2">
                                                        <svg class="animate-spin w-5 h-5" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Subiendo...
                                                    </span>
                                                </button>
                                            </div>

                                            <!-- Sección para compartir (solo si hay archivos subidos) -->
                                            <div x-show="files.some(f => f.uploaded)"
                                                class="mt-6 pt-6 border-t-2 border-gray-200">
                                                <div
                                                    class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 sm:p-6 border border-gray-200">
                                                    <div class="flex items-center gap-2 mb-4">
                                                        <svg class="w-5 h-5 text-gray-700" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                        </svg>
                                                        <h5 class="text-base font-bold text-gray-900">Compartir documentos
                                                        </h5>
                                                    </div>

                                                    {{-- <!-- Email Form --> --}}
                                                    {{-- {{ route('meetings.documents.share.email', $meeting) }} --}}
                                                    <form method="POST" action="" class="space-y-3 mb-4">
                                                        @csrf
                                                        <template
                                                            x-for="(fileData, index) in files.filter(f => f.uploaded)"
                                                            :key="index">
                                                            <input type="hidden" name="file_urls[]"
                                                                :value="fileData.url">
                                                        </template>

                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-2">
                                                                Correos electrónicos (separados por coma)
                                                            </label>
                                                            <div class="relative">
                                                                <div
                                                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                                    <svg class="w-5 h-5 text-gray-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                                    </svg>
                                                                </div>
                                                                <input type="text" name="emails"
                                                                    placeholder="correo1@ejemplo.com, correo2@ejemplo.com"
                                                                    class="w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <button type="submit"
                                                            class="w-full flex items-center justify-center gap-2 px-5 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                            </svg>
                                                            <span>Enviar por Email</span>
                                                        </button>
                                                    </form>

                                                    <!-- Divisor -->
                                                    <div class="relative my-4">
                                                        <div class="absolute inset-0 flex items-center">
                                                            <div class="w-full border-t border-gray-300"></div>
                                                        </div>
                                                        <div class="relative flex justify-center text-xs">
                                                            <span class="px-3 bg-gray-100 text-gray-500">O</span>
                                                        </div>
                                                    </div>

                                                    <!-- WhatsApp -->
                                                    <a :href="'https://wa.me/?text=' + encodeURIComponent(
                                                        '📄 Documentos de la reunión ' + '{{ $meeting->title }}' +
                                                        ':\\n\\n' + files.filter(f => f.uploaded).map((f, i) => (i +
                                                            1) + '. ' + f.name + ': ' + f.url).join('\\n\\n'))"
                                                        target="_blank"
                                                        class="w-full flex items-center justify-center gap-2 px-5 py-2.5 sm:py-3 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm font-semibold rounded-lg hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                                            <path
                                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                                        </svg>
                                                        <span>Compartir por WhatsApp</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="px-6 sm:px-8 py-4 border-t border-gray-200 bg-gray-50">
                                        <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                                            <button type="button" @click="open = false; resetModal()"
                                                class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-100 transition-all">
                                                Cerrar
                                            </button>
                                            <button type="button" x-show="files.length > 0" @click="resetModal()"
                                                class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-indigo-700 bg-indigo-50 border-2 border-indigo-200 rounded-lg hover:bg-indigo-100 transition-all">
                                                Limpiar todo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input file oculto para agregar más archivos -->
                        <input type="file" id="fileInput" multiple
                            @change="addFiles($event.target.files); $event.target.value = ''" class="hidden"
                            accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png,.gif,.zip">
                    </div>
                @else
                    <a href="{{ route('meetings.edit', $meeting) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                    </a>
                @endif
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Estado</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ ucfirst($meeting->status) }}</p>
                    </div>
                    <div
                        class="p-3 rounded-full
                    @if ($meeting->status === 'open') bg-green-100
                    @elseif($meeting->status === 'closed') bg-gray-100
                    @else bg-yellow-100 @endif">
                        <svg class="w-8 h-8
                        @if ($meeting->status === 'open') text-green-600
                        @elseif($meeting->status === 'closed') text-gray-600
                        @else text-yellow-600 @endif"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Fecha</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $meeting->date->format('d/m/Y') }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Horario</p>
                        <p class="mt-2 text-lg font-bold text-gray-900">
                            {{ \Carbon\Carbon::parse($meeting->opening_time)->format('h:i A') }}
                        </p>
                        <p class="text-sm text-gray-500">
                            a {{ \Carbon\Carbon::parse($meeting->closing_time)->format('h:i A') }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Asistentes</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $attendances->count() }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendances List -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Lista de Asistencia</h3>
            </div>

            @if ($attendances->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">DNI
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Participante</th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Hora
                                de Ingreso</th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Método</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($attendances as $index => $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    {{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                    {{ $attendance->participant->dni }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $attendance->participant->first_name }}
                                        {{ $attendance->participant->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                    {{ $attendance->registered_at->timezone('America/Lima')->format('h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                    @if ($attendance->registration_method === 'barcode') bg-blue-100 text-blue-800
                                    @elseif($attendance->registration_method === 'api') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($attendance->registration_method) ? 'Escaneo de Barras' : 'Registro Manual' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Aún no hay asistencias registradas</p>
                    @if ($meeting->status === 'open')
                        <a href="{{ route('attendance.register', ['meeting' => $meeting->id]) }}"
                            class="inline-block px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Registrar primera asistencia
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

@endsection

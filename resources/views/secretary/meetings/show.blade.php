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
                    <a href="{{ route('documents.meeting', $meeting) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                       Visualizar Archivos
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
                            try {
                                const response = await fetch('{{ route('meetings.documents.upload', $meeting) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (response.ok && data.success) {
                                    fileData.uploaded = true;
                                    fileData.url = data.file_url;
                                    fileData.uploading = false;
                                } else {
                                    fileData.error = data.message || 'Error al subir el archivo';
                                    fileData.uploading = false;
                                }
                            } catch (error) {
                                console.error('Error:', error);
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
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $meeting->status_label }}</p>
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
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $attendances->total() }}</p>
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
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Lista de Asistencia</h3>
                @if ($meeting->status === 'closed')
                    <a href="{{ route('meetings.report', $meeting) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Descargar Reporte PDF
                    </a>
                @endif
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

                @if ($attendances->hasPages())
                    <div class="px-6 py-4 bg-gray-50">
                        {{ $attendances->links() }}
                    </div>
                @endif
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

        @if ($meeting->is_closed)
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Documentos de la Reunión</h5>
                </div>
                <div class="card-body">
                    @if (auth()->user()->role === 'Secretary' || auth()->user()->role === 'Administrator')
                        <form action="{{ route('documents.store', $meeting) }}" method="POST"
                            enctype="multipart/form-data" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Subir Documentos (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG)</label>
                                <input type="file" name="documents[]" class="form-control" multiple
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Puedes seleccionar múltiples archivos. Máximo 10MB por
                                    archivo.</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir Documentos</button>
                        </form>
                    @endif

                    @if ($meeting->documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tamaño</th>
                                        <th>Subido por</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($meeting->documents as $document)
                                        <tr>
                                            <td>{{ $document->original_name }}</td>
                                            <td>{{ number_format($document->file_size / 1024, 2) }} KB</td>
                                            <td>{{ $document->uploader->name }}</td>
                                            <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('documents.download', [$meeting, basename($document->file_path)]) }}"
                                                    class="btn btn-sm btn-success">
                                                    Descargar
                                                </a>
                                                @if (auth()->user()->role === 'Secretary' || auth()->user()->role === 'Administrator')
                                                    <form action="{{ route('documents.destroy', $document) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('¿Eliminar este documento?')">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay documentos subidos.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endsection

<!-- filepath: c:\xampp\htdocs\proyecto_beta\resources\views\documents\download.blade.php -->
<div id="previewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closePreview()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="previewFilename"></h3>
                    <button onclick="closePreview()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div id="previewContent" class="flex items-center justify-center min-h-[400px] bg-gray-50 rounded">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Cargando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPreview(url, filename, extension) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    const filenameEl = document.getElementById('previewFilename');

    filenameEl.textContent = filename;
    content.innerHTML = '<div class="text-center"><svg class="w-12 h-12 mx-auto text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="mt-2 text-sm text-gray-500">Cargando...</p></div>';

    modal.classList.remove('hidden');

    setTimeout(() => {
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension.toLowerCase())) {
            content.innerHTML = `<img src="${url}" alt="${filename}" class="max-w-full h-auto rounded shadow-lg">`;
        } else if (extension.toLowerCase() === 'pdf') {
            content.innerHTML = `<iframe src="${url}" class="w-full h-[600px] border-0 rounded shadow-lg"></iframe>`;
        } else {
            content.innerHTML = `<div class="p-8 text-center"><svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><p class="mt-4 text-gray-500">Vista previa no disponible para este tipo de archivo</p></div>`;
        }
    }, 100);
}

function closePreview() {
    const modal = document.getElementById('previewModal');
    modal.classList.add('hidden');
    document.getElementById('previewContent').innerHTML = '';
}

// Cerrar con tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closePreview();
    }
});
</script>
@endpush

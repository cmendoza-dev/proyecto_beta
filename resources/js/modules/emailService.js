export const EMAILJS_CONFIG = {
    serviceId: 'service_39ah582',
    templateId: 'template_u6cp1np',
    publicKey: 'mpe9oN39k_jL28HiD'
};

export async function ensureEmailJS() {
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

export async function enviarEmailParticipante(meetingId, participante, documentos) {
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

export async function enviarDocumentosMasivo(meetingId, participantes, documentos) {
    const resultados = {
        exitosos: [],
        fallidos: []
    };

    mostrarLoading(true);

    for (const participante of participantes) {
        try {
            await enviarEmailParticipante(meetingId, participante, documentos);
            resultados.exitosos.push(participante.email);
            await delay(500);
        } catch (error) {
            console.error(`Error enviando a ${participante.email}:`, error);
            resultados.fallidos.push({
                email: participante.email,
                error: error.message
            });
        }
    }

    await registrarEnvios(meetingId, resultados);
    mostrarLoading(false);
    mostrarResultados(resultados);

    return resultados;
}

async function registrarEnvios(meetingId, resultados) {
    try {
        const recipients = [
            ...resultados.exitosos.map(email => ({
                email,
                status: 'success'
            })),
            ...resultados.fallidos.map(f => ({
                email: f.email,
                status: 'failed',
                error: f.error
            }))
        ];

        await fetch(`/meetings/${meetingId}/email-log`, {
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
            <button onclick="window.cerrarModalResultados()" class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
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

window.cerrarModalResultados = function() {
    document.getElementById('resultados-modal')?.remove();
};

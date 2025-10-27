import { ensureEmailJS, enviarDocumentosMasivo } from './emailService.js';
import { compartirPorWhatsApp } from './whatsappService.js';

export async function prepararEnvioMasivo(meetingId) {
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
            await Swal.fire({
                icon: 'info',
                title: 'Sin participantes',
                text: 'No hay participantes registrados para enviar correos.'
            });
            return;
        }

        if (!data.documentos || data.documentos.length === 0) {
            await Swal.fire({
                icon: 'info',
                title: 'Sin documentos',
                text: 'No hay documentos para enviar.'
            });
            return;
        }

        const { isConfirmed } = await Swal.fire({
            title: '¿Enviar documentos por email?',
            html: `Se enviarán <b>${data.documentos.length}</b> documento(s) a <b>${data.participantes.length}</b> participante(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280'
        });

        if (isConfirmed) {
            await enviarDocumentosMasivo(meetingId, data.participantes, {
                meeting: data.meeting,
                files: data.documentos,
                sender: data.sender
            });
        }
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al preparar el envío: ' + (error.message || 'Desconocido')
        });
    }
}

// Exportar funciones para uso global
window.prepararEnvioMasivo = prepararEnvioMasivo;
window.compartirPorWhatsApp = compartirPorWhatsApp;

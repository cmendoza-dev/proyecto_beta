export async function compartirPorWhatsApp(meetingId) {
    // TODO: Función en desarrollo
    // Esta funcionalidad está siendo implementada
    console.log('compartirPorWhatsApp - En desarrollo');

    await Swal.fire({
        icon: 'info',
        title: 'Funcionalidad en desarrollo',
        text: 'Por favor, usa la opción de compartir por correo electrónico mientras tanto.'
    });

    /* CÓDIGO ORIGINAL - COMENTADO TEMPORALMENTE
    try {
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
        console.log('Datos recibidos para WhatsApp:', data);

        if (!data.participantes || data.participantes.length === 0) {
            await Swal.fire({
                icon: 'info',
                title: 'Sin participantes',
                text: 'No hay participantes registrados para compartir'
            });
            return;
        }

        if (!data.documentos || data.documentos.length === 0) {
            await Swal.fire({
                icon: 'info',
                title: 'Sin documentos',
                text: 'No hay documentos para compartir'
            });
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Enviando por WhatsApp...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar a través del backend
        const envioResponse = await fetch(`/meetings/${meetingId}/enviar-whatsapp`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!envioResponse.ok) {
            throw new Error('Error al enviar mensajes');
        }

        const resultado = await envioResponse.json();

        await Swal.fire({
            icon: 'success',
            title: 'Mensajes enviados',
            html: `
                <p>✅ Enviados: ${resultado.exitosos}</p>
                <p>❌ Fallidos: ${resultado.fallidos}</p>
            `
        });

    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al enviar por WhatsApp: ' + (error.message || 'Desconocido')
        });
    }
    */
}

// OPCIÓN ALTERNATIVA: Crear grupo de WhatsApp (Solo abre la interfaz)
export async function compartirPorWhatsAppGrupo(meetingId) {
    try {
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
                text: 'No hay participantes registrados'
            });
            return;
        }

        // Obtener números de teléfono
        const numeros = data.participantes
            .filter(p => p.phone)
            .map(p => limpiarNumeroTelefono(p.phone))
            .join(',');

        if (!numeros) {
            await Swal.fire({
                icon: 'warning',
                title: 'Sin números',
                text: 'No hay participantes con números de teléfono'
            });
            return;
        }

        // Construir mensaje
        const mensaje = construirMensajeWhatsApp(data);

        // Abrir WhatsApp para envío grupal (el usuario debe crear/seleccionar el grupo)
        const urlWhatsApp = `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
        window.open(urlWhatsApp, '_blank');

        await Swal.fire({
            icon: 'info',
            title: 'WhatsApp abierto',
            html: `
                <p>Se abrió WhatsApp con el mensaje.</p>
                <p><strong>Nota:</strong> Selecciona manualmente a los participantes o el grupo para enviar el mensaje con los archivos adjuntos.</p>
                <p><small>Los archivos deben adjuntarse manualmente desde la aplicación.</small></p>
            `
        });

    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al preparar el envío: ' + (error.message || 'Desconocido')
        });
    }
}

function construirMensajeWhatsApp(data) {
    let mensaje = `📋 *${data.meeting.title}*\n\n`;
    mensaje += `📅 *Fecha:* ${formatearFecha(data.meeting.date)}\n`;
    mensaje += `📍 *Ubicación:* ${data.meeting.location || 'Sin ubicación'}\n\n`;

    if (data.meeting.description) {
        mensaje += `📝 *Descripción:*\n${data.meeting.description}\n\n`;
    }

    mensaje += `📎 *Documentos (${data.documentos.length})* :\n`;
    data.documentos.forEach((doc, index) => {
        mensaje += `${index + 1}. ${doc.original_name}\n`;
    });

    mensaje += `\n🔗 *Enlace para ver y descargar:*\n`;
    mensaje += `${window.location.origin}/meetings/${data.meeting.id}\n\n`;
    mensaje += `_Mensaje automático del Sistema de Asistencia_`;

    return mensaje;
}

function limpiarNumeroTelefono(numero) {
    let limpio = numero.replace(/[\s\-\(\)]/g, '');
    if (!limpio.startsWith('+')) {
        limpio = '+52' + limpio; // México
    }
    return limpio;
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

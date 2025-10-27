<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WhatsAppController extends Controller
{
    /**
     * Enviar documentos de reunión por WhatsApp
     */
    public function enviarWhatsApp(Meeting $meeting)
    {
        try {
            // Verificar que hay participantes
            $participantes = $meeting->attendances()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get();

            if ($participantes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay participantes con teléfono'
                ], 400);
            }

            // Obtener documentos
            $documentos = $meeting->documents;

            if ($documentos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay documentos para enviar'
                ], 400);
            }

            $exitosos = 0;
            $fallidos = 0;

            // Enviar a cada participante
            foreach ($participantes as $participante) {
                try {
                    $telefono = $this->limpiarNumeroTelefono($participante->phone);

                    // Enviar mensaje inicial
                    $this->enviarMensajeTexto($telefono, $meeting);

                    // Enviar cada documento
                    foreach ($documentos as $documento) {
                        $this->enviarDocumento($telefono, $documento);
                    }

                    $exitosos++;
                } catch (\Exception $e) {
                    \Log::error("Error enviando a {$participante->phone}: " . $e->getMessage());
                    $fallidos++;
                }
            }

            return response()->json([
                'success' => true,
                'exitosos' => $exitosos,
                'fallidos' => $fallidos
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en enviarWhatsApp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensaje de texto por WhatsApp Business API
     */
    private function enviarMensajeTexto($telefono, Meeting $meeting)
    {
        $mensaje = $this->construirMensaje($meeting);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
            'Content-Type' => 'application/json',
        ])->post(config('services.whatsapp.api_url') . '/messages', [
            'messaging_product' => 'whatsapp',
            'to' => $telefono,
            'type' => 'text',
            'text' => [
                'body' => $mensaje
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error al enviar mensaje: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Enviar documento por WhatsApp Business API
     */
    private function enviarDocumento($telefono, $documento)
    {
        // Obtener URL pública del documento
        $url = Storage::disk('public')->url($documento->file_path);
        $urlCompleta = url($url);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
            'Content-Type' => 'application/json',
        ])->post(config('services.whatsapp.api_url') . '/messages', [
            'messaging_product' => 'whatsapp',
            'to' => $telefono,
            'type' => 'document',
            'document' => [
                'link' => $urlCompleta,
                'filename' => $documento->original_name
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error al enviar documento: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Construir mensaje
     */
    private function construirMensaje(Meeting $meeting)
    {
        $fecha = \Carbon\Carbon::parse($meeting->date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $mensaje = "📋 *{$meeting->title}*\n\n";
        $mensaje .= "📅 *Fecha:* {$fecha}\n";
        $mensaje .= "📍 *Ubicación:* " . ($meeting->location ?: 'Sin ubicación') . "\n\n";

        if ($meeting->description) {
            $mensaje .= "📝 *Descripción:*\n{$meeting->description}\n\n";
        }

        $mensaje .= "📎 *Documentos adjuntos:* {$meeting->documents->count()}\n\n";
        $mensaje .= "🔗 *Ver en línea:*\n" . route('meetings.show', $meeting->id) . "\n\n";
        $mensaje .= "_Mensaje automático del Sistema de Asistencia_";

        return $mensaje;
    }

    /**
     * Limpiar número de teléfono
     */
    private function limpiarNumeroTelefono($numero)
    {
        $limpio = preg_replace('/[\s\-\(\)]/', '', $numero);

        if (!str_starts_with($limpio, '+')) {
            $limpio = '+52' . $limpio; // México
        }

        return $limpio;
    }
}

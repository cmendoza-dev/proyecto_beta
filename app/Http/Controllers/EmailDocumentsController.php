<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailDocumentsController extends Controller
{
    /**
     * Obtener datos para envío masivo de emails
     */
    public function getEmailData($meetingId)
    {
        try {
            $meeting = Meeting::with([
                'documents.uploader',
                'participants',
            ])->findOrFail($meetingId);

            // Verificar permisos
            if (!$this->canSendEmails($meeting)) {
                return response()->json([
                    'error' => 'No tienes permisos para enviar correos'
                ], 403);
            }

            // Participantes (via attendances pivot)
            $participantes = $meeting->participants->map(function ($participant) {
                $fullName = trim(($participant->name ?? '') . ' ' . ($participant->last_name ?? ''));
                return [
                    'id' => $participant->id,
                    'name' => $fullName !== '' ? $fullName : ($participant->name ?? ''),
                    'email' => $participant->email,
                ];
            })->toArray();

            // Documentos (tabla documents)
            $documentos = $meeting->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'original_name' => $doc->original_name ?? $doc->name,
                    'file_path' => $doc->file_path,
                    'file_size' => $doc->file_size,
                    'mime_type' => $doc->mime_type,
                    'uploaded_by' => optional($doc->uploader)->name,
                    'uploaded_at' => optional($doc->created_at)->format('d/m/Y H:i'),
                ];
            })->toArray();

            // Datos de la reunión
            $meetingData = [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'date' => $meeting->date,
                'location' => $meeting->location,
                'description' => $meeting->description,
                'status' => $meeting->status
            ];

            // Remitente (rol en users)
            $senderUser = Auth::user();
            $sender = [
                'id' => $senderUser?->id,
                'name' => $senderUser?->name,
                'email' => $senderUser?->email,
                'role' => $senderUser?->role, // confirma el nombre de la columna en users
            ];

            return response()->json([
                'success' => true,
                'meeting' => $meetingData,
                'participantes' => $participantes,
                'documentos' => $documentos,
                'sender' => $sender
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar envío de emails (opcional - para tracking)
     */
    public function logEmailSend(Request $request, $meetingId)
    {
        $validated = $request->validate([
            'recipients' => 'required|array',
            'recipients.*.email' => 'required|email',
            'recipients.*.status' => 'required|in:success,failed',
            'recipients.*.error' => 'nullable|string'
        ]);

        try {
            $meeting = Meeting::findOrFail($meetingId);

            $successCount = collect($validated['recipients'])
                ->where('status', 'success')
                ->count();

            $failedCount = collect($validated['recipients'])
                ->where('status', 'failed')
                ->count();

            return response()->json([
                'success' => true,
                'message' => "Emails procesados: {$successCount} exitosos, {$failedCount} fallidos",
                'details' => [
                    'success' => $successCount,
                    'failed' => $failedCount,
                    'total' => count($validated['recipients'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar envíos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si el usuario puede enviar emails
     */
    private function canSendEmails($meeting)
    {
        $user = Auth::user();

        // Administradores y Secretarios pueden enviar (rol en tabla users)
        if (in_array($user->role ?? null, ['Administrator', 'Secretary'])) {
            return true;
        }

        // El creador de la reunión puede enviar
        if ($meeting->created_by === ($user->id ?? null)) {
            return true;
        }

        return false;
    }

    /**
     * Historial (placeholder)
     */
    public function getEmailHistory($meetingId)
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);

            return response()->json([
                'success' => true,
                'message' => 'Historial no implementado aún'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class DocumentController extends Controller
{
    /**
     * Display a listing of meetings with documents.
     */
    public function index(Request $request)
    {
        $meetings = Meeting::with(['documents.uploader', 'participants'])
            ->whereHas('documents')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where('title', 'ilike', "%{$term}%"); // Postgres
            })
            ->when($request->filled('type_meeting'), fn($q) => $q->where('type_meeting', $request->type_meeting))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('documents.index', compact('meetings'));
    }

    /**
     * Store newly uploaded documents.
     */
    public function store(Request $request, Meeting $meeting)
    {
        $request->validate([
            'documents.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        if ($meeting->status !== 'closed') {
            return back()->with('error', 'Solo se pueden subir documentos a reuniones cerradas.');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . uniqid() . '_' . $originalName;
                $filePath = $file->storeAs('documents/meeting_' . $meeting->id, $fileName, 'public');

                Document::create([
                    'meeting_id' => $meeting->id,
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'original_name' => $originalName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id()
                ]);
            }

            return back()->with('success', 'Documentos subidos exitosamente.');
        }

        return back()->with('error', 'No se seleccionaron archivos.');
    }

    /**
     * Upload a single document via AJAX (for modal upload)
     */
    public function upload(Request $request, Meeting $meeting)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip|max:10240'
        ]);

        if ($meeting->status !== 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden subir documentos a reuniones cerradas.'
            ], 403);
        }

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . uniqid() . '_' . $originalName;
            $filePath = $file->storeAs('documents/meeting_' . $meeting->id, $fileName, 'public');

            $document = Document::create([
                'meeting_id' => $meeting->id,
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento subido exitosamente.',
                'file_url' => route('documents.download', [
                    'meeting' => $meeting->id,
                    'filename' => basename($filePath)
                ]),
                'document' => [
                    'id' => $document->id,
                    'name' => $document->original_name,
                    'size' => $document->file_size,
                    'uploaded_at' => $document->created_at->format('d/m/Y H:i')
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se seleccionó ningún archivo.'
        ], 400);
    }

    /**
     * Share documents by email
     */
    public function shareByEmail(Request $request, Meeting $meeting)
    {
        $request->validate([
            'emails' => 'required|string',
            'file_urls.*' => 'required|url'
        ]);

        // Separar emails por coma y limpiar espacios
        $emails = array_map('trim', explode(',', $request->emails));

        // Validar formato de emails
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', "El email '{$email}' no es válido.");
            }
        }

        $fileUrls = $request->file_urls;

        try {
            // Enviar email a cada destinatario
            foreach ($emails as $email) {
                Mail::send('emails.documents-shared', [
                    'meeting' => $meeting,
                    'fileUrls' => $fileUrls,
                    'senderName' => auth()->user()->name
                ], function ($message) use ($email, $meeting) {
                    $message->to($email)
                        ->subject('Documentos de la reunión: ' . $meeting->title);
                });
            }

            return back()->with('success', 'Documentos compartidos exitosamente por email.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar los emails: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific document.
     */
    public function download(Meeting $meeting, $filename)
    {
        $document = Document::where('meeting_id', $meeting->id)
            ->where('file_path', 'like', '%' . $filename)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Documento eliminado exitosamente.');
    }
}

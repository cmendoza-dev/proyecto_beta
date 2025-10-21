<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of meetings with documents.
     */
    public function index()
    {
        $meetings = Meeting::whereNotNull('attachments')
            ->where('attachments', '!=', '[]')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($meeting) {
                // Decodificar attachments si es JSON string
                if (is_string($meeting->attachments)) {
                    $meeting->attachments = json_decode($meeting->attachments, true) ?? [];
                }
                return $meeting;
            });

        return view('documents.index', compact('meetings'));
    }

    /**
     * Download a specific document.
     */
    public function download(Meeting $meeting, $filename)
    {
        // Decodificar attachments
        $attachments = is_string($meeting->attachments)
            ? json_decode($meeting->attachments, true)
            : $meeting->attachments;

        if (!$attachments || !is_array($attachments)) {
            abort(404, 'Documento no encontrado');
        }

        // Buscar el archivo en los attachments
        $filePath = collect($attachments)->first(function ($attachment) use ($filename) {
            return basename($attachment) === $filename;
        });

        if (!$filePath || !Storage::exists($filePath)) {
            abort(404, 'Documento no encontrado');
        }

        return Storage::download($filePath, $filename);
    }
}

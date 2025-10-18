<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index()
    {
        $meetings = Meeting::query()
            ->withCount('attendances')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('secretary.meetings.index', compact('meetings'));
    }

    public function create()
    {
        return view('secretary.meetings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        // Determinar el estado inicial según el botón presionado
        $status = $request->input('action') === 'save_and_open' ? 'open' : 'draft';

        $meeting = Meeting::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'opening_time' => $validated['opening_time'],
            'closing_time' => $validated['closing_time'],
            'location' => $validated['location'] ?? null,
            'status' => $status,
            'created_by' => auth()->id(),
        ]);

        $message = $status === 'open'
            ? 'Reunión creada y abierta correctamente'
            : 'Reunión creada correctamente';

        return redirect()->route('meetings.index')->with('success', $message);
    }

    public function show(Meeting $meeting)
    {
        // $meeting = Meeting::findOrFail($id);

        //Cargar las asistencias de la reunión
        $attendances = $meeting->attendances()
        ->with('participant')
        ->orderBy('created_at', 'asc')
        ->get();

        return view('secretary.meetings.show', compact('meeting', 'attendances'));
    }

    public function edit($id)
    {
        $meeting = Meeting::findOrFail($id);
        return view('secretary.meetings.edit', compact('meeting'));
    }

    public function update(Request $request, $id)
    {
        $meeting = Meeting::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,open,closed'],
        ]);

        $meeting->update($validated);

        return redirect()->route('meetings.index')->with('success', 'Reunión actualizada correctamente');
    }

    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $meeting->delete();

        return redirect()->route('meetings.index')->with('success', 'Reunión eliminada correctamente');
    }

    public function open($id)
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->status !== 'draft') {
            return back()->withErrors(['message' => 'Solo se pueden abrir reuniones en estado borrador.']);
        }

        $meeting->update(['status' => 'open']);

        return redirect()->route('meetings.index')->with('success', 'Reunión abierta correctamente');
    }

    public function close($id)
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->status !== 'open') {
            return back()->withErrors(['message' => 'Solo se pueden cerrar reuniones abiertas.']);
        }

        $meeting->update(['status' => 'closed']);

        return redirect()->route('meetings.index')->with('success', 'Reunión cerrada correctamente');
    }

    public function generateReport($id)
    {
        $meeting = Meeting::with(['attendances.participant'])->findOrFail($id);

        // Verificar que tenga asistencias
        if ($meeting->attendances->count() === 0) {
            return redirect()->route('meetings.show', $meeting)
                ->with('error', 'No hay asistencias registradas para generar el reporte');
        }

        // Aquí irá la generación del PDF
        // Por ahora retorna vista de prueba o mensaje
        return redirect()->route('meetings.show', $meeting)
            ->with('info', 'Generación de reportes PDF en desarrollo');
    }
}


// <?php
// use Barryvdh\DomPDF\Facade\Pdf;

// public function generateReport($id)
// {
//     $meeting = Meeting::with(['attendances.participant'])->findOrFail($id);

//     if ($meeting->attendances->count() === 0) {
//         return redirect()->route('meetings.show', $meeting)
//             ->with('error', 'No hay asistencias registradas');
//     }

//     $pdf = Pdf::loadView('reports.meeting-pdf', compact('meeting'));

//     return $pdf->download('reunion-' . $meeting->id . '-' . $meeting->date->format('Y-m-d') . '.pdf');
// }

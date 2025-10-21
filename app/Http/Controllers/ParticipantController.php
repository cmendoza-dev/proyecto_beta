<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParticipantController extends Controller
{
    public function index()
    {
        $participants = Participant::latest()->paginate(10);
        return view('participants.index', compact('participants'));
    }

    public function create()
    {
        return view('participants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni'         => ['required', 'digits:8', 'unique:participants,dni'],
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:150'],
            'phone'       => ['required', 'string', 'max:15'],
            'email'       => ['nullable', 'email', 'max:255', 'unique:participants,email'],
            'organization'=> ['nullable', 'string', 'max:255'],
            'position'    => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        Participant::create([
            'name'         => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'dni'          => $validated['dni'],
            'phone'        => $validated['phone'],
            'email'        => $validated['email'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'position'     => $validated['position'] ?? null,
            'is_active'    => $request->boolean('is_active'),
        ]);

        return redirect()->route('participants.index')
            ->with('success', 'Participante creado correctamente');
    }

    public function show($id)
    {
        $participant = Participant::findOrFail($id);
        return view('participants.show', compact('participant'));
    }

    public function edit($id)
    {
        $participant = Participant::findOrFail($id);
        // Reutiliza la misma vista de create para editar
        return view('participants.create', compact('participant'));
    }

    public function update(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);

        $validated = $request->validate([
            // DNI no se edita (readonly en la vista)
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:150'],
            'phone'       => ['required', 'string', 'max:15'],
            'email'       => [
                'nullable', 'email', 'max:255',
                Rule::unique('participants', 'email')->ignore($participant->id),
            ],
            'organization'=> ['nullable', 'string', 'max:255'],
            'position'    => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $participant->update([
            'name'         => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'phone'        => $validated['phone'],
            'email'        => $validated['email'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'position'     => $validated['position'] ?? null,
            'is_active'    => $request->boolean('is_active'),
        ]);

        return redirect()->route('participants.index')
            ->with('success', 'Participante actualizado correctamente');
    }

    public function destroy($id)
    {
        $participant = Participant::findOrFail($id);
        $participant->delete();

        return redirect()->route('participants.index')
            ->with('success', 'Participante eliminado correctamente');
    }
}

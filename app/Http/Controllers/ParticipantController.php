<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;

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
        return view('participants.edit', compact('participant'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('participants.index')
            ->with('success', 'Participante actualizado correctamente');
    }

    public function destroy($id)
    {
        return redirect()->route('participants.index')
            ->with('success', 'Participante eliminado correctamente');
    }
}

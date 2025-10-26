<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\Participant;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function register(Request $request)
    {
        $openMeetings = Meeting::where('status', 'open')->orderByDesc('date')->get();

        $selectedMeeting = null;
        if ($request->filled('meeting')) {
            $selectedMeeting = Meeting::find($request->input('meeting'));
        }

        $recentAttendances = collect();
        $totalAttendances = 0;

        if ($selectedMeeting) {
            $recentAttendances = Attendance::with('participant')
                ->where('meeting_id', $selectedMeeting->id)
                ->orderByDesc('attended_at')
                ->limit(3)
                ->get();

            $totalAttendances = Attendance::where('meeting_id', $selectedMeeting->id)->count();
        }

        return view('secretary.attendance.register', compact(
            'openMeetings', 'selectedMeeting', 'recentAttendances', 'totalAttendances'
        ));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'meeting_id' => ['required', 'exists:meetings,id'],
            'dni' => ['required'],
        ]);

        $participant = Participant::where('dni', $data['dni'])->first();
        if (!$participant) {
            return back()->withErrors(['dni' => 'Participante no encontrado'])->withInput();
        }

        $attendance = Attendance::firstOrCreate(
            ['meeting_id' => $data['meeting_id'], 'participant_id' => $participant->id],
            ['attended_at' => now(), 'status' => 'present']
        );

        if (!$attendance->wasRecentlyCreated) {
            return back()->withErrors(['dni' => 'El participante ya fue registrado en esta reunión.'])->withInput();
        }

        return back()->with('success', 'Asistencia registrada.');
    }

}

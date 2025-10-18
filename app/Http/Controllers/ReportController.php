<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::query()
            ->withCount('attendances');

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filtro por fecha desde
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        // Filtro por fecha hasta
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $meetings = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Estadísticas
        $totalMeetings = Meeting::count();
        $totalAttendances = \App\Models\Attendance::count();
        $averageAttendance = $totalMeetings > 0
            ? round($totalAttendances / $totalMeetings, 1)
            : 0;

        return view('admin.reports.index', compact(
            'meetings',
            'totalMeetings',
            'totalAttendances',
            'averageAttendance'
        ));
    }
}

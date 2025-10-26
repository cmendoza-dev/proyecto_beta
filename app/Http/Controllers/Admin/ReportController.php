<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Listado con filtros y conteo de asistencias
        $query = Meeting::query()->withCount('attendances');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('date', '<=', $to);
        }

        $meetings = $query
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        // Estadísticas globales (puedes ajustarlo a filtradas si prefieres)
        $allMeetingsQuery = Meeting::query()->withCount('attendances');
        $totalMeetings = $allMeetingsQuery->count();
        $totalAttendances = $allMeetingsQuery->get()->sum('attendances_count');
        $averageAttendance = $totalMeetings > 0 ? round($totalAttendances / $totalMeetings, 2) : 0;

        return view('admin.reports.index', compact(
            'meetings',
            'totalMeetings',
            'totalAttendances',
            'averageAttendance'
        ));
    }
}

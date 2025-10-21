<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $todayMeetings = Meeting::whereDate('date', today())->count();
        $todayAttendances = Attendance::whereDate('created_at', today())->count();

        // Solo reuniones cerradas, más recientes
        $recentMeetings = Meeting::where('status', 'closed')
            ->withCount('attendances')
            ->with(['participants:id,name,last_name'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'todayMeetings',
            'todayAttendances',
            'recentMeetings'
        ));
    }
}

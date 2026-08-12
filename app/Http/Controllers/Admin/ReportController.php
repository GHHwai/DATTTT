<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
        ];

        return view('admin.reports', compact('stats'));
    }

    /**
     * JSON data consumed by Chart.js on the reports page.
     */
    public function chartData(): JsonResponse
    {
        $tasksByStatus = Task::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $usersByRole = User::join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'name');

        $signupsByDay = User::select(DB::raw('date(created_at) as day'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        return response()->json([
            'tasksByStatus' => $tasksByStatus,
            'usersByRole' => $usersByRole,
            'signupsByDay' => $signupsByDay,
        ]);
    }
}

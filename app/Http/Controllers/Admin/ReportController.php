<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
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
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
            'resolved_tickets' => Ticket::whereIn('status', ['resolved', 'closed'])->count(),
            'unclaimed_tickets' => Ticket::whereNull('assigned_to')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count(),
        ];

        return view('admin.reports', compact('stats'));
    }

    /**
     * JSON data consumed by Chart.js on the reports page.
     */
    public function chartData(): JsonResponse
    {
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $ticketsByCategory = Ticket::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $usersByRole = User::join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'name');

        $ticketsByDay = Ticket::select(DB::raw('date(created_at) as day'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        // How many tickets each IT staff member has resolved/closed —
        // relevant now that tickets are claimed/worked by IT staff.
        $resolvedByStaff = Ticket::query()
            ->join('users', 'tickets.assigned_to', '=', 'users.id')
            ->whereIn('tickets.status', ['resolved', 'closed'])
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->pluck('total', 'name');

        return response()->json([
            'ticketsByStatus' => $ticketsByStatus,
            'ticketsByPriority' => $ticketsByPriority,
            'ticketsByCategory' => $ticketsByCategory,
            'usersByRole' => $usersByRole,
            'ticketsByDay' => $ticketsByDay,
            'resolvedByStaff' => $resolvedByStaff,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = $this->summaryStats();

        return view('admin.reports', compact('stats'));
    }

    /**
     * JSON data consumed by Chart.js on the reports page.
     */
    public function chartData(): JsonResponse
    {
        return response()->json($this->chartDataSets());
    }

    /**
     * Download the current report stats and chart data as a single CSV file.
     */
    public function export(): StreamedResponse
    {
        $filename = 'admin-reports-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Excel-friendly UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Admin Reports Export']);
            fputcsv($handle, ['Generated at', now()->toDateTimeString()]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Metric', 'Value']);
            foreach ($this->summaryStats() as $label => $value) {
                fputcsv($handle, [$this->labelFor($label), $value]);
            }
            fputcsv($handle, []);

            $sections = [
                'Tickets by Status' => 'ticketsByStatus',
                'Tickets by Priority' => 'ticketsByPriority',
                'Tickets by Category' => 'ticketsByCategory',
                'Users by Role' => 'usersByRole',
                'Tickets Submitted by Day (last 14 days)' => 'ticketsByDay',
                'Resolved Tickets by IT Staff' => 'resolvedByStaff',
            ];

            $chartData = $this->chartDataSets();

            foreach ($sections as $title => $key) {
                fputcsv($handle, [$title]);
                fputcsv($handle, ['Label', 'Count']);

                foreach ($chartData[$key] as $label => $count) {
                    fputcsv($handle, [$label, $count]);
                }

                fputcsv($handle, []);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Top-level summary counts shown as cards on the reports page.
     *
     * @return array<string, int>
     */
    private function summaryStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
            'resolved_tickets' => Ticket::whereIn('status', ['resolved', 'closed'])->count(),
            'unclaimed_tickets' => Ticket::whereNull('assigned_to')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count(),
        ];
    }

    /**
     * Shared query logic for the Chart.js JSON endpoint and the CSV export.
     */
    private function chartDataSets(): array
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

        return [
            'ticketsByStatus' => $ticketsByStatus,
            'ticketsByPriority' => $ticketsByPriority,
            'ticketsByCategory' => $ticketsByCategory,
            'usersByRole' => $usersByRole,
            'ticketsByDay' => $ticketsByDay,
            'resolvedByStaff' => $resolvedByStaff,
        ];
    }

    /**
     * Turn a summary stat array key into a human-readable label for the CSV.
     */
    private function labelFor(string $key): string
    {
        return match ($key) {
            'total_users' => 'Total Users',
            'total_tickets' => 'Total Tickets',
            'open_tickets' => 'Open / In Progress',
            'resolved_tickets' => 'Resolved / Closed',
            'unclaimed_tickets' => 'Unclaimed',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }
}

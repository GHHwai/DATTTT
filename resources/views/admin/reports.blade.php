@extends('layouts.app')

@section('title', 'Admin Reports')

@section('content')
<h1 class="text-xl font-semibold mb-4">Reports &amp; Analytics</h1>
<p class="text-sm text-gray-500 mb-4">Visible to admins only — IT staff can work tickets but don't see this page.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['total_users'] }}</div>
        <div class="text-sm text-gray-500">Total Users</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['total_tickets'] }}</div>
        <div class="text-sm text-gray-500">Total Tickets</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['open_tickets'] }}</div>
        <div class="text-sm text-gray-500">Open / In Progress</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['resolved_tickets'] }}</div>
        <div class="text-sm text-gray-500">Resolved / Closed</div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets by Status</h2>
        <canvas id="statusChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets by Priority</h2>
        <canvas id="priorityChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Users by Role</h2>
        <canvas id="rolesChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets Submitted (last 14 days)</h2>
        <canvas id="ticketsChart" height="220"></canvas>
    </div>
</div>
@endsection

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
    fetch('{{ route('admin.reports.data') }}')
        .then((r) => r.json())
        .then((data) => {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data.ticketsByStatus),
                    datasets: [{
                        data: Object.values(data.ticketsByStatus),
                        backgroundColor: ['#fbbf24', '#60a5fa', '#34d399', '#9ca3af'],
                    }],
                },
            });

            new Chart(document.getElementById('priorityChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data.ticketsByPriority),
                    datasets: [{
                        data: Object.values(data.ticketsByPriority),
                        backgroundColor: ['#d1d5db', '#60a5fa', '#fbbf24', '#f87171'],
                    }],
                },
            });

            new Chart(document.getElementById('rolesChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(data.usersByRole),
                    datasets: [{
                        label: 'Users',
                        data: Object.values(data.usersByRole),
                        backgroundColor: '#6366f1',
                    }],
                },
                options: { plugins: { legend: { display: false } } },
            });

            new Chart(document.getElementById('ticketsChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(data.ticketsByDay),
                    datasets: [{
                        label: 'Tickets Submitted',
                        data: Object.values(data.ticketsByDay),
                        borderColor: '#6366f1',
                        tension: 0.3,
                        fill: false,
                    }],
                },
            });
        });
</script>
@endpush

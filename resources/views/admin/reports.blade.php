@extends('layouts.app')

@section('title', 'Admin Reports')

@section('content')
<h1 class="text-xl font-semibold mb-4">Reports &amp; Analytics</h1>
<p class="text-sm text-gray-500 mb-4">Visible to admins only — IT staff can work tickets but don't see this page.</p>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
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
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['unclaimed_tickets'] }}</div>
        <div class="text-sm text-gray-500">Unclaimed</div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets by Status</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="statusChart"></canvas>
            <p id="statusChart-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">No data yet</p>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets by Priority</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="priorityChart"></canvas>
            <p id="priorityChart-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">No data yet</p>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets by Category</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="categoryChart"></canvas>
            <p id="categoryChart-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">No data yet</p>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Users by Role</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="rolesChart"></canvas>
            <p id="rolesChart-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">No data yet</p>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tickets Submitted (last 14 days)</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="ticketsChart"></canvas>
            <p id="ticketsChart-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">No data yet</p>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Resolved Tickets by IT Staff</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="staffChart"></canvas>
            <p id="staffChart-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">No data yet</p>
        </div>
    </div>
</div>

<div id="reportsError" class="hidden mt-6 rounded bg-red-100 text-red-800 px-4 py-3 text-sm"></div>
@endsection

@push('head')
@endpush

@push('scripts')
<script>
    function showError(message) {
        console.error('[Admin Reports]', message);
        const el = document.getElementById('reportsError');
        el.textContent = message;
        el.classList.remove('hidden');
    }

    function renderOrEmpty(canvasId, labels, config) {
        const emptyEl = document.getElementById(canvasId + '-empty');
        if (!labels || labels.length === 0) {
            emptyEl.classList.remove('hidden');
            return;
        }
        new Chart(document.getElementById(canvasId), config);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') {
            showError('Chart.js failed to load from the CDN. Check your network/firewall settings, or self-host the library.');
            return;
        }

        fetch('{{ route('admin.reports.data') }}', {
            headers: { 'Accept': 'application/json' },
        })
            .then((r) => {
                if (!r.ok) {
                    throw new Error('Server responded with ' + r.status);
                }
                return r.json();
            })
            .then((data) => {
                renderOrEmpty('statusChart', Object.keys(data.ticketsByStatus || {}), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.ticketsByStatus),
                        datasets: [{
                            data: Object.values(data.ticketsByStatus),
                            backgroundColor: ['#fbbf24', '#60a5fa', '#34d399', '#9ca3af'],
                        }],
                    },
                });

                renderOrEmpty('priorityChart', Object.keys(data.ticketsByPriority || {}), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.ticketsByPriority),
                        datasets: [{
                            data: Object.values(data.ticketsByPriority),
                            backgroundColor: ['#d1d5db', '#60a5fa', '#fbbf24', '#f87171'],
                        }],
                    },
                });

                renderOrEmpty('categoryChart', Object.keys(data.ticketsByCategory || {}), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(data.ticketsByCategory),
                        datasets: [{
                            data: Object.values(data.ticketsByCategory),
                            backgroundColor: ['#a78bfa', '#f472b6', '#38bdf8', '#facc15', '#94a3b8'],
                        }],
                    },
                });

                renderOrEmpty('rolesChart', Object.keys(data.usersByRole || {}), {
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

                renderOrEmpty('ticketsChart', Object.keys(data.ticketsByDay || {}), {
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

                renderOrEmpty('staffChart', Object.keys(data.resolvedByStaff || {}), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data.resolvedByStaff),
                        datasets: [{
                            label: 'Resolved Tickets',
                            data: Object.values(data.resolvedByStaff),
                            backgroundColor: '#10b981',
                        }],
                    },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } } },
                });
            })
            .catch((err) => {
                showError('Could not load chart data: ' + err.message);
            });
    });
</script>
@endpush

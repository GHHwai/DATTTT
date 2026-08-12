@extends('layouts.app')

@section('title', 'Admin Reports')

@section('content')
<h1 class="text-xl font-semibold mb-4">Reports &amp; Analytics</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['total_users'] }}</div>
        <div class="text-sm text-gray-500">Total Users</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['total_tasks'] }}</div>
        <div class="text-sm text-gray-500">Total Tasks</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['completed_tasks'] }}</div>
        <div class="text-sm text-gray-500">Completed Tasks</div>
    </div>
    <div class="bg-white rounded shadow p-4">
        <div class="text-2xl font-bold">{{ $stats['pending_tasks'] }}</div>
        <div class="text-sm text-gray-500">Pending Tasks</div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Tasks by Status</h2>
        <canvas id="tasksChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-medium mb-2">Users by Role</h2>
        <canvas id="rolesChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded shadow p-4 md:col-span-2">
        <h2 class="font-medium mb-2">Signups (last 14 days)</h2>
        <canvas id="signupsChart" height="120"></canvas>
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
            new Chart(document.getElementById('tasksChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data.tasksByStatus),
                    datasets: [{
                        data: Object.values(data.tasksByStatus),
                        backgroundColor: ['#fbbf24', '#60a5fa', '#34d399'],
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

            new Chart(document.getElementById('signupsChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(data.signupsByDay),
                    datasets: [{
                        label: 'Signups',
                        data: Object.values(data.signupsByDay),
                        borderColor: '#6366f1',
                        tension: 0.3,
                        fill: false,
                    }],
                },
            });
        });
</script>
@endpush

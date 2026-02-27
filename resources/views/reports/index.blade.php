@extends('layouts.panel')
@section('title', 'Project Reports')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Performance Reports <span class="page-subtitle">Monthly overview of leads, revenue and production</span></h1>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="panel-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="panel-card-title"><i class="fa-solid fa-chart-area"></i> Revenue & Growth</h5>
                <select class="form-select form-select-sm w-auto" id="reportRange">
                    <option value="6">Last 6 Months</option>
                    <option value="12" selected>Last 12 Months</option>
                </select>
            </div>
            <div style="height:350px;">
                <canvas id="revenueReportChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="panel-card h-100">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-pie-chart"></i> Project Stages</h5>
            <div style="height:280px;">
                <canvas id="stageReportChart"></canvas>
            </div>
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span>Active Projects</span>
                    <span class="fw-bold">{{ \App\Models\Project::whereNotIn('stage',['completed'])->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span>Leads Converted</span>
                    <span class="fw-bold">{{ \App\Models\Lead::where('status','converted')->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-table"></i> Monthly Statistics Table</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th>Month</th>
                            <th>Total Leads</th>
                            <th>Conversions</th>
                            <th>Conv. Rate</th>
                            <th>Projects Started</th>
                            <th>Revenue (Meta Budget)</th>
                        </tr>
                    </thead>
                    <tbody id="statsTableBody">
                        {{-- Filled by JS --}}
                        <tr><td colspan="6" class="text-center py-4 text-muted">Loading statistics...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadReports();
    
    $('#reportRange').on('change', loadReports);

    function loadReports() {
        $.getJSON('/reports/data', function(data) {
            renderCharts(data);
            renderTable(data);
        });
    }

    function renderCharts(data) {
        // Revenue Chart
        const ctxRev = document.getElementById('revenueReportChart').getContext('2d');
        if(window.revChart) window.revChart.destroy();
        window.revChart = new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: data.months,
                datasets: [
                    {
                        label: 'Total Budget (₹)',
                        data: data.revenue,
                        borderColor: '#6c3fc5',
                        backgroundColor: 'rgba(108,63,197, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Leads Received',
                        data: data.leads,
                        borderColor: '#45aaf2',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        type: 'line',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    y1: { position: 'right', beginAtZero: true, grid: { display: false } }
                }
            }
        });

        // Stage Chart
        const ctxStage = document.getElementById('stageReportChart').getContext('2d');
        if(window.stageChart) window.stageChart.destroy();
        window.stageChart = new Chart(ctxStage, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.stages),
                datasets: [{
                    data: Object.values(data.stages),
                    backgroundColor: ['#6c3fc5', '#45aaf2', '#f7b731', '#26de81', '#a55eea']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    function renderTable(data) {
        let html = '';
        data.months.reverse().forEach((month, idx) => {
            const revIdx = data.months.length - 1 - idx;
            const leads = data.leads[revIdx];
            const conv = data.conversions[revIdx];
            const rate = leads > 0 ? (conv / leads * 100).toFixed(1) : '0';
            
            html += `
                <tr>
                    <td><strong>${month}</strong></td>
                    <td>${leads}</td>
                    <td>${conv}</td>
                    <td><span class="badge bg-light text-dark">${rate}%</span></td>
                    <td>${data.projects[revIdx]}</td>
                    <td class="fw-bold">₹${data.revenue[revIdx].toLocaleString()}</td>
                </tr>
            `;
        });
        $('#statsTableBody').html(html);
        data.months.reverse(); // Restore order for charts
    }
});
</script>
@endpush

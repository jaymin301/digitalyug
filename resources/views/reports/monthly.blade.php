@extends('layouts.panel')

@section('title', 'Monthly Performance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Monthly Report</li>
@endsection

@section('content')
<div class="dashboard-overview">
    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-title">
            Monthly Performance Report
            <span class="page-subtitle">Detailed overview of leads, revenue, and production performance</span>
        </h1>
        <div class="d-flex gap-2 align-items-center">
            <label for="yearSelect" class="small text-muted d-none d-md-inline">Select Year:</label>
            <select class="form-select form-select-sm" id="yearSelect" style="width:120px; border-radius: 8px;">
                @php $currentYear = now()->year; @endphp
                @for($y = $currentYear; $y >= $currentYear - 2; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="stat-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fa-solid fa-handshake"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="totalLeads">0</div>
                <div class="stat-label">Total Leads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="totalConversions">0</div>
                <div class="stat-label">Total Conversions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="totalRevenue">₹0</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="totalCompleted">0</div>
                <div class="stat-label">Completed Projects</div>
            </div>
        </div>
    </div>

    {{-- Monthly Trends Chart --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-chart-line"></i> Monthly Performance Trends</h5>
                </div>
                <div class="chart-container" style="position: relative; height:350px;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Stats Table --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-table"></i> Monthly Breakdown</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Total Leads</th>
                                <th>Conversions</th>
                                <th>Conv. Rate</th>
                                <th>Revenue (₹)</th>
                                <th>Active Projects</th>
                                <th>Completed</th>
                                <th>Edits Approved</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            {{-- Populated by JavaScript --}}
                            <tr><td colspan="8" class="text-center py-4 text-muted">Loading data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let perfChart = null;

    function formatCurrency(val) {
        return '₹' + Number(val).toLocaleString('en-IN');
    }

    function loadData(year) {
        // Show loading state
        $('#reportTableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Loading report data...</td></tr>');

        $.getJSON('{{ route('reports.monthly-data') }}', { year: year }, function(res) {
            // Update Totals
            $('#totalLeads').text(res.totals.total_leads);
            $('#totalConversions').text(res.totals.converted_leads);
            $('#totalRevenue').text(formatCurrency(res.totals.total_revenue));
            $('#totalCompleted').text(res.totals.completed_projects);

            // Populate Table
            let tableHtml = '';
            res.months.forEach(m => {
                const convRate = m.total_leads > 0 ? ((m.converted_leads / m.total_leads) * 100).toFixed(1) : '0.0';
                tableHtml += `
                    <tr>
                        <td><strong>${m.month}</strong></td>
                        <td>${m.total_leads}</td>
                        <td class="text-success">${m.converted_leads}</td>
                        <td><span class="badge rounded-pill bg-light text-dark">${convRate}%</span></td>
                        <td class="fw-bold">${formatCurrency(m.revenue)}</td>
                        <td>${m.active_projects}</td>
                        <td>${m.completed_projects}</td>
                        <td>${m.edits_approved}</td>
                    </tr>
                `;
            });
            $('#reportTableBody').html(tableHtml);

            // Update Chart
            updateChart(res.months);
        });
    }

    function updateChart(monthsData) {
        const labels = monthsData.map(m => m.month);
        const revenue = monthsData.map(m => m.revenue);
        const conversions = monthsData.map(m => m.converted_leads);
        const leads = monthsData.map(m => m.total_leads);

        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        if (perfChart) perfChart.destroy();

        perfChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: revenue,
                        backgroundColor: 'rgba(108,63,197, 0.15)',
                        borderColor: '#6c3fc5',
                        borderWidth: 2,
                        borderRadius: 5,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Conversions',
                        data: conversions,
                        type: 'line',
                        borderColor: '#26de81',
                        backgroundColor: '#26de81',
                        pointRadius: 4,
                        tension: 0.4,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Leads',
                        data: leads,
                        type: 'line',
                        borderColor: '#45aaf2',
                        backgroundColor: '#45aaf2',
                        borderDash: [5, 5],
                        pointRadius: 0,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    y: {
                        position: 'left',
                        title: { display: true, text: 'Revenue' },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { callback: v => '₹' + (v/1000).toFixed(0) + 'k' }
                    },
                    y1: {
                        position: 'right',
                        title: { display: true, text: 'Counts (Leads/Conv)' },
                        grid: { drawOnChartArea: false }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.datasetIndex === 0) {
                                    label += formatCurrency(context.parsed.y);
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize
    loadData($('#yearSelect').val());

    // Handle change
    $('#yearSelect').on('change', function() {
        loadData($(this).val());
    });
});
</script>
@endpush

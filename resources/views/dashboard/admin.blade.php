@extends('layouts.panel')
@section('title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="dashboard-overview">
    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-title">
            Good {{ now()->format('H') < 12 ? 'Morning' : (now()->format('H') < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}! 👋
            <span class="page-subtitle">Here's what's happening at Digital Yug today</span>
        </h1>
        <div class="d-flex gap-2">
            <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;font-size:13px;padding:8px 16px;">
                <i class="fa-solid fa-calendar-day me-1"></i>{{ now()->format('d M Y') }}
            </span>
        </div>
    </div>

    {{-- Quick Actions --}}
    @role('Admin|Manager')
    <div class="quick-actions">
        @role('Admin')<a href="{{ route('admin.employees.create') }}" class="quick-btn"><i class="fa-solid fa-user-plus text-primary"></i> Add Employee</a>@endrole
        <a href="{{ route('leads.create') }}" class="quick-btn"><i class="fa-solid fa-plus text-success"></i> New Lead</a>
        <a href="{{ route('projects.index') }}" class="quick-btn"><i class="fa-solid fa-folder-open text-warning"></i> Projects</a>
        <a href="{{ route('reports.monthly') }}" class="quick-btn"><i class="fa-solid fa-chart-bar text-info"></i> Monthly Report</a>
    </div>
    @endrole

    {{-- Stats Row --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fa-solid fa-handshake"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalLeads }}</div>
                <div class="stat-label">Total Leads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ $convertedLeads }}</div>
                <div class="stat-label">Converted Leads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fa-solid fa-folder-open"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalProjects }}</div>
                <div class="stat-label">Total Projects</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-spinner"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ $conceptProjects + $shootingProjects + $editingProjects }}</div>
                <div class="stat-label">Active Projects</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ $completedProjects }}</div>
                <div class="stat-label">Completed Projects</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-red"><i class="fa-solid fa-film"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ $pendingEdits }}</div>
                <div class="stat-label">Pending Edits</div>
            </div>
        </div>
    </div>

    {{-- Kanban + Chart Row --}}
    <div class="row g-4 mb-4">
        {{-- Kanban Board --}}
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-table-columns"></i> Project Pipeline</h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="kanban-board">
                    @foreach(['pending' => 'Pending', 'concept' => 'Concept', 'shooting' => 'Shooting', 'editing' => 'Editing', 'completed' => 'Done'] as $stage => $label)
                    <div class="kanban-col">
                        <div class="kanban-col-header col-{{ $stage }}">
                            <span>{{ $label }}</span>
                            <span>{{ isset($kanbanProjects[$stage]) ? $kanbanProjects[$stage]->count() : 0 }}</span>
                        </div>
                        <div class="kanban-cards">
                            @if(isset($kanbanProjects[$stage]) && $kanbanProjects[$stage]->count() > 0)
                                @foreach($kanbanProjects[$stage]->take(4) as $p)
                                <a href="{{ route('projects.show', $p) }}" class="kanban-card d-block text-decoration-none">
                                    <div class="kc-name">{{ $p->name }}</div>
                                    <div class="kc-meta">{{ $p->lead->customer_name ?? '' }} · by {{ $p->manager?->name ?? 'N/A' }}</div>
                                    @if($p->start_date)
                                    <div class="kc-progress">
                                        <div class="progress progress-sm">
                                            <div class="progress-bar" style="width:{{ $p->progress_percent }}%"></div>
                                        </div>
                                    </div>
                                    @endif
                                </a>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-3" style="font-size:12px;">No projects</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Team Performance Row --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-users-gear text-success"></i> Team Performance (Current Month)</h5>
                    <span class="badge bg-soft-success text-success" style="background:rgba(38,222,129,0.1);font-size:12px;padding:5px 12px;">{{ now()->format('F Y') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover dashboard-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Role</th>
                                <th class="text-center">Projects</th>
                                <th class="text-center">Shoots</th>
                                <th class="text-center">Concepts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teamPerformance as $perf)
                            <tr>
                                <td data-label="Employee"><strong>{{ $perf['name'] }}</strong></td>
                                <td data-label="Role"><span class="badge bg-soft-info text-info" style="background:rgba(69,170,242,0.1);border:1px solid rgba(69,170,242,0.2);">{{ $perf['role'] }}</span></td>
                                <td data-label="Projects" class="text-center fw-semibold text-dark">{{ $perf['projects'] }}</td>
                                <td data-label="Shoots" class="text-center">
                                    <span class="badge rounded-pill bg-soft-success text-success" style="background:rgba(38,222,129,0.1);border:1px solid rgba(38,222,129,0.2);min-width:35px;">{{ $perf['shoots'] }}</span>
                                </td>
                                <td data-label="Concepts" class="text-center">
                                    <span class="badge rounded-pill bg-soft-purple text-purple" style="background:rgba(108,63,197,0.1);border:1px solid rgba(108,63,197,0.2);color:#6c3fc5;min-width:35px;">{{ $perf['concepts'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No activity recorded this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Chart + Recent Leads --}}
    <div class="row g-4">
        {{-- Revenue Chart --}}
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-chart-area"></i> Revenue & Leads (Last 6 Months)</h5>
                    <select class="form-select form-select-sm" id="chartYearSelect" style="width:auto;">
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                        <option value="{{ now()->year - 1 }}">{{ now()->year - 1 }}</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Stage Breakdown --}}
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-chart-pie"></i> Stage Breakdown</h5>
                </div>
                <div class="chart-container">
                    <canvas id="stageChart" height="200"></canvas>
                </div>
                <div class="mt-3">
                    @foreach([
                        ['label' => 'Pending', 'count' => $pendingProjects, 'color' => '#718096'],
                        ['label' => 'Concept', 'count' => $conceptProjects, 'color' => '#45aaf2'],
                        ['label' => 'Shooting', 'count' => $shootingProjects, 'color' => '#f7b731'],
                        ['label' => 'Editing', 'count' => $editingProjects, 'color' => '#6c3fc5'],
                        ['label' => 'Completed', 'count' => $completedProjects, 'color' => '#26de81'],
                    ] as $s)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $s['color'] }};flex-shrink:0;"></div>
                            <span style="font-size:13px;color:#718096;">{{ $s['label'] }}</span>
                        </div>
                        <strong style="font-size:13px;">{{ $s['count'] }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recent Leads --}}
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-clock-rotate-left"></i> Recent Leads</h5>
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">All Leads</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover dashboard-table">
                        <thead><tr>
                            <th>Date</th><th>Customer</th><th>Contact</th>
                            <th>Reels</th><th>Budget</th><th>Status</th><th>By</th>
                        </tr></thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                            <tr>
                                <td data-label="Date">{{ $lead->date->format('d M Y') }}</td>
                                <td data-label="Customer"><strong>{{ $lead->customer_name }}</strong></td>
                                <td data-label="Contact">{{ $lead->contact_number }}</td>
                                <td data-label="Reels">{{ $lead->total_reels }}</td>
                                <td data-label="Budget">₹{{ number_format($lead->total_meta_budget) }}</td>
                                <td data-label="Status">{!! $lead->status_badge !!}</td>
                                <td data-label="By">{{ $lead->createdBy?->name ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No leads yet</td></tr>
                            @endforelse
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
        // Stage doughnut chart
        const stageCtx = document.getElementById('stageChart').getContext('2d');
        new Chart(stageCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending','Concept','Shooting','Editing','Completed'],
                datasets: [{
                    data: [{{ $pendingProjects }},{{ $conceptProjects }},{{ $shootingProjects }},{{ $editingProjects }},{{ $completedProjects }}],
                    backgroundColor: ['#718096','#45aaf2','#f7b731','#6c3fc5','#26de81'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: { cutout: '72%', plugins: { legend: { display: false } } }
        });

        // Revenue / leads bar+line chart
        function loadChart(year) {
            $.getJSON('{{ route('dashboard.stats') }}', function(res) {
                const labels = res.monthlyRevenue.map(m => m.month);
                const revenue = res.monthlyRevenue.map(m => m.revenue);
                const leads = res.monthlyRevenue.map(m => m.leads);

                const ctx = document.getElementById('revenueChart').getContext('2d');
                if (window._revChart) window._revChart.destroy();
                window._revChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Revenue (₹)',
                                data: revenue,
                                backgroundColor: 'rgba(108,63,197,0.12)',
                                borderColor: '#6c3fc5',
                                borderWidth: 2,
                                borderRadius: 6,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Leads',
                                data: leads,
                                type: 'line',
                                borderColor: '#26de81',
                                backgroundColor: 'rgba(38,222,129,0.08)',
                                pointBackgroundColor: '#26de81',
                                pointRadius: 4,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, font: { size: 12 } } },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ctx.datasetIndex === 0
                                        ? ' ₹' + Number(ctx.raw).toLocaleString('en-IN')
                                        : ' ' + ctx.raw + ' leads'
                                }
                            }
                        },
                        scales: {
                            y: { position: 'left', grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '₹' + (v/1000).toFixed(0) + 'k', font: { size: 11 } } },
                            y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } } }
                        }
                    }
                });
            });
        }

        loadChart('{{ now()->year }}');
    });
</script>
@endpush

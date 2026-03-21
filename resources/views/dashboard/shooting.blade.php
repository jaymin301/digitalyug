<div class="dashboard-overview">
    <div class="page-header">
        <h1 class="page-title">
            Hi {{ explode(' ', auth()->user()->name)[0] }}! 📷
            <span class="page-subtitle">Your upcoming shoots &amp; performance</span>
        </h1>
    </div>

    {{-- Performance Stats Row --}}
    <div class="row g-3 mb-4">
        @php $latest = $performance[count($performance)-1] ?? null; @endphp
        <div class="col-md-4">
            <div class="stat-card p-3 border rounded shadow-sm bg-white d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-primary text-primary" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(108,63,197,0.1);">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold">PROJECTS THIS MONTH</div>
                    <div class="h5 fw-bold mb-0">{{ $latest['project_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-3 border rounded shadow-sm bg-white d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-success text-success" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(38,222,129,0.1);">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold">SHOOTS COMPLETED</div>
                    <div class="h5 fw-bold mb-0">{{ $latest['shoot_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-3 border rounded shadow-sm bg-white d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-warning text-warning" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(247,183,49,0.1);">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold">CONCEPTS HANDLED</div>
                    <div class="h5 fw-bold mb-0">{{ $latest['concept_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="panel-card h-100">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-camera"></i> My Recent Shoots</h5>
                    <a href="{{ route('shoots.index') }}" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date</th><th>Project</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($myShoots as $shoot)
                            <tr>
                                <td>{{ $shoot->shoot_date->format('d M') }}</td>
                                <td class="text-truncate" style="max-width:150px;">{{ $shoot->project->name ?? 'N/A' }}</td>
                                <td>{!! $shoot->status_badge !!}</td>
                                <td><a href="{{ route('shoots.show', $shoot) }}" class="btn btn-sm btn-link p-0"><i class="fa-solid fa-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted small">No shoots assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card h-100">
                <div class="panel-card-header">
                    <h5 class="panel-card-title"><i class="fa-solid fa-chart-line"></i> 6-Month Performance</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:13px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Projects</th>
                                <th class="text-center">Shoots</th>
                                <th class="text-center">Concepts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_reverse($performance) as $p)
                            <tr>
                                <td class="fw-bold">{{ $p['month'] }}</td>
                                <td class="text-center">{{ $p['project_count'] }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-soft-success text-success" style="background:rgba(38,222,129,0.1);">{{ $p['shoot_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-soft-purple text-purple" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $p['concept_count'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

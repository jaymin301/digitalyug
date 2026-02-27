@extends('layouts.panel')
@section('title', $project->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $project->name }} <span class="page-subtitle">Project Overview &amp; Workflow Stage: {!! $project->stage_badge !!}</span></h1>
    <div class="d-flex gap-2">
        @if($project->stage === 'pending')
        <button class="btn btn-primary" onclick="activateProject({{ $project->id }})"><i class="fa-solid fa-bolt me-2"></i>Activate Project</button>
        @endif
        @role('Admin|Manager')
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Manage Workflow</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('concepts.assign-form', $project) }}"><i class="fa-solid fa-lightbulb me-2 text-warning"></i>Assign Concept Task</a></li>
                <li><a class="dropdown-item" href="{{ route('shoots.create', $project) }}"><i class="fa-solid fa-camera me-2 text-info"></i>Schedule Shoot</a></li>
                <li><a class="dropdown-item" href="{{ route('editing.assign-form', $project) }}"><i class="fa-solid fa-film me-2 text-primary"></i>Assign Edit Task</a></li>
            </ul>
        </div>
        @endrole
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Project Progress --}}
        <div class="panel-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="panel-card-title"><i class="fa-solid fa-chart-line"></i> Campaign Progress</h5>
                <span class="fw-bold text-primary">{{ $project->progress_percent }}% Complete</span>
            </div>
            <div class="progress progress-lg">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:{{ $project->progress_percent }}%"></div>
            </div>
            <div class="row mt-4 g-3 text-center">
                <div class="col-4">
                    <div class="text-muted small mb-1">APPROVED CONCEPTS</div>
                    <div class="h4 fw-bold mb-0">{{ $project->approved_concepts }} / {{ $project->conceptTasks->sum('concepts_required') }}</div>
                </div>
                <div class="col-4 border-start">
                    <div class="text-muted small mb-1">COMPLETED SHOOTS</div>
                    <div class="h4 fw-bold mb-0">{{ $project->completed_shoots }}</div>
                </div>
                <div class="col-4 border-start">
                    <div class="text-muted small mb-1">APPROVED VIDEOS</div>
                    <div class="h4 fw-bold mb-0">{{ $project->completed_edits }} / {{ $project->editTasks->sum('total_videos') }}</div>
                </div>
            </div>
        </div>

        {{-- Workflow Timeline --}}
        <div class="workflow-timeline">
            {{-- Concepts --}}
            <div class="timeline-item">
                <div class="timeline-icon bg-warning text-white"><i class="fa-solid fa-lightbulb"></i></div>
                <div class="timeline-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">Stage 1: Concept Writing</h6>
                        <a href="{{ route('concepts.project', $project) }}" class="btn btn-sm btn-outline-primary">View Concepts</a>
                    </div>
                    @forelse($project->conceptTasks as $ct)
                    <div class="d-flex align-items-center gap-3 p-2 border-bottom last-child-border-0">
                        <div style="flex:1;">
                            <div class="small fw-bold">{{ $ct->assignedTo->name }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $ct->concepts_required }} Concepts · {!! $ct->status_badge !!}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-muted small py-2">No concept tasks assigned yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Shoots --}}
            <div class="timeline-item">
                <div class="timeline-icon bg-info text-white"><i class="fa-solid fa-camera"></i></div>
                <div class="timeline-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">Stage 2: Shooting</h6>
                        <a href="{{ route('shoots.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-primary">Schedules</a>
                    </div>
                    @forelse($project->shootSchedules as $ss)
                    <div class="d-flex align-items-center gap-3 p-2 border-bottom last-child-border-0">
                        <div style="flex:1;">
                            <div class="small fw-bold">{{ $ss->location }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $ss->shoot_date->format('d M') }} · {!! $ss->status_badge !!}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-muted small py-2">No shoots scheduled yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Editing --}}
            <div class="timeline-item">
                <div class="timeline-icon bg-primary text-white"><i class="fa-solid fa-film"></i></div>
                <div class="timeline-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">Stage 3: Video Editing</h6>
                        <a href="{{ route('editing.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-primary">View Edits</a>
                    </div>
                    @forelse($project->editTasks as $et)
                    <div class="d-flex align-items-center gap-3 p-2 border-bottom last-child-border-0">
                        <div style="flex:1;">
                            <div class="small fw-bold">{{ $et->title }}</div>
                            <div class="progress progress-sm my-1"><div class="progress-bar" style="width:{{ $et->progress_percent }}%"></div></div>
                            <div class="text-muted" style="font-size:11px;">{!! $et->status_badge !!} · {{ $et->completed_count }}/{{ $et->total_videos }} videos</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-muted small py-2">No editing tasks assigned yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Details Sidebar --}}
    <div class="col-lg-4">
        <div class="panel-card mb-4">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-info-circle"></i> Project Info</h5>
            <div class="mb-3">
                <label class="form-label">Client Name</label>
                <div class="fw-bold">{{ $project->lead->customer_name ?? 'N/A' }}</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Assigned Manager</label>
                <div class="fw-bold">{{ $project->manager->name ?? 'Unassigned' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Start Date</label>
                    <div class="fw-bold">{{ $project->start_date ? $project->start_date->format('d M Y') : '—' }}</div>
                </div>
                <div class="col-6">
                    <label class="form-label">End Date</label>
                    <div class="fw-bold">{{ $project->end_date ? $project->end_date->format('d M Y') : '—' }}</div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <div class="fw-bold">{{ $project->lead->contact_number ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-money-bill-wave"></i> Budget Allocation</h5>
            <div class="mb-3">
                <div class="h3 fw-bold mb-0">₹{{ number_format($project->lead->total_meta_budget ?? 0) }}</div>
                <div class="text-muted small">Total Meta Budget</div>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">Client Side</span>
                <span class="small fw-bold">₹{{ number_format($project->lead->client_meta_budget ?? 0) }}</span>
            </div>
            <div class="progress progress-sm mb-3"><div class="progress-bar bg-info" style="width:{{ $project->lead->total_meta_budget > 0 ? ($project->lead->client_meta_budget / $project->lead->total_meta_budget * 100) : 0 }}%"></div></div>
            
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">Digital Yug Side</span>
                <span class="small fw-bold">₹{{ number_format($project->lead->dy_meta_budget ?? 0) }}</span>
            </div>
            <div class="progress progress-sm"><div class="progress-bar bg-primary" style="width:{{ $project->lead->total_meta_budget > 0 ? ($project->lead->dy_meta_budget / $project->lead->total_meta_budget * 100) : 0 }}%"></div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function activateProject(id) {
    Swal.fire({
        title: 'Activate Project',
        html: `<input type="date" id="sd" class="form-control" value="{{ date('Y-m-d') }}">`,
        showCancelButton: true,
        confirmButtonText: 'Activate Now',
        preConfirm: () => document.getElementById('sd').value || Swal.showValidationMessage('Date required')
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost(`/projects/${id}/activate`, { start_date: r.value }, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1500);
            });
        }
    });
}
</script>
@endpush

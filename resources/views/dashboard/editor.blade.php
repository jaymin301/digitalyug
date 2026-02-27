<div class="dashboard-overview">
    <div class="page-header">
        <h1 class="page-title">
            Hi {{ explode(' ', auth()->user()->name)[0] }}! 🎬
            <span class="page-subtitle">Your editing queue &amp; progress</span>
        </h1>
    </div>
    @php $pending = $myEdits->whereIn('status',['pending','in_progress','review'])->count(); $done = $myEdits->where('status','approved')->count(); @endphp
    <div class="stat-grid">
        <div class="stat-card"><div class="stat-icon bg-orange"><i class="fa-solid fa-spinner"></i></div><div class="stat-body"><div class="stat-value">{{ $pending }}</div><div class="stat-label">In Progress</div></div></div>
        <div class="stat-card"><div class="stat-icon bg-teal"><i class="fa-solid fa-circle-check"></i></div><div class="stat-body"><div class="stat-value">{{ $done }}</div><div class="stat-label">Approved</div></div></div>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h5 class="panel-card-title"><i class="fa-solid fa-film"></i> My Editing Tasks</h5>
            <a href="{{ route('editing.index') }}" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Title</th><th>Project</th><th>Progress</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($myEdits as $task)
                    <tr>
                        <td><strong>{{ $task->title }}</strong></td>
                        <td>{{ $task->project->name ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1"><div class="progress-bar" style="width:{{ $task->progress_percent }}%"></div></div>
                                <small>{{ $task->completed_count }}/{{ $task->total_videos }}</small>
                            </div>
                        </td>
                        <td>{!! $task->status_badge !!}</td>
                        <td><a href="{{ route('editing.show', $task) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No editing tasks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

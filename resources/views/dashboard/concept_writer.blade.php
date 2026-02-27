<div class="dashboard-overview">
    <div class="page-header">
        <h1 class="page-title">
            Hi {{ explode(' ', auth()->user()->name)[0] }}! ✍️
            <span class="page-subtitle">Your concept writing tasks</span>
        </h1>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h5 class="panel-card-title"><i class="fa-solid fa-lightbulb"></i> My Concept Tasks</h5>
            <a href="{{ route('concepts.index') }}" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Project</th><th>Required</th><th>Status</th><th>Due</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($myTasks as $task)
                    <tr>
                        <td><strong>{{ $task->project->name ?? 'N/A' }}</strong></td>
                        <td>{{ $task->concepts_required }}</td>
                        <td>{!! $task->status_badge !!}</td>
                        <td>{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                        <td><a href="{{ route('concepts.submit-form', $task) }}" class="btn btn-sm btn-primary">Submit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No tasks assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

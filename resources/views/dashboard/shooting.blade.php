<div class="dashboard-overview">
    <div class="page-header">
        <h1 class="page-title">
            Hi {{ explode(' ', auth()->user()->name)[0] }}! 📷
            <span class="page-subtitle">Your upcoming shoots &amp; schedules</span>
        </h1>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h5 class="panel-card-title"><i class="fa-solid fa-camera"></i> My Shoot Schedules</h5>
            <a href="{{ route('shoots.index') }}" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Date</th><th>Project</th><th>Location</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($myShoots as $shoot)
                    <tr>
                        <td>{{ $shoot->shoot_date->format('d M Y') }}</td>
                        <td>{{ $shoot->project->name ?? 'N/A' }}</td>
                        <td>{{ $shoot->location }}</td>
                        <td>{!! $shoot->status_badge !!}</td>
                        <td><a href="{{ route('shoots.show', $shoot) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No shoots assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

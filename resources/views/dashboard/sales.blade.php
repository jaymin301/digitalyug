<div class="dashboard-overview">
    <div class="page-header">
        <h1 class="page-title">
            Hey {{ explode(' ', auth()->user()->name)[0] }}! 👋
            <span class="page-subtitle">Your leads &amp; activity today</span>
        </h1>
    </div>
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fa-solid fa-handshake"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ auth()->user()->leads()->count() }}</div>
                <div class="stat-label">My Leads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ auth()->user()->leads()->where('status','converted')->count() }}</div>
                <div class="stat-label">Converted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fa-solid fa-phone"></i></div>
            <div class="stat-body">
                <div class="stat-value">{{ auth()->user()->leads()->where('status','contacted')->count() }}</div>
                <div class="stat-label">Follow-ups</div>
            </div>
        </div>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h5 class="panel-card-title"><i class="fa-solid fa-clock-rotate-left"></i> My Recent Leads</h5>
            <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>New Lead</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Date</th><th>Customer</th><th>Reels</th><th>Budget</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($myLeads as $lead)
                    <tr>
                        <td>{{ $lead->date->format('d M') }}</td>
                        <td><strong>{{ $lead->customer_name }}</strong><br><small class="text-muted">{{ $lead->contact_number }}</small></td>
                        <td>{{ $lead->total_reels }}</td>
                        <td>₹{{ number_format($lead->total_meta_budget) }}</td>
                        <td>{!! $lead->status_badge !!}</td>
                        <td><a href="{{ route('leads.show', $lead) }}" class="btn-action view"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No leads yet. <a href="{{ route('leads.create') }}">Create one!</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

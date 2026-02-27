@extends('layouts.panel')
@section('title', 'Notifications')
@section('breadcrumb')
    <li class="breadcrumb-item active">Notifications</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Notifications <span class="page-subtitle">Stay updated with task assignments and approvals</span></h1>
    <button class="btn btn-outline-primary" onclick="markAllRead()"><i class="fa-solid fa-check-double me-2"></i>Mark All as Read</button>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="panel-card p-0 overflow-hidden">
            <div class="list-group list-group-flush" id="notificationList">
                @forelse($notifications as $n)
                <div class="list-group-item list-group-item-action p-4 border-bottom {{ $n->read_at ? 'bg-white opacity-75' : 'bg-light-purple border-start border-4 border-purple' }}" id="notif_{{ $n->id }}">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon {{ $n->read_at ? 'bg-secondary' : 'bg-purple' }} shadow-sm" style="width:40px;height:40px;">
                                <i class="fa-solid {{ $n->icon ?? 'fa-bell' }}" style="font-size:16px;"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">{{ $n->title }}</h6>
                                <p class="mb-1 text-muted small">{{ $n->message }}</p>
                                <small class="text-secondary">{{ $n->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            @if($n->link)
                            <a href="{{ $n->link }}" class="btn btn-sm btn-primary rounded-pill px-3">View</a>
                            @endif
                            @if(!$n->read_at)
                            <button class="btn btn-link btn-sm text-secondary p-0" onclick="markAsRead({{ $n->id }})">Mark read</button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fa-solid fa-bell-slash fa-3x text-muted opacity-25 mb-3"></i>
                    <h5 class="text-muted">No notifications yet</h5>
                </div>
                @endforelse
            </div>
            
            @if($notifications->hasPages())
            <div class="p-3 border-top bg-white">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAsRead(id) {
    ajaxPost(`/notifications/${id}/read`, {}, function(res) {
        $(`#notif_${id}`).removeClass('bg-light-purple border-start border-4 border-purple').addClass('bg-white opacity-75').find('button').remove();
        updateNotificationCount();
    });
}
function markAllRead() {
    ajaxPost('/notifications/mark-all-read', {}, function(res) {
        showSuccess(res.message);
        setTimeout(() => location.reload(), 1000);
    });
}
</script>
<style>
.bg-light-purple { background-color: rgba(108,63,197, 0.03); }
.border-purple { border-color: #6c3fc5 !important; }
</style>
@endpush

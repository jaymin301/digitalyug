@extends('layouts.panel')
@section('title', 'Shoot Schedules')
@section('breadcrumb')
    <li class="breadcrumb-item active">Shoots</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Shooting Schedules <span class="page-subtitle">Manage on-location production</span></h1>
    @role('Admin|Manager')
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Schedule Shoot</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">Select Project</li>
            @foreach(\App\Models\Project::whereNotIn('stage',['completed','pending'])->get() as $p)
            <li><a class="dropdown-item" href="{{ route('shoots.create', $p) }}">{{ $p->name }}</a></li>
            @endforeach
        </ul>
    </div>
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-camera"></i> Shoot Pipeline</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="shootsTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Date</th><th>Project</th><th>Location</th>
                <th>Shooter</th><th>Status</th><th>Duration</th><th>Reels</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($shoots as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $s->shoot_date->format('d M Y') }}</strong></td>
                    <td>{{ $s->project->name ?? 'N/A' }}</td>
                    <td><span style="font-size:12px;">{{ Str::limit($s->location, 30) }}</span></td>
                    <td>{{ $s->shootingPerson->name ?? 'N/A' }}</td>
                    <td>{!! $s->status_badge !!}</td>
                    <td>{{ $s->duration ?? '—' }}</td>
                    <td>{{ $s->reels_shot }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('shoots.show', $s) }}" class="btn-action view" title="View/Check-in"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                            <button class="btn-action delete btn-delete" data-url="{{ route('shoots.destroy', $s) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endrole
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#shootsTable').DataTable({ responsive: true, pageLength: 15, order: [[1,'desc']] });
});
</script>
@endpush

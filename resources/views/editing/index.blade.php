@extends('layouts.panel')
@section('title', 'Editing Tasks')
@section('breadcrumb')
    <li class="breadcrumb-item active">Editing</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Video Editing <span class="page-subtitle">Manage post-production and approvals</span></h1>
    @role('Admin|Manager')
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectSelectModal">
            <i class="fa-solid fa-film me-2"></i>Assign Editing
        </button>
    {{-- <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Assign Editing</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">Select Project</li>
            @foreach(\App\Models\Project::whereNotIn('stage',['completed','pending'])->orderBy('created_at', 'desc')->get() as $p)
                <li><a class="dropdown-item" href="{{ route('editing.assign-form', $p) }}">{{ $p->name }}</a></li>
            @endforeach
        </ul>
    </div> --}}
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-film"></i> Editing Queue</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="editingTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Task Title</th><th>Project</th><th>Editor</th>
                <th>Concept</th><th>Progress</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($tasks as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $t->title }}</strong></td>
                    <td>{{ $t->project->name ?? 'N/A' }}</td>
                    <td>{{ $t->assignedTo->name ?? 'N/A' }}</td>
                    <td><span style="font-size:12px;">{{ $t->concept->title ?? 'General' }}</span></td>
                    <td style="min-width:120px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1"><div class="progress-bar" style="width:{{ $t->progress_percent }}%"></div></div>
                            <span class="small fw-bold">{{ $t->completed_count }}/{{ $t->total_videos }}</span>
                        </div>
                    </td>
                    <td>{!! $t->status_badge !!}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('editing.show', $t) }}" class="btn-action view" title="View/Update"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                            <button class="btn-action delete btn-delete" data-url="{{ route('editing.destroy', $t) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endrole
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@role('Admin|Manager')
<div class="modal fade" id="projectSelectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15);">

            <div class="modal-header" style="border-bottom:1px solid #f0f2f8;padding:20px 24px;">
                <div>
                    <h5 class="modal-title fw-bold mb-1">
                        <i class="fa-solid fa-film me-2 text-primary"></i>Assign Editing Task
                    </h5>
                    <p class="text-muted small mb-0">Select a project to assign editing for</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Search --}}
                <div class="mb-3">
                    <input type="text" id="editProjectSearch" class="form-control"
                        placeholder="Search project..."
                        style="border-radius:10px;">
                </div>

                {{-- Project list --}}
                <div id="editProjectList" style="max-height:380px;overflow-y:auto;overflow-x:hidden;padding-right:4px;">
                    @forelse(\App\Models\Project::with('lead','approvedConcepts')->whereNotIn('stage', ['completed', 'pending'])->latest()->get() as $p)
                        <a href="{{ route('editing.assign-form', $p) }}"
                            class="edit-project-select-item d-flex align-items-center gap-3 p-3 rounded-3 mb-2 text-decoration-none"
                            style="border:1px solid #f0f2f8;transition:all 0.15s;overflow:hidden;"
                            data-name="{{ strtolower($p->name) }}">

                            {{-- Icon --}}
                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                style="width:40px;height:40px;background:rgba(108,63,197,0.08);">
                                <i class="fa-solid fa-folder-open" style="color:#6c3fc5;font-size:16px;"></i>
                            </div>

                            {{-- Info --}}
                            <div style="flex:1;min-width:0;overflow:hidden;">
                                <div class="fw-semibold text-dark text-truncate" style="font-size:14px;">{{ $p->name }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                    <span class="text-muted text-truncate" style="font-size:12px;max-width:140px;">
                                        <i class="fa-solid fa-user me-1"></i>{{ $p->lead->customer_name ?? 'N/A' }}
                                    </span>
                                    <span style="font-size:11px;padding:2px 8px;border-radius:10px;white-space:nowrap;
                                        @if($p->stage === 'shooting') background:rgba(247,183,49,0.12);color:#c67c00;
                                        @elseif($p->stage === 'concept') background:rgba(69,170,242,0.12);color:#1a6fa3;
                                        @elseif($p->stage === 'editing') background:rgba(108,63,197,0.12);color:#6c3fc5;
                                        @else background:#f0f2f8;color:#718096;
                                        @endif">
                                        {{ ucfirst($p->stage) }}
                                    </span>
                                    @if($p->approvedConcepts && $p->approvedConcepts->count() > 0)
                                    <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(38,222,129,0.12);color:#1a8754;white-space:nowrap;">
                                        <i class="fa-solid fa-check me-1"></i>{{ $p->approvedConcepts->count() }} concepts
                                    </span>
                                    @else
                                    <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(235,77,75,0.1);color:#eb4d4b;white-space:nowrap;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>No concepts
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <i class="fa-solid fa-chevron-right text-muted flex-shrink-0" style="font-size:11px;"></i>
                        </a>
                    @empty
                        <div class="text-center py-5">
                            <i class="fa-solid fa-folder-open fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <p class="text-muted">No active projects available.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>
@endrole

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#editingTable').DataTable({ 
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Projects or Task Titles...",
        },
        responsive: true, 
        pageLength: 10, 
    });

      // Client-side search
    $('#editProjectSearch').on('input', function() {
        const q = $(this).val().toLowerCase();
        $('.edit-project-select-item').each(function() {
            const name = $(this).data('name');
            if (name.includes(q)) {
                $(this).css('display', 'flex');
            } else {
                this.style.setProperty('display', 'none', 'important');
            }
        });
    });

    // Hover effects
    $(document).on('mouseenter', '.edit-project-select-item', function() {
        $(this).css({
            'background': '#fafbff',
            'border-color': '#6c3fc5',
            'transform': 'translateX(3px)'
        });
    }).on('mouseleave', '.edit-project-select-item', function() {
        $(this).css({
            'background': '',
            'border-color': '#f0f2f8',
            'transform': ''
        });
    });

    // Reset on close
    $('#projectSelectModal').on('hidden.bs.modal', function() {
        $('#editProjectSearch').val('');
        $('.edit-project-select-item').show();
    });
});
</script>
@endpush

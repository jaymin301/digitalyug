@extends('layouts.panel')

@section('title', 'Agencies')

@section('breadcrumb')
    <li class="breadcrumb-item active">Agencies</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Agency Management</h5>
        <a href="{{ route('agencies.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-plus me-2"></i>Create Agency
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="agenciesTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Agency Name</th>
                        <th>Owner Name</th>
                        <th>Contact</th>
                        <th>Remark</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agencies as $agency)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark">{{ $agency->name }}</div>
                        </td>
                        <td>{{ $agency->owner_name }}</td>
                        <td>{{ $agency->contact }}</td>
                        <td>
                            <span class="text-muted small">{{ Str::limit($agency->remark, 50) ?: '--' }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('agencies.edit', $agency) }}" class="btn btn-sm btn-light-primary" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-light-danger btn-delete" 
                                        data-url="{{ route('agencies.destroy', $agency) }}" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#agenciesTable').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search agencies...",
            },
            pageLength: 10,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: 4 }
            ]
        });
    });
</script>
@endpush

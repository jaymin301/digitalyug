@extends('layouts.panel')

@section('title', 'Edit Agency')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agencies.index') }}" class="text-decoration-none">Agencies</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-white py-3" style="border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold">Edit Agency: {{ $agency->name }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('agencies.update', $agency) }}" method="POST">
                    @method('PUT')
                    @include('agencies._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

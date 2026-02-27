@extends('layouts.panel')
@section('title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection
@section('content')
{{-- Role-based dashboard routing --}}
@if(auth()->user()->hasRole(['Admin','Manager']))
    @include('dashboard.admin')
@elseif(auth()->user()->hasRole('Sales Executive'))
    @include('dashboard.sales')
@elseif(auth()->user()->hasRole('Concept Writer'))
    @include('dashboard.concept_writer')
@elseif(auth()->user()->hasRole('Shooting Person'))
    @include('dashboard.shooting')
@elseif(auth()->user()->hasRole('Video Editor'))
    @include('dashboard.editor')
@endif
@endsection

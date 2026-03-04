@extends('layouts.panel')

@section('title', 'Shooting Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Shooting Dashboard</li>
@endsection

@section('content')
    @include('dashboard.shooting')
@endsection

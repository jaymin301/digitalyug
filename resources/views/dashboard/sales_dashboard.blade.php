@extends('layouts.panel')

@section('title', 'Sales Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Dashboard</li>
@endsection

@section('content')
    @include('dashboard.sales')
@endsection

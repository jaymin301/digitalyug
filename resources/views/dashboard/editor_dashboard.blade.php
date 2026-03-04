@extends('layouts.panel')

@section('title', 'Editor Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Editor Dashboard</li>
@endsection

@section('content')
    @include('dashboard.editor')
@endsection

@extends('layouts.panel')
@section('title', 'Profile')
@section('breadcrumb')
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Profile <span class="page-subtitle">Manage your account settings</span></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="panel-card mb-4">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="panel-card mb-4">
            @include('profile.partials.update-password-form')
        </div>

        <div class="panel-card mb-4 border-danger border-top border-4">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection

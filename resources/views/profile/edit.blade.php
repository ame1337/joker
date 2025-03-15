@extends('layouts.app')

@section('nav')
@include('layouts.nav')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-4">
            @include('profile.partials.user-info')
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@if (session('status'))
<flash :message="{{ json_encode(session('status')) }}" :type="{{ json_encode('alert-success') }}"></flash>
@endif
@endsection
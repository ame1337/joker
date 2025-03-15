@extends('layouts.app')

@section('nav')
@include('layouts.nav')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-4">
            @include('profile.partials.user-info')
        </div>
    </div>
</div>
@endsection
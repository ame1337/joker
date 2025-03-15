@extends('layouts.app')

@section('nav')
@include('layouts.nav')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">

        <div class="col-md-8">
            <div class="p-3">
                <main class="text-center">
                    <h1><img src="{{ asset('storage/imgs/logo.png') }}" alt="logo"></h1>
                    <div class="fs-5 mb-3 fw-bold">@lang('Welcome on Joker portal! Game on and GLHF!')</div>
                    <p>
                        <a href="{{ route('lobby') }}" class="btn btn-lg btn-primary border">
                            <i class="fa-solid fa-play"></i> @lang('Play Now')
                        </a>
                    </p>
                </main>
            </div>
        </div>
    </div>
</div>
@endsection
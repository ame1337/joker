@extends('layouts.app')

@section('nav')
@include('layouts.nav')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card p-3">
                <div class="card-body p-4 pb-3">
                    <div class="card-title text-center">
                        <h1 class="fw-bold mb-0 fs-5">
                            <i class="fa-solid fa-user-plus"></i>
                            @lang('Sign up') @lang('to Joker')
                        </h1>
                    </div>
                </div>

                <div class="card-body p-4 pt-0">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">@lang('Username')</label>
                            <input id="username" type="text" 
                                class="form-control rounded-3 @error('username') is-invalid 
                                @enderror" name="username" 
                                value="{{ old('username') }}" required autofocus autocomplete="username">

                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">@lang('Email')</label>
                            <input id="email" type="email" 
                                class="form-control rounded-3 @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autocomplete="email">

                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">@lang('Password')</label>
                            <input id="password" type="password"
                                class="form-control rounded-3 @error('password') is-invalid @enderror" 
                                name="password" required autocomplete="new-password">
                            <small class="form-text">@lang('Min 8 characters')</small>

                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">@lang('Confirm password')</label>
                            <input id="password-confirm" type="password" class="form-control rounded-3"
                                name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <button class="w-100 mb-2 btn rounded-3 btn-success" type="submit">
                            @lang('Sign up')
                        </button>

                        <div class="text-center">
                            <a class="btn btn-link" href="{{ route('login') }}">
                                <small>@lang('Already registered?')</small>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
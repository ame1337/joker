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
                            <i class="fa-solid fa-right-to-bracket"></i>
                            @lang('Sign in') @lang('to Joker')
                        </h1>
                    </div>
                </div>

                <div class="card-body p-4 pt-0">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">@lang('Email')</label>
                            <input id="email" type="email" class="form-control rounded-3 @error('email') is-invalid 
                                @enderror" name="email" autocomplete="email"
                                value="{{ old('email') }}" required autofocus>

                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">@lang('Password')</label>
                            <input id="password" type="password" class="form-control rounded-3 @error('password') is-invalid 
                                @enderror" name="password" required autocomplete="current-password">

                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember"
                                id="remember_me" {{ old('remember') ? 'checked' : '' }}>

                            <label class="form-check-label" for="remember_me">
                                @lang('Remember me')
                            </label>
                        </div>
                        <button class="w-100 btn rounded-3 btn-success" type="submit">
                            @lang('Sign in')
                        </button>
                    </form>
                    <hr>
                    <div>
                        <a id="btn-fb" href="/login/facebook" class="w-100 py-2 mb-2 btn btn-outline-primary rounded-3">
                            <i class="fab fa-facebook-f"></i> @lang('Sign in with Facebook')
                        </a>
                    </div>
                    <div>
                        <a href="/login/google" class="w-100 py-2 mb-2 btn btn-outline-danger rounded-3">
                            <i class="fab fa-google"></i> @lang('Sign in with Google')</a>
                        </a>
                    </div>

                    <div class="text-center">
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            <small>@lang('Forgot password?')</small>
                        </a>
                        <a class="btn btn-link" href="{{ route('register') }}">
                            <small>@lang('Sign up')</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
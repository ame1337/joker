@extends('layouts.app')

@section('nav')
@include('layouts.nav')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-body p-4 pb-3">
                    <div class="card-title text-center">
                        <h1 class="fw-bold mb-0 fs-5">
                            <i class="fa-solid fa-unlock"></i>
                            @lang('Reset password')
                        </h1>
                    </div>
                </div>

                <div class="card-body p-4 pt-0">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">@lang('Email')</label>

                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ $email ?? old('email') }}" required>

                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">@lang('Password')</label>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                required>
                            <small class="form-text">@lang('Min 8 characters')</small>

                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">@lang('Confirm password')</label>
                            <input id="password-confirm" type="password" class="form-control"
                                name="password_confirmation" required>
                        </div>

                        <button type="submit" class="w-100 btn btn-block btn-success">
                            @lang('Reset')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
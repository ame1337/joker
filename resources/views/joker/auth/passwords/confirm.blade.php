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
                            <i class="fas fa-lock"></i> {{ __('Confirm password') }}
                        </h1>
                    </div>
                </div>

                <div class="card-body p-4 pt-0">
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" 
                                class="form-control rounded-3 @error('password') is-invalid @enderror" 
                                name="password" required>

                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="w-100 mb-2 btn rounded-3 btn-success" type="submit">
                            {{ __('Confirm') }}
                        </button>

                        <div class="text-center">
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                <small>{{ __('Forgot password?') }}</small>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
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
                            <i class="fa-solid fa-unlock-keyhole"></i>
                            @lang('Reset password')
                        </h1>
                    </div>
                </div>

                <div class="card-body p-4 pt-0">
                    @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">@lang('Email')</label>
                            <input id="email" type="email" 
                                class="form-control rounded-3 @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required>

                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="w-100 mb-2 btn rounded-3 btn-success" type="submit">
                            @lang('Send email')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
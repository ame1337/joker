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
                            <i class="fas fa-at"></i> @lang('Change email')
                        </h1>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="/email/change">
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


                        <button type="submit" class="w-100 btn btn-block btn-success rounded-3">
                            @lang('Confirm')
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
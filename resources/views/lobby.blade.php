@extends('layouts.app')

@section('nav')
@include('layouts.nav')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
    <div class="col-md-8">
    <div class="card">
        <div class="card-header">
            <strong>@lang('Tables')</strong>
            <button type="button"
                class="btn btn-primary float-end"
                data-bs-toggle="modal"
                data-bs-target="#new_table">
                <i class="fa-solid fa-diamond"></i> @lang('New table')
            </button>
        </div>
        <lobby :initial-games="{{ json_encode($games)  }}"></lobby>
    </div>
</div></div>
</div>
<div class="modal fade" id="new_table" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fw-bold fs-5" id="staticBackdropLabel">
                    <i class="fa-solid fa-diamond"></i> @lang('New table')
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/games" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="type" class="form-label">@lang('Type')</label>
                        <select id="type" class="form-select rounded-3" name="type">
                            <option value="1" selected>@lang('Standard')</option>
                            <option value="9">@lang('Only 9')</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="penalty" class="form-label">@lang('Penalty')</label>
                        <select id="penalty" class="form-select rounded-3" name="penalty">
                            <option value="-200" selected>-200</option>
                            <option value="-300">-300</option>
                            <option value="-400">-400</option>
                            <option value="-500">-500</option>
                            <option value="-900">-900</option>
                            <option value="-1000">-1000</option>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="pwd" name="password">
                        <label class="form-check-label" for="pwd">@lang('Pin code')</label>
                    </div>
                    <button class="btn btn-success btn-block float-end rounded-3" onclick="Echo.leave('lobby')">
                        @lang('Create table')
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
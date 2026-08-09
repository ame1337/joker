@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/cards.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div id="game-view" data-game-id="{{ json_encode($id) }}"
               data-has-password="{{ json_encode($password) }}"
               data-pin-code="{{ json_encode($pin) }}"
               data-is_bot_disabled="{{ json_encode($bot_disabled) }}"
               data-init_bot_timer="{{ json_encode((int) $bot_timer) }}"></div>

    @if(Auth::user()->isAdmin)
        <div id="_admin-panel" data-game-id="{{ $id }}"></div>
    @endif

    <div id="theme-changer" class="position-fixed start-0 bottom-0 ms-1 mb-1">
        @include('layouts.theme')
    </div>
@endsection

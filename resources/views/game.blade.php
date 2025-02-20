@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/cards.css') }}" rel="stylesheet">
@endsection

@section('content')
    <game-view :game-id="{{ json_encode($id) }}"
               :has-password="{{ json_encode($password) }}"
               :pin-code="{{ json_encode($pin) }}"
               :is_bot_disabled="{{ json_encode($bot_disabled) }}"
               :init_bot_timer="{{ json_encode((int) $bot_timer) }}"></game-view>

    @if(Auth::user()->isAdmin)
        <admin-panel :game-id="{{ $id }}"></admin-panel>
    @endif

    <div id="theme-changer" class="position-fixed start-0 bottom-0 ms-1 mb-1">
        <theme-changer></theme-changer>
    </div>
@endsection

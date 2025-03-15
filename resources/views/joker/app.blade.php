<!doctype html>
<html lang="{{ App::getLocale() }}" data-bs-theme="green">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Joker') }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon" sizes="16x16">
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script>
        window.App = {!! json_encode([
            'user' => Auth::user(),
            'url' => env('APP_URL'),
            'locale' => App::getLocale()
        ]) !!};
    </script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('style')
</head>
<body>
    @include('layouts.theme')

    <div id="app">
        @yield('nav')


        @yield('content')
    </div>
</body>
</html>
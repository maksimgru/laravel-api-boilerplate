<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>
        @isset($title)
            {{ $title }} |
        @endisset
        {{ config('app.name') }}
    </title>
    <!-- JWT Token -->
    <meta name="jwt-token" content="{{ jwtToken() }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Styles -->
    <link href="{{ mix('css/vendors.css') }}" rel="stylesheet">
    <link href="{{ mix('css/custom.css') }}" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
</head>

<body
    class="sb-nav-fixed @guest bg-dark @endguest"
    data-lang="{{ app()->getLocale() }}"
>
    @auth
        @yield('layout-auth')
    @endauth
    @guest
        @yield('layout-guest')
    @endguest

    @includeIf('layouts.includes.modal')

    <!-- Scripts -->
    <script src="{{ mix('js/vendors.js') }}"></script>
    <script>window.LOYALTY = {}; // Global Namespace</script>
    <script>window.LOYALTY['language'] = {!! getLangFileContent() ?: '{}' !!};</script>
    <script src="{{ mix('js/custom.js') }}"></script>
</body>

</html>


@extends('layouts.app')

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('role_constants', 'App\Constants\RoleConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@section('layout-auth')
    @includeIf('layouts.includes.navbar-top', ['route_constants' => $route_constants, 'role_constants' => $role_constants])
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            @includeIf('layouts.includes.navbar-side', ['route_constants' => $route_constants, 'role_constants' => $role_constants])
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    @includeWhen(session('status'), 'layouts.includes.alert', ['message' => session('status')])
                    @includeIf('layouts.includes.breadcrumbs', ['breadcrumbs' => [], 'route_constants' => $route_constants, 'role_constants' => $role_constants])
                    @yield('content')
                </div>
            </main>
            @includeIf('layouts.includes.footer', ['route_constants' => $route_constants, 'role_constants' => $role_constants])
        </div>
    </div>
@endsection

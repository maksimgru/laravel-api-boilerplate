@extends('layouts.auth', ['title' => $list_page_title ?? __('Settings')])

@inject('route_constants', 'App\Constants\RouteConstants')

@section('content')

    <div class="row mt-4">
        <div class="col-md-9">
            <h1><i class=" fa fa-gears"></i> {{ $list_page_title ?? __('Settings') }}</h1>
        </div>
    </div>

    {{-- TABLES --}}
    @includeIf('layouts.includes.tables.table-settings', [
        'route_constants' => $route_constants,
    ])

@endsection

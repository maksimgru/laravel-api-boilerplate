@extends('layouts.auth', ['title' => $list_page_title ?? __('Roles')])

@inject('route_constants', 'App\Constants\RouteConstants')

@section('content')

    <div class="row mt-4">
        <div class="col-md-9">
            <h1><i class=" fa fa-lock"></i> {{ $list_page_title ?? __('Roles') }}</h1>
        </div>
    </div>

{{-- TABLES --}}
@includeIf('layouts.includes.tables.table-roles', [
    'route_constants'     => $route_constants,
    'table_title'         => $list_page_title,
    'table_load_data_url' => $table_load_data_url,
    'default_order'       => $default_order ?? null,
])

@endsection

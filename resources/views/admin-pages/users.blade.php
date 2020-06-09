@extends('layouts.auth', ['title' => $users_list_page_title ?? __('Users')])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('role_constants', 'App\Constants\RoleConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@section('content')

<div class="row mt-4">
    <div class="col-md-9">
        <h1><i class=" fa fa-user"></i> {{ $users_list_page_title }}</h1>
    </div>

    @if($add_new_user_btn_label && $add_new_user_btn_href)
        <div class="col-md-3">
            <a href="{{ $add_new_user_btn_href }}" class="btn btn-primary float-right mb-2">
                {{ $add_new_user_btn_label }}
            </a>
        </div>
    @endif

</div>

{{-- TABLES --}}
@includeIf('layouts.includes.tables.table-users', [
    'route_constants'     => $route_constants,
    'role_constants'      => $role_constants,
    'media_constants'     => $media_constants,
    'table_title'         => $users_list_page_title,
    'table_load_data_url' => $table_load_data_url,
    'default_order'       => $default_order,
    'url_view_row'        => $url_view_row ?? '',
    'url_restore_row'     => $url_restore_row ?? '',
    'url_delete_row'      => $url_delete_row ?? '',
])

@endsection

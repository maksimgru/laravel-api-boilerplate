@extends('layouts.auth', ['title' => __('Trash')])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('role_constants', 'App\Constants\RoleConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@php
    $user_options = $user_options ?? [];
@endphp

@section('content')

<h1 class="mt-4">{{ __('Trash') }}</h1>

<h2 class="mt-4"><i class=" fa fa-user"></i> {{ $user_options['table_title'] ?? null }}</h2>
@includeIf('layouts.includes.tables.table-users', [
    'route_constants'     => $route_constants,
    'role_constants'      => $role_constants,
    'media_constants'     => $media_constants,
    'table_title'         => $user_options['table_title'] ?? null,
    'table_load_data_url' => $user_options['table_load_data_url'] ?? null,
    'default_order'       => $user_options['default_order'] ?? null,
    'url_view_row'        => $user_options['url_view_row'] ?? null,
    'url_restore_row'     => $user_options['url_restore_row'] ?? null,
    'url_delete_row'      => $user_options['url_delete_row'] ?? null,
])

<h2 class="mt-4"><i class=" fa fa-map-marker"></i> {{ $visit_place_options['table_title'] ?? null }}</h2>
@includeIf('layouts.includes.tables.table-visit-places', [
    'route_constants'           => $route_constants,
    'media_constants'           => $media_constants,
    'table_title'               => $visit_place_options['table_title'] ?? null,
    'table_load_data_url'       => $visit_place_options['table_load_data_url'] ?? null,
    'default_order'             => $visit_place_options['default_order'] ?? null,
    'url_view_row'              => $visit_place_options['url_view_row'] ?? null,
    'url_restore_row'           => $visit_place_options['url_restore_row'] ?? null,
    'url_delete_row'            => $visit_place_options['url_delete_row'] ?? null,
    'url_visit_place_category'  => $visit_place_options['url_visit_place_category'] ?? null,
    'url_business_user_profile' => $visit_place_options['url_business_user_profile'] ?? null,
])

<h2 class="mt-4"><i class=" fa fa-comment"></i> {{ $visit_place_comment_options['table_title'] ?? null }}</h2>
@includeIf('layouts.includes.tables.table-visit-place-comments', [
    'route_constants'           => $route_constants,
    'media_constants'           => $media_constants,
    'table_title'               => $visit_place_comment_options['table_title'] ?? null,
    'table_load_data_url'       => $visit_place_comment_options['table_load_data_url'] ?? null,
    'default_order'             => $visit_place_comment_options['default_order'] ?? null,
    'url_view_row'              => $visit_place_comment_options['url_view_row'] ?? null,
    'url_restore_row'           => $visit_place_comment_options['url_restore_row'] ?? null,
    'url_delete_row'            => $visit_place_comment_options['url_delete_row'] ?? null,
    'url_visit_place'           => $visit_place_comment_options['url_visit_place'] ?? null,
    'url_user_profile'          => $visit_place_comment_options['url_user_profile'] ?? null,
])

<h2 class="mt-4"><i class=" fa fa-pencil"></i> {{ $page_options['table_title'] ?? null }}</h2>
@includeIf('layouts.includes.tables.table-pages', [
    'route_constants'     => $route_constants,
    'media_constants'     => $media_constants,
    'table_title'         => $page_options['table_title'] ?? null,
    'table_load_data_url' => $page_options['table_load_data_url'] ?? null,
    'default_order'       => $page_options['default_order'] ?? null,
    'url_view_row'        => $page_options['url_view_row'] ?? null,
    'url_restore_row'     => $page_options['url_restore_row'] ?? null,
    'url_delete_row'      => $page_options['url_delete_row'] ?? null,
])

@endsection

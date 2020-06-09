@extends('layouts.auth', ['title' => $list_page_title ?? __('Pages')])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@section('content')

    <div class="row mt-4">
        <div class="col-md-9">
            <h1><i class=" fa fa-pencil"></i> {{ $list_page_title ?? __('Pages') }}</h1>
        </div>

        @if($add_new_model_btn_label && $add_new_model_btn_href)
            <div class="col-md-3">
                <a href="{{ $add_new_model_btn_href }}" class="btn btn-primary float-right mb-2">
                    {{ $add_new_model_btn_label }}
                </a>
            </div>
        @endif

    </div>

{{-- TABLES --}}
@includeIf('layouts.includes.tables.table-pages', [
    'route_constants'           => $route_constants,
    'media_constants'           => $media_constants,
    'table_title'               => $list_page_title,
    'table_load_data_url'       => $table_load_data_url,
    'default_order'             => $default_order ?? null,
    'url_view_row'              => $url_view_row ?? '',
    'url_restore_row'           => $url_restore_row ?? '',
    'url_delete_row'            => $url_delete_row ?? '',
])

@endsection

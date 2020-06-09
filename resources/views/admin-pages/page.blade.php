@extends('layouts.auth', ['title' => $page['title']])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@php
    $main_image_id = $page['media']['main_image']['id'];
    $main_image_url = $page['media']['main_image']['thumbs']['thumb-large'];
    $main_image_link_url = $main_image_id ? $page['media']['main_image']['origin'] : '';

    $main_image_form_enctype = $main_image_id ? 'application/x-www-form-urlencoded' : 'multipart/form-data';
    $main_image_form_action_url = $main_image_id
        ? route($route_constants::ROUTE_NAME_WEB_DELETE_PAGE_MEDIA, ['media' => $main_image_id])
        : route($route_constants::ROUTE_NAME_WEB_PAGE_UPDATE, ['page' => $page['id']])
    ;
    $main_image_form_action_url = $can_edit ? $main_image_form_action_url : '';
    $gallery = $page['media']['gallery'];

    $form_action_url = $can_edit
        ? route($route_constants::ROUTE_NAME_WEB_PAGE_UPDATE, ['page' => $page['id']])
        : ''
    ;
@endphp

@section('content')

<h1 class="mt-4">{{ $page['title'] }}</h1>

{{-- EDIT FORM --}}
<div id="page-{{ $page['id'] }}" class="entity-form entity-page mb-5">

    {{-- FORM DATA --}}
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card">
                <form id="form-visit-place-{{ $page['id'] }}"
                      class="form"
                      method="POST"
                      data-loader-type="spinner"
                      data-btn-submit-selector=".l-submit"
                      action="{{ $form_action_url }}">
                    @csrf
                    <div class="card-header">
                        <h4 class="card-title">{{ __('Edit Page ID#:num', ['num' => $page['id']]) }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-input', [
                                        'label'    => __('Title'),
                                        'name'     => 'title',
                                        'value'    => $page['title'],
                                        'required' => true,
                                        'disabled' => !$can_edit,
                                    ])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-input', [
                                        'label'       => __('Slug'),
                                        'name'        => 'slug',
                                        'value'       => $page['slug'],
                                        'required'    => false,
                                        'disabled'    => !$can_edit,
                                        'placeholder' => __('from Title by default'),
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-textarea', [
                                        'label'         => __('Content'),
                                        'name'          => 'content',
                                        'value'         => $page['content'],
                                        'disabled'      => !$can_edit,
                                        'rows'          => 10,
                                        'placeholder'   => __('Here can be content'),
                                        'addon'         => false,
                                        'input_classes' => 'form-control js-ckeditor',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-input', [
                                        'label'              => __('Created At'),
                                        'type'               => 'input',
                                        'name'               => 'created_at',
                                        'value'              => \Carbon\Carbon::parse($page['created_at'])->format(config('formats.datetime')),
                                        'pattern'            => '\d{4}-\d{2}-\d{2}',
                                        'disabled'           => true,
                                        'addon_icon_classes' => 'fa fa-calendar',
                                    ])
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-input', [
                                        'label'              => __('Updated At'),
                                        'type'               => 'input',
                                        'name'               => 'updated_at',
                                        'value'              => \Carbon\Carbon::parse($page['updated_at'])->format(config('formats.datetime')),
                                        'pattern'            => '\d{4}-\d{2}-\d{2}',
                                        'disabled'           => true,
                                        'addon_icon_classes' => 'fa fa-calendar',
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        @if($can_edit)
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary float-left l-submit">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        @endif
                        <div class="clearfix"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- IMAGES --}}
    <div class="row mb-5">
        {{-- MAIN IMAGE --}}
        <div class="col-md-4 mb-4">
            <div class="card card-visit-place main-image">
                <div class="card-body">
                    @includeIf('layouts.includes.form.form-single-media', [
                        'media_id'         => $main_image_id,
                        'media_url'        => $main_image_url,
                        'link_url'         => $main_image_link_url,
                        'name'             => $media_constants::REQUEST_FIELD_NAME_MAIN_IMAGE,
                        'enctype'          => $main_image_form_enctype,
                        'action'           => $main_image_form_action_url,
                        'form_id'          => $main_image_id ? 'media-' . $main_image_id : null,
                        'btn_upload_label' => __('Upload Main Image'),
                        'modal_selector'   => '#modal-media',
                        'modal_body'       => htmlspecialchars(sprintf('<img src="%1$s" class="w-100">', $main_image_link_url), ENT_QUOTES),
                    ])
                </div>
            </div>
        </div>

        {{-- GALLERY --}}
        <div class="col-md-8 mb-4">
            {{-- CAROUSEL --}}
            <div class="mb-2 wrap-gallery js-owl-carousel owl-carousel owl-theme">
                @foreach($gallery as $gallery_item)
                    <div class="card gallery-item">
                        @includeIf('layouts.includes.form.form-single-media', [
                            'media_id'       => $gallery_item['id'],
                            'media_url'      => $gallery_item['thumbs']['thumb-medium'],
                            'link_url'       => $gallery_item['origin'],
                            'name'           => $media_constants::REQUEST_FIELD_NAME_GALLERY . '[]',
                            'enctype'        => 'application/x-www-form-urlencoded',
                            'action'         => $can_edit ? route($route_constants::ROUTE_NAME_WEB_DELETE_PAGE_MEDIA, ['media' => $gallery_item['id']]) : '',
                            'form_id'        => 'media-' . $gallery_item['id'],
                            'modal_selector' => '#modal-media',
                            'modal_body'     => htmlspecialchars(sprintf('<img src="%1$s" class="w-100">', $gallery_item['origin']), ENT_QUOTES),
                        ])
                    </div>
                @endforeach
            </div>
            {{-- UPLOAD btn --}}
            <div class="col-xl-12 text-center">
                @includeIf('layouts.includes.form.form-single-media', [
                    'media_url'        => config('medialibrary.placeholder_image_path'),
                    'name'             => $media_constants::REQUEST_FIELD_NAME_GALLERY . '[]',
                    'error_key'        => $media_constants::REQUEST_FIELD_NAME_GALLERY . '.0',
                    'enctype'          => 'multipart/form-data',
                    'action'           => $form_action_url,
                    'btn_upload_label' => __('Upload to gallery'),
                    'as_btn_upload'    => true,
                ])
            </div>
        </div>
    </div>
</div>

@endsection

@extends('layouts.auth', ['title' => __('New Page')])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@php
    $form_action_url = route($route_constants::ROUTE_NAME_WEB_PAGE_CREATE_NEW);
@endphp

@section('content')

<h1 class="mt-4">{{ __('New Page') }}</h1>

{{-- EDIT FORM --}}
<div id="new-visit-place" class="entity-form entity-visit-place mb-5">

    {{-- FORM DATA --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <form id="form-visit-place-new"
                      class="form"
                      method="POST"
                      data-loader-type="spinner"
                      data-btn-submit-selector=".l-submit"
                      action="{{ $form_action_url }}">
                    @csrf
                    <div class="card-header">
                        <h4 class="card-title">{{ __('New Page') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-input', [
                                        'label'    => __('Title'),
                                        'name'     => 'title',
                                        'required' => true,
                                        'disabled' => !$can_create,
                                    ])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-input', [
                                        'label'       => __('Slug'),
                                        'name'        => 'slug',
                                        'required'    => false,
                                        'disabled'    => !$can_create,
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
                                        'disabled'      => !$can_create,
                                        'rows'          => 10,
                                        'placeholder'   => __('Here can be content'),
                                        'addon'         => false,
                                        'input_classes' => 'form-control js-ckeditor',
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        @if($can_create)
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary float-left l-submit">
                                    {{ __('Create') }}
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
                        'media_url' => config('medialibrary.placeholder_image_path'),
                        'name'      => $media_constants::REQUEST_FIELD_NAME_MAIN_IMAGE,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

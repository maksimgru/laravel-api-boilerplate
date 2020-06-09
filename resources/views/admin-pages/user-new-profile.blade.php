@extends('layouts.auth', ['title' => __('New User')])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('role_constants', 'App\Constants\RoleConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@php
    $is_invalid_class = 'is-invalid';
    $profile_form_action_url = route($route_constants::ROUTE_NAME_WEB_CREATE_NEW_USER_PROFILE);

    $load_manager_users_ajax_url = apiRoute(
        $route_constants::ROUTE_NAME_USERS,
        ['primary_role_id' => $roles[$role_constants::ROLE_MANAGER], 'order_by' => 'username', 'sorted_by' => 'asc']
    );

    $load_business_users_ajax_url = apiRoute(
        $route_constants::ROUTE_NAME_USERS,
        ['primary_role_id' => $roles[$role_constants::ROLE_BUSINESS], 'order_by' => 'username', 'sorted_by' => 'asc']
    );
@endphp

@section('content')

<h1 class="mt-4">{{ __('New User') }}</h1>

{{-- PROFILE --}}
<div id="new-user" class="row mb-5 entity-profile entity-user">
    <div class="col-md-3 text-center">
        {{-- AVATAR --}}
        <div class="card card-user mb-5">
            <div class="card-body">
                @includeIf('layouts.includes.form.form-single-media', [
                    'media_url' => config('medialibrary.placeholder_avatar_path'),
                    'name'      => $media_constants::REQUEST_FIELD_NAME_AVATAR,
                ])
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-simple" title="facebook"><i class="fa fa-facebook-square"></i></a>
                <a href="#" class="btn btn-simple" title="twitter"><i class="fa fa-twitter"></i></a>
                <a href="#" class="btn btn-simple" title="google-plus"><i class="fa fa-google-plus-square"></i></a>
            </div>
        </div>

        {{-- BALANCE --}}
        <div class="card user-balance bg-success text-white mb-5 btn-ctrl-media-container">
            <div class="card bg-success">
                <div class="card-header"><h4 class="card-title font-weight-bold display-5">{{ __('Balance') }}</h4></div>
                <div class="card-body display-5">$0</div>
            </div>
        </div>

        {{-- COMMISSION --}}
        <div class="card user-commission bg-success text-white mb-5 btn-ctrl-media-container">
            <div class="card bg-success">
                <div class="card-header"><h4 class="card-title font-weight-bold display-5">{{ __('Commission') }} <br /> <small>{{ __('(from referrals)') }}</small></h4></div>
                <div class="card-body display-5">0%</div>
            </div>
        </div>
    </div>

    {{-- FORM DATA --}}
    <div class="col-md-9">
        <div class="card">
            <form id="form-profile-new-user"
                  class="form"
                  method="POST"
                  data-loader-type="spinner"
                  data-btn-submit-selector=".l-submit"
                  action="{{ $profile_form_action_url }}">
                @csrf
                <div class="card-header">
                    <h4 class="card-title">{{ __('New User Profile') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-email', [
                                    'label'    => __('Email'),
                                    'name'     => 'email',
                                    'required' => true,
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Username'),
                                    'name'               => 'username',
                                    'required'           => true,
                                    'addon_icon_classes' => 'fa fa-user',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'       => __('Password'),
                                    'type'        => 'password',
                                    'name'        => 'password',
                                    'required'    => true,
                                    'placeholder' => __('Password'),
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'    => __('First Name'),
                                    'name'     => 'first_name',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'    => __('Last Name'),
                                    'name'     => 'last_name',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Birthday'),
                                    'type'               => 'date',
                                    'name'               => 'birthday',
                                    'pattern'            => '\d{4}-\d{2}-\d{2}',
                                    'addon_icon_classes' => 'fa fa-calendar',
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                      'label'    => __('Company'),
                                      'name'     => 'company',
                                  ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                      'label'    => __('Position'),
                                      'name'     => 'position',
                                  ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-select', [
                                    'label'    => __('Role'),
                                    'name'     => 'primary_role_id',
                                    'value'    => $selectedRole,
                                    'required' => true,
                                    'items'    => $roles,
                                    'disabled' => !$can_edit_role,
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Country'),
                                    'name'               => 'address[country]',
                                    'error_key'          => 'address.country',
                                    'addon_icon_classes' => 'fa fa-street-view',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('City'),
                                    'name'               => 'address[city]',
                                    'error_key'          => 'address.city',
                                    'addon_icon_classes' => 'fa fa-street-view',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Postal Code'),
                                    'name'               => 'address[postal_code]',
                                    'error_key'          => 'address.postal_code',
                                    'addon_icon_classes' => 'fa fa-map-signs',
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'                  => __('Address'),
                                    'name'                   => 'address[address]',
                                    'error_key'              => 'address.address',
                                    'addon_icon_classes'     => 'fa fa-street-view',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                      'label'              => __('Phone'),
                                      'name'               => 'phone',
                                      'addon_icon_classes' => 'fa fa-phone',
                                  ])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    @includeIf('layouts.includes.form.form-control-textarea', [
                                        'label'         => __('About'),
                                        'name'          => 'about',
                                        'rows'          => 5,
                                        'placeholder'   => __('Here can be your description'),
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
                                    'label'              => __('Facebook'),
                                    'name'               => 'socials[facebook]',
                                    'type'               => 'url',
                                    'error_key'          => 'socials.facebook',
                                    'placeholder'        => __('URL'),
                                    'addon_icon_classes' => 'fa fa-link',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Twitter'),
                                    'name'               => 'socials[twitter]',
                                    'type'               => 'url',
                                    'error_key'          => 'socials.twitter',
                                    'placeholder'        => __('URL'),
                                    'addon_icon_classes' => 'fa fa-link',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Google'),
                                    'name'               => 'socials[google_plus]',
                                    'type'               => 'url',
                                    'error_key'          => 'socials.google_plus',
                                    'placeholder'        => __('URL'),
                                    'addon_icon_classes' => 'fa fa-link',
                                ])
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->isAdmin())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-select', [
                                    'label'                     => __('Manager'),
                                    'name'                      => 'manager_id',
                                    'value'                     => '',
                                    'disabled'                  => false,
                                    'url_view_preselected_item' => '',
                                    'addon_icon_classes'        => 'fa fa-user',
                                    'input_group_classes'       => 'input-group nowrap',
                                    'input_classes'             => 'form-control js-select2',
                                    'data_sets'                 => [
                                        'data-ajax--url'                  => $load_manager_users_ajax_url,
                                        'data-ajax--url-preselected-item' => '',
                                        'data-min-input-length'           => 2,
                                        'data-item-text-keys'             => 'username;email',
                                        'data-search-fields'              => 'email:ilike;username:ilike',
                                        'data-language-placeholder'       => __('Search User by Email or Username'),
                                        'data-language-input-too-short'   => __('Please enter :min or more characters. Search by Email or Username.', ['min' => 2]),
                                        'data-language-loading-more'      => '',
                                        'data-language-no-results'        => '',
                                        'data-language-error-loading'     => '',
                                        'data-language-searching'         => '',
                                    ],
                                ])
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-select', [
                                    'label'                     => __('Business'),
                                    'name'                      => 'business_id',
                                    'value'                     => '',
                                    'disabled'                  => false,
                                    'url_view_preselected_item' => '',
                                    'addon_icon_classes'        => 'fa fa-user',
                                    'input_group_classes'       => 'input-group nowrap',
                                    'input_classes'             => 'form-control js-select2',
                                    'data_sets'                 => [
                                        'data-ajax--url'                  => $load_business_users_ajax_url,
                                        'data-ajax--url-preselected-item' => '',
                                        'data-min-input-length'           => 2,
                                        'data-item-text-keys'             => 'username;email',
                                        'data-search-fields'              => 'email:ilike;username:ilike',
                                        'data-language-placeholder'       => __('Search User by Email or Username'),
                                        'data-language-input-too-short'   => __('Please enter :min or more characters. Search by Email or Username.', ['min' => 2]),
                                        'data-language-loading-more'      => '',
                                        'data-language-no-results'        => '',
                                        'data-language-error-loading'     => '',
                                        'data-language-searching'         => '',
                                    ],
                                ])
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary float-left l-submit">
                            {{ __('Create') }}
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

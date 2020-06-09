@extends('layouts.auth', ['title' => $is_auth_user ? __('My Profile') : __('User Profile')])

@inject('route_constants', 'App\Constants\RouteConstants')
@inject('role_constants', 'App\Constants\RoleConstants')
@inject('media_constants', 'App\Constants\MediaLibraryConstants')

@php
    $is_invalid_class = 'is-invalid';
    $avatar_id = $user['media']['avatar_urls']['id'];
    $avatar_url = $user['media']['avatar_urls']['thumbs']['thumb-medium'];
    $avatar_form_control_name = $media_constants::REQUEST_FIELD_NAME_AVATAR;
    $avatar_form_enctype = $avatar_id ? 'application/x-www-form-urlencoded' : 'multipart/form-data';
    $avatar_form_action_url = $avatar_id
        ? route($route_constants::ROUTE_NAME_WEB_DELETE_USER_MEDIA, ['media' => $avatar_id])
        : ($is_auth_user
            ? route($route_constants::ROUTE_NAME_WEB_UPDATE_MY_PROFILE)
            : route($route_constants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE, ['user_id' => $user['id']])
        )
    ;
    $profile_form_action_url = $is_auth_user
        ? route($route_constants::ROUTE_NAME_WEB_UPDATE_MY_PROFILE)
        : route($route_constants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE, ['user_id' => $user['id']])
    ;
    $balance_form_action_url = route($route_constants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE, ['user_id' => $user['id']]);
    $commission_form_action_url = route($route_constants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE, ['user_id' => $user['id']]);
    $url_request_verify_email = $is_auth_user ? route($route_constants::ROUTE_NAME_WEB_EMAIL_VERIFY_NOTICE) : null;

    $is_auth_admin = Auth::user()->isAdmin();
    $is_auth_manager = Auth::user()->isPrimaryRoleManager();
    $is_auth_business = Auth::user()->isPrimaryRoleBusiness();

    $load_manager_users_ajax_url = apiRoute(
        $is_auth_admin
            ? $route_constants::ROUTE_NAME_USERS
            : ''
        ,
        ['primary_role_id' => $roles[$role_constants::ROLE_MANAGER], 'order_by' => 'username', 'sorted_by' => 'asc']
    );

    $load_business_users_ajax_url = apiRoute(
        $is_auth_admin
            ? $route_constants::ROUTE_NAME_USERS
            : ($is_auth_manager ? $route_constants::ROUTE_NAME_MANAGER_OWN_BUSINESS_USERS : '')
        ,
        ['primary_role_id' => $roles[$role_constants::ROLE_BUSINESS], 'order_by' => 'username', 'sorted_by' => 'asc']
    );

    $load_manager_user_ajax_url = !empty($user['parent_manager'])
        ? apiRoute(
            $is_auth_admin
                ? $route_constants::ROUTE_NAME_USER
                : ''
            ,
            ['user_id' => $user['parent_manager']['id']]
        ) : ''
    ;

    $load_business_user_ajax_url = !empty($user['parent_business'])
        ? apiRoute(
            $is_auth_admin
                ? $route_constants::ROUTE_NAME_USER
                : ($is_auth_manager ? $route_constants::ROUTE_NAME_MANAGER_OWN_BUSINESS_USER : '')
            ,
            ['user_id' => $user['parent_business']['id']]
        ) : ''
    ;

    $view_manager_user_url = !empty($user['parent_manager'])
        ? route(
            $route_constants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE,
            ['user_id' => $user['parent_manager']['id']]
        ) : ''
    ;

    $view_business_user_url = !empty($user['parent_business'])
        ? route(
            $route_constants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE,
            ['user_id' => $user['parent_business']['id']]
        ) : ''
    ;

@endphp

@section('content')

<h1 class="mt-4">{{ __('User ID#:num', ['num' => $user['id']]) }}</h1>

{{-- PROFILE --}}
<div id="user-{{ $user['id'] }}" class="row mb-5 entity-profile entity-user">
    <div class="col-md-3 text-center">
        {{-- AVATAR --}}
        <div class="card card-user mb-5">
            <div class="card-body">
                @includeIf('layouts.includes.form.form-single-media', [
                    'media_id'  => $avatar_id,
                    'media_url' => $avatar_url,
                    'name'      => $avatar_form_control_name,
                    'enctype'   => $avatar_form_enctype,
                    'action'    => $avatar_form_action_url,
                    'btn_upload_label' => __('Upload Avatar'),
                ])
                <h4 class="title mt-2">
                    {{$user['first_name']}} {{$user['last_name']}}
                    <br />
                    <small><i class="fa fa-user text-black-50"></i> {{$user['username']}}</small>
                </h4>
            </div>
            <div class="card-footer">
                <a href="{{$user['properties']['socials']['facebook']}}" class="btn btn-simple" title="facebook" @if($user['properties']['socials']['facebook']) target="_blank" @endif><i class="fa fa-facebook-square"></i></a>
                <a href="{{$user['properties']['socials']['twitter']}}" class="btn btn-simple" title="twitter" @if($user['properties']['socials']['twitter']) target="_blank" @endif><i class="fa fa-twitter"></i></a>
                <a href="{{$user['properties']['socials']['google_plus']}}" class="btn btn-simple" title="google-plus" @if($user['properties']['socials']['google_plus']) target="_blank" @endif><i class="fa fa-google-plus-square"></i></a>
            </div>
        </div>

        {{-- BALANCE --}}
        <div class="card user-balance bg-success text-white mb-5 btn-ctrl-media-container">
            @if($can_edit_balance)
                <a href="#wrapper-form-balance-{{$user['id']}}"
                   class="btn btn-warning btn-ctrl-media collapsed"
                   data-toggle="collapse"
                   aria-expanded="true"
                   aria-controls="form-balance"
                >{{ __('Edit') }}</a>
            @endif
            <div class="card bg-success">
                <div class="card-header"><h4 class="card-title font-weight-bold display-5">{{ __('Balance :currency', ['currency' => '($)']) }}</h4></div>
                <div class="card-body display-5">{{ number_format($user['properties']['balance'], 2, '.', ',') }}</div>
            </div>
            @if($can_edit_balance)
                <div class="card-footer collapse" id="wrapper-form-balance-{{$user['id']}}">
                    <form id="form-balance-{{$user['id']}}"
                          class="form"
                          method="POST"
                          data-loader-type="spinner"
                          data-btn-submit-selector=""
                          action="{{ $balance_form_action_url }}"
                    >
                        @csrf
                        @includeIf('layouts.includes.form.form-control-input', [
                            'label'         => __('Balance :currency', ['currency' => '($)']),
                            'name'          => 'balance',
                            'type'          => 'number',
                            'step'          => '0.01',
                            'input_classes' => 'form-control col-md-8 offset-md-2 text-center mb-2',
                            'value'         => $user['properties']['balance'],
                            'addon'        => false,
                        ])
                        <button type="submit" class="btn btn-primary l-submit">{{ __('Update') }}</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- COMMISSION --}}
        @if(\in_array($user['primary_role']['name'], [$role_constants::ROLE_MANAGER, $role_constants::ROLE_BUSINESS], true))
        <div class="card user-commission bg-success text-white mb-5 btn-ctrl-media-container">
            @if($can_edit_balance && $can_edit_commission)
                <a href="#wrapper-form-commission-{{ $user['id'] }}"
                   class="btn btn-warning btn-ctrl-media collapsed"
                   data-toggle="collapse"
                   aria-expanded="true"
                   aria-controls="form-commission"
                >{{ __('Edit') }}</a>
            @endif
            <div class="card bg-success">
                <div class="card-header"><h4 class="card-title font-weight-bold display-5">{{ __('Commission') }} <br /> <small>{{ __('(from referrals)') }}</small></h4></div>
                <div class="card-body display-5">{{ number_format($user['properties']['commission'], 1, '.', ',') }} %</div>
            </div>
            @if($can_edit_commission)
                <div class="card-footer collapse" id="wrapper-form-commission-{{$user['id']}}">
                    <form id="form-commission-{{$user['id']}}"
                          class="form"
                          method="POST"
                          data-loader-type="spinner"
                          data-btn-submit-selector=""
                          action="{{ $commission_form_action_url }}"
                    >
                        @csrf
                        @includeIf('layouts.includes.form.form-control-input', [
                            'label'         => __('Commission :currency', ['currency' => '(%)']),
                            'name'          => 'commission',
                            'type'          => 'number',
                            'min'           => 0,
                            'max'           => 100,
                            'step'          => '0.1',
                            'input_classes' => 'form-control col-md-8 offset-md-2 text-center mb-2',
                            'value'         => $user['properties']['commission'],
                            'addon'        => false,
                        ])
                        <button type="submit" class="btn btn-primary l-submit">{{ __('Update') }}</button>
                    </form>
                </div>
            @endif
        </div>
        @endif
    </div>

    {{-- FORM DATA --}}
    <div class="col-md-9">
        <div class="card">
            <form id="form-profile-{{$user['id']}}"
                  class="form"
                  method="POST"
                  data-loader-type="spinner"
                  data-btn-submit-selector=".l-submit"
                  action="{{ $profile_form_action_url }}">
                @csrf
                <div class="card-header">
                    <h4 class="card-title">{{ __('Edit Profile') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-email', [
                                    'label'              => __('Email'),
                                    'name'               => 'email',
                                    'value'              => $user['email'],
                                    'required'           => true,
                                    'disabled'           => true,
                                    'verify'             => true,
                                    'is_verified'        => $has_verified_email,
                                    'url_request_verify' => $url_request_verify_email,
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Username'),
                                    'name'               => 'username',
                                    'value'              => $user['username'],
                                    'required'           => true,
                                    'disabled'           => !$can_edit_profile,
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
                                    'disabled'    => !$can_edit_profile,
                                    'placeholder' => __('Leave blank if no need change ...'),
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
                                    'value'    => $user['first_name'],
                                    'disabled' => !$can_edit_profile,
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'    => __('Last Name'),
                                    'name'     => 'last_name',
                                    'value'    => $user['last_name'],
                                    'disabled' => !$can_edit_profile,
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'              => __('Birthday'),
                                    'type'               => 'date',
                                    'name'               => 'birthday',
                                    'value'              => $user['birthday'],
                                    'pattern'            => '\d{4}-\d{2}-\d{2}',
                                    'disabled'           => !$can_edit_profile,
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
                                      'value'    => $user['properties']['company'],
                                      'disabled' => !$can_edit_profile,
                                  ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                      'label'    => __('Position'),
                                      'name'     => 'position',
                                      'value'    => $user['properties']['position'],
                                      'disabled' => !$can_edit_profile,
                                  ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-select', [
                                    'label'    => __('Role'),
                                    'name'     => 'primary_role_id',
                                    'value'    => $user['primary_role_id'],
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
                                    'value'              => $user['properties']['address']['country'],
                                    'disabled'           => !$can_edit_profile,
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
                                    'value'              => $user['properties']['address']['city'],
                                    'disabled'           => !$can_edit_profile,
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
                                    'value'              => $user['properties']['address']['postal_code'],
                                    'disabled'           => !$can_edit_profile,
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
                                    'value'                  => $user['properties']['address']['address'],
                                    'disabled'               => !$can_edit_profile,
                                    'addon_icon_classes'     => 'fa fa-street-view',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-input', [
                                      'label'              => __('Phone'),
                                      'name'               => 'phone',
                                      'value'              => $user['properties']['phone'],
                                      'disabled'           => !$can_edit_profile,
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
                                    'value'         => $user['properties']['about'],
                                    'disabled'      => !$can_edit_profile,
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
                                    'value'              => $user['properties']['socials']['facebook'],
                                    'disabled'           => !$can_edit_profile,
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
                                    'value'              => $user['properties']['socials']['twitter'],
                                    'disabled'           => !$can_edit_profile,
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
                                    'value'              => $user['properties']['socials']['google_plus'],
                                    'disabled'           => !$can_edit_profile,
                                    'placeholder'        => __('URL'),
                                    'addon_icon_classes' => 'fa fa-link',
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
                                    'value'              => \Carbon\Carbon::parse($user['created_at'])->format(config('formats.datetime')),
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
                                    'value'              => \Carbon\Carbon::parse($user['updated_at'])->format(config('formats.datetime')),
                                    'pattern'            => '\d{4}-\d{2}-\d{2}',
                                    'disabled'           => true,
                                    'addon_icon_classes' => 'fa fa-calendar',
                                ])
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group d-inline-block">
                                @includeIf('layouts.includes.form.form-control-input', [
                                    'label'     => __('Is Activated'),
                                    'type'      => 'checkbox',
                                    'name'      => 'is_active',
                                    'value'     => old('is_active') ?? (int) $user['is_active'],
                                    'error_key' => 'is_active',
                                    'disabled'  => !$can_activated,
                                    'checked'   => old('is_active') ?? $user['is_active'],
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                @includeIf('layouts.includes.form.form-control-select', [
                                    'label'                     => __('Manager'),
                                    'name'                      => 'manager_id',
                                    'value'                     => !empty($user['parent_manager']) ? $user['parent_manager']['id'] : '',
                                    'items'                     => !empty($user['parent_manager']) && !$load_manager_user_ajax_url
                                        ? ['ID#' . $user['parent_manager']['id'] . ' | ' . $user['parent_manager']['username'] => $user['parent_manager']['id']]
                                        : []
                                    ,
                                    'disabled'                  => !$can_edit_profile || $is_auth_user,
                                    'url_view_preselected_item' => $view_manager_user_url,
                                    'addon_icon_classes'        => 'fa fa-user',
                                    'input_group_classes'       => 'input-group nowrap',
                                    'input_classes'             => 'form-control js-select2',
                                    'data_sets'                 => [
                                         'data-ajax--url'                  => $load_manager_users_ajax_url,
                                         'data-ajax--url-preselected-item' => $load_manager_user_ajax_url,
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
                                    'value'                     => !empty($user['parent_business']) ? $user['parent_business']['id'] : '',
                                    'items'                     => !empty($user['parent_business']) && !$load_business_user_ajax_url
                                        ? ['ID#' . $user['parent_business']['id'] . ' | ' . $user['parent_business']['username'] => $user['parent_business']['id']]
                                        : []
                                    ,
                                    'disabled'                  => !$can_edit_profile || $is_auth_user,
                                    'url_view_preselected_item' => $view_business_user_url,
                                    'addon_icon_classes'        => 'fa fa-user',
                                    'input_group_classes'       => 'input-group nowrap',
                                    'input_classes'             => 'form-control js-select2',
                                    'data_sets'                 => [
                                        'data-ajax--url'                  => $load_business_users_ajax_url,
                                        'data-ajax--url-preselected-item' => $load_business_user_ajax_url,
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
                </div>
                <div class="card-footer">
                    @if($can_edit_profile)
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary float-left l-submit">
                            {{ __('Update Profile') }}
                        </button>
                    </div>
                    @endif
                    <div class="clearfix"></div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@extends('layouts.guest')

@inject('route_constants', 'App\Constants\RouteConstants')

@section('content')

<div class="card-header">
    <h3 class="text-center font-weight-light my-4">{{ __('Reset Password') }}</h3>
</div>

<div class="card-body">
    <form method="POST" action="{{ route($route_constants::ROUTE_NAME_WEB_PASSWORD_UPDATE) }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group row">
            @includeIf('layouts.includes.form.form-control-email', [
                'label'               => __('Email'),
                'name'                => 'email',
                'value'               => old('email') ?? request('email'),
                'id'                  => 'inputEmailAddress',
                'label_classes'       => 'col-md-4 col-form-label text-md-right control-label font-weight-bold',
                'input_classes'       => 'form-control',
                'input_group_classes' => 'col-md-6 input-group',
                'required'            => true,
                'autofocus'           => true,
                'autocomplete'        => 'email',
                'placeholder'         => __('Enter email address'),
            ])
        </div>

        <div class="form-group row">
            @includeIf('layouts.includes.form.form-control-input', [
                'label'               => __('Password'),
                'type'                => 'password',
                'name'                => 'password',
                'id'                  => 'inputNewPassword',
                'label_classes'       => 'col-md-4 col-form-label text-md-right control-label font-weight-bold',
                'input_classes'       => 'form-control',
                'input_group_classes' => 'col-md-6 input-group',
                'autocomplete'        => 'new-password',
                'required'            => true,
                'placeholder'         => __('Enter new password'),
            ])
        </div>

        <div class="form-group row">
            @includeIf('layouts.includes.form.form-control-input', [
                'label'               => __('Confirm Password'),
                'type'                => 'password',
                'name'                => 'password_confirmation',
                'id'                  => 'inputConfirmPassword',
                'label_classes'       => 'col-md-4 col-form-label text-md-right control-label font-weight-bold',
                'input_classes'       => 'form-control',
                'input_group_classes' => 'col-md-6 input-group',
                'autocomplete'        => 'new-password',
                'required'            => true,
                'placeholder'         => __('Enter confirm password'),
            ])
        </div>

        <div class="form-group row mb-0">
            <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card-footer text-center">
    <div class="small"></div>
</div>
@endsection

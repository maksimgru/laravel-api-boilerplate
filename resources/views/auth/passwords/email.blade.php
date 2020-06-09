@extends('layouts.guest')

@inject('route_constants', 'App\Constants\RouteConstants')

@section('content')

<div class="card-header">
    <h3 class="text-center font-weight-light my-4">{{ __('Reset Password') }}</h3>
</div>

<div class="card-body">
    @if (session('status'))

        @includeWhen(session('status'), 'layouts.includes.alert', ['message' => session('status')])

        <div class="small text-muted mb-2">
            {{ __('Please check your email (:email) for a Password Reset Link.', ['email' => old('email')]) }}
        </div>

        <div class="small text-muted mb-4">
            {{ __('If you did not receive the email') }},
            <a href="{{ route($route_constants::ROUTE_NAME_WEB_PASSWORD_REQUEST) }}" class="btn btn-link p-0 m-0 align-baseline">
                {{ __('click here to request another.') }}
            </a>
        </div>

        <a href="{{ route($route_constants::ROUTE_NAME_WEB_LOGIN) }}" class="btn btn-primary" >
            {{ __('Return to login') }}
        </a>

    @else

        <div class="small mb-3 text-muted">{{ __('Enter your email address and we will send you a link to reset your password.') }}</div>

        <form method="POST" action="{{ route($route_constants::ROUTE_NAME_WEB_PASSWORD_EMAIL) }}">
            @csrf
            <div class="form-group">
                @includeIf('layouts.includes.form.form-control-email', [
                    'label'         => __('Email'),
                    'name'          => 'email',
                    'value'         => old('email'),
                    'id'            => 'inputEmailAddress',
                    'label_classes' => 'small mb-1',
                    'input_classes' => 'form-control py-4',
                    'required'      => true,
                    'autofocus'     => true,
                    'autocomplete'  => 'email',
                    'placeholder'   => __('Enter email address'),
                ])
            </div>
            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                <a class="small" href="{{ route($route_constants::ROUTE_NAME_WEB_LOGIN) }}">{{ __('Return to login') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Send Password Reset Link') }}</button>
            </div>
        </form>

    @endif
</div>

<div class="card-footer text-center">
    <div class="small"></div>
</div>

@endsection

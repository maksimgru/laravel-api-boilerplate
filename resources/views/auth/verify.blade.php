@extends('layouts.auth')

@inject('route_constants', 'App\Constants\RouteConstants')

@section('content')

<div class="card-header">
    <h3 class="text-center font-weight-light my-4">{{ __('Verify Your Email Address') }}</h3>
</div>

<div class="card-body">
    @includeWhen($resent ?? false, 'layouts.includes.alert', ['message' => __('A fresh verification link has been sent to your email address.')])

    {{ __('Before proceeding, please check your email for a verification link.') }}
    {{ __('If you did not receive the email') }},

    <form class="d-inline" method="POST" action="{{ route($route_constants::ROUTE_NAME_WEB_EMAIL_VERIFY_RESEND) }}">
        @csrf
        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
    </form>
</div>

<div class="card-footer text-center">
    <div class="small"></div>
</div>

@endsection

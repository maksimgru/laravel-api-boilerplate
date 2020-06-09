@extends('layouts.guest')

@section('content')
    @inject('route_constants', 'App\Constants\RouteConstants')

    <div class="card-header">
        <h3 class="text-center font-weight-light my-4">{{ __('Login') }}</h3>
    </div>

    <div class="card-body">
        @includeWhen(session('status'), 'layouts.includes.alert', ['message' => session('status')])
        <form method="POST" action="{{ route($route_constants::ROUTE_NAME_WEB_LOGIN) }}">
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

            <div class="form-group">
                @includeIf('layouts.includes.form.form-control-input', [
                    'label'         => __('Password'),
                    'type'          => 'password',
                    'name'          => 'password',
                    'id'            => 'inputPassword',
                    'label_classes' => 'small mb-1',
                    'input_classes' => 'form-control py-4',
                    'autocomplete'  => 'current-password',
                    'required'      => true,
                    'placeholder'   => __('Enter password'),
                ])
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" id="rememberPasswordCheck" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                    <label class="custom-control-label" for="rememberPasswordCheck">{{ __('Remember Me') }}</label>
                </div>
            </div>

            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                @if (Route::has('password.request'))
                    <a class="small" href="{{ route($route_constants::ROUTE_NAME_WEB_PASSWORD_REQUEST) }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
                <button type="submit" class="btn btn-primary">
                    {{ __('Login') }}
                </button>
            </div>
        </form>
    </div>

    <div class="card-footer text-center">
        <div class="small"></div>
    </div>

@endsection

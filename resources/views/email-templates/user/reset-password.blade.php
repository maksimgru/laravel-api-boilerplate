@component('mail::message')

@component('mail::panel')
<p>To start process reset your password</p>
<p>Please, enter <small>"reset-password-token"</small> in your application:</p>
@endcomponent

# reset-password-token
@component('mail::promotion')
<strong>{{ $resetPasswordToken }}</strong>
@endcomponent

<p>OR click button</p>

@component('mail::button', ['url' => $resetPasswordUrl])
Reset password link
@endcomponent

Thanks,
<br>
{{ config('app.name') }}

@endcomponent

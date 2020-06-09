@component('mail::message')

<h1>Verify Email Address on {{ config('app.name') }}</h1>

<p>Please click the button below to verify your email address.</p>

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

<p>If you did not create an account, no further action is required.</p>

@component('mail::subcopy')
If you’re having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:
<a herf="{!! $verificationUrl !!}" target="_blank" title="Verify Email Address">{!! $verificationUrl !!}</a>
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

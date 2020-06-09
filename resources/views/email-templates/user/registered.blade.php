@component('mail::message')

<p>{{ __('You are registered on :name', ['name' => config('app.name')]) }}</p>

- Email: {{ $user->email }}
- Username: {{ $user->username }}
- Password: {{ $requestInput['password'] }}
- Primary Role: {{ $user->primaryRole()->first()->name }}

@if($loginUrl)
@component('mail::button', ['url' => $loginUrl])
{{ __('Login') }}
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent

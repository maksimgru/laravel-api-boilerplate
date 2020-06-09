@php
    $name = $name ?? 'input';
    $message = $message ?? 'invalid input';
@endphp

@error($name)<span class="invalid-feedback {{ $name }}" role="alert"><strong>{{ $message }}</strong></span>@enderror

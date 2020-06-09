@php
    $id = $id ?? '';
    $for = $id ?? '';
    $label = $label ?? '';
    $name = $name ?? 'input';
    $value = $value ?? '';
    $placeholder = $placeholder ?? $label;
    $error_key = $error_key ?? $name;

    $disabled = $disabled ?? false;
    $required = $required ?? false;
    $autofocus = $autofocus ?? false;
    $autocomplete = $autocomplete ?? 'email';
    $pattern = $pattern ?? false;

    $is_invalid_class = $is_invalid_class ?? 'is-invalid';
    $input_group_classes = $input_group_classes ?? 'input-group';
    $input_classes = $input_classes ?? 'form-control';
    $label_classes = $label_classes ?? 'control-label font-weight-bold';
    $label_classes = $required ? $label_classes . ' label-required ' : $label_classes;

    $addon = $addon ?? true;
    $addon_icon_classes = $addon_icon_classes ?? 'fa fa-envelope';

    $verify = $verify ?? false;
    $is_verified = $is_verified ?? false;
    $tooltip_placement = $tooltip_placement ?? 'top';
    $title_verified = $title_verified ?? __('auth.email.already_verified');
    $title_not_verified = $title_not_verified ?? __('auth.email.not_verified_notice');
    $label_request_verify = $label_request_verify ?? __('Request Verify');
    $url_request_verify = $url_request_verify ?? '';
@endphp

@if($label)
    <label @if($id) for="{{ $id }}" @endif
            class="{{ $label_classes }}">
        {{ $label }}
        @if($verify)
            @if($is_verified)
                <i class="fa fa-check-circle text-success"
                   @if($tooltip_placement) data-toggle="tooltip" @endif
                   @if($tooltip_placement) data-placement="{{ $tooltip_placement }}" @endif
                   title="{{ $title_verified }}"
                ></i>
            @else
                <i class="fa fa-times-circle text-danger"
                   @if($tooltip_placement) data-toggle="tooltip" @endif
                   @if($tooltip_placement) data-placement="{{ $tooltip_placement }}" @endif
                   title="{{ $title_not_verified }}"
                ></i>
                @if($url_request_verify) <small><a href="{{ $url_request_verify }}">{{ $label_request_verify }}</a></small> @endif
            @endif
        @endif
    </label>
@endif

<div class="{{ $input_group_classes }}">
    <input @if($id) id="{{ $id }}" @endif
           class="{{ $input_classes }} @error($error_key) {{ $is_invalid_class }} @enderror"
           type="email"
           name="{{ $name }}"
           value="{{ old($error_key) ?? $value }}"
           placeholder="{{ $placeholder }}"
           aria-describedby="@camelcase($name)Help"
           @if($disabled) disabled @endif
           @if($required) required @endif
           @if($autofocus) autofocus @endif
           @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if($pattern) pattern="{{ $pattern }}" @endif
    />

    @if($addon)
        <div class="input-group-addon">
            <span><i class="{{ $addon_icon_classes }}"></i></span>
        </div>
    @endif

    @includeIf('layouts.includes.form.invalid-feedback-message', ['name' => $error_key])
</div>

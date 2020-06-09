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
    $autocomplete = $autocomplete ?? false;
    $pattern = $pattern ?? false;

    $rows = $rows ?? 5;

    $is_invalid_class = $is_invalid_class ?? 'is-invalid';
    $input_group_classes = $input_group_classes ?? 'input-group';
    $input_classes = $input_classes ?? 'form-control';
    $label_classes = $label_classes ?? 'control-label font-weight-bold';
    $label_classes = $required ? $label_classes . ' label-required ' : $label_classes;

    $addon = $addon ?? true;
    $addon_icon_classes = $addon_icon_classes ?? 'fa fa-file-text';
@endphp

@if($label)
    <label @if($id) for="{{ $id }}" @endif
           class="{{ $label_classes }}"
    >
        {{ $label }}
    </label>
@endif

<div class="{{ $input_group_classes }}">
    <textarea @if($id) id="{{ $id }}" @endif
           class="{{ $input_classes }} @error($error_key) {{ $is_invalid_class }} @enderror"
           name="{{ $name }}"
           value="{{ old($error_key) ?? $value }}"
           placeholder="{{ $placeholder }}"
           aria-describedby="@camelcase($name)Help"
           rows="{{ $rows }}"
           @if($disabled) disabled @endif
           @if($required) required @endif
           @if($autofocus) autofocus @endif
           @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if($pattern) pattern="{{ $pattern }}" @endif
    >{{ $value }}</textarea>

    @if($addon)
        <div class="input-group-addon">
            <span><i class="{{ $addon_icon_classes }}"></i></span>
        </div>
    @endif

    @includeIf('layouts.includes.form.invalid-feedback-message', ['name' => $error_key])
</div>

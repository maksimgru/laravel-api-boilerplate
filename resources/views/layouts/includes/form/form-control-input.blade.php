@php
    $id = $id ?? '';
    $for = $id ?? '';
    $label = $label ?? '';
    $type = $type ?? 'text';
    $name = $name ?? 'input';
    $value = $value ?? '';
    $placeholder = $placeholder ?? $label;
    $error_key = $error_key ?? $name;

    $disabled = $disabled ?? false;
    $required = $required ?? false;
    $checked = $checked ?? false;
    $autofocus = $autofocus ?? false;
    $autocomplete = $autocomplete ?? false;
    $pattern = $pattern ?? false;
    $onchange = $onchange ?? false;

    $is_checkbox_type = $type == 'checkbox';
    $onchange = $is_checkbox_type ? 'this.nextElementSibling.value = this.checked ? 1 : 0' : $onchange;

    $is_number_type = $type == 'number';
    $min = $min ?? false;
    $max = $max ?? false;
    $step = $step ?? 1;

    $is_invalid_class = $is_invalid_class ?? 'is-invalid';
    $input_group_classes = $input_group_classes ?? 'input-group';
    $input_classes = $input_classes ?? 'form-control';
    $label_classes = $label_classes ?? 'control-label font-weight-bold';
    $label_classes = $required ? $label_classes . ' label-required ' : $label_classes;

    $addon = $addon ?? true;
    $addon = $type == 'checkbox' ? false : $addon;
    $addon_icon_classes = $addon_icon_classes ?? 'fa fa-file-text';
@endphp

@if($label)
    <label @if($id) for="{{ $id }}" @endif
            class="{{ $label_classes }}"
    >
           {{ $label }}
    </label>
@endif

<div class="{{ $input_group_classes }} @if($type === 'password') show-hide-password-group @endif">
    <input @if($id) id="{{ $id }}" @endif
        class="{{ $input_classes }} @error($error_key) {{ $is_invalid_class }} @enderror"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($error_key) ?? $value }}"
        placeholder="{{ $placeholder }}"
        aria-describedby="@camelcase($name)Help"
        @if($id) id="{{ $id }}" @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        @if($checked) checked @endif
        @if($autofocus) autofocus @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($pattern) pattern="{{ $pattern }}" @endif
        @if($onchange) onchange="{{ $onchange }}" @endif
        @if($is_number_type && $min !== false) min="{{ $min }}" @endif
        @if($is_number_type && $max !== false) max="{{ $max }}" @endif
        @if($is_number_type) step="{{ $step }}" @endif
    />

    @if($is_checkbox_type)
        <input type="hidden" name="{{ $name }}" value="{{ old($error_key) ?? $value }}" />
    @endif

    @if($addon)
        <div class="input-group-addon">
            @if($type === 'password')
                <span class="pointer l-eye"><i class="fa fa-eye-slash" aria-hidden="true"></i></span>
            @elseif($type === 'url' && $value)
                <a href="{{ $value }}"
                   target="_blank"
                   data-toggle="tooltip"
                   data-placement="bottom"
                   title="{{ __('Go To') }}"
                ><i class="{{ $addon_icon_classes }}"></i></a>
            @else
                <span><i class="{{ $addon_icon_classes }}"></i></span>
            @endif
        </div>
    @endif

    @includeIf('layouts.includes.form.invalid-feedback-message', ['name' => $error_key])
</div>

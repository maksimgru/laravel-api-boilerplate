@php
    $id = $id ?? '';
    $for = $id ?? '';
    $label = $label ?? '';
    $name = $name ?? 'input';
    $value = $value ?? '';
    $items = $items ?? [];
    $placeholder = $placeholder ?? $label;
    $error_key = $error_key ?? $name;

    $disabled = $disabled ?? false;
    $required = $required ?? false;
    $autofocus = $autofocus ?? false;

    $is_invalid_class = $is_invalid_class ?? 'is-invalid';
    $input_group_classes = $input_group_classes ?? 'input-group';
    $input_classes = $input_classes ?? 'form-control';
    $label_classes = $label_classes ?? 'control-label font-weight-bold';
    $label_classes = $required ? $label_classes . ' label-required ' : $label_classes;

    $addon = $addon ?? true;
    $addon_icon_classes = $addon_icon_classes ?? 'fa fa-file-text';
    $url_view_preselected_item = $url_view_preselected_item ?? '';

    $data_sets = $data_sets ?? [];
@endphp

@if($label)
    <label @if($id) for="{{ $id }}" @endif
           class="{{ $label_classes }}"
    >
        {{ $label }}
    </label>
@endif

<div class="{{ $input_group_classes }}">
    <select @if($id) id="{{ $id }}" @endif
           class="{{ $input_classes }} @error($error_key) {{ $is_invalid_class }} @enderror"
           name="{{ $name }}"
           placeholder="{{ $placeholder }}"
           aria-describedby="@camelcase($name)Help"
           @if($disabled) disabled @endif
           @if($required) required @endif
           @if($autofocus) autofocus @endif
           @foreach($data_sets as $key => $value)
               {{ $key }}="{{ $value }}"
           @endforeach
    >
           @foreach($items as $item_key => $item_value)
                  <option
                          @if($item_value == (old($error_key) ?? $value)) selected @endif
                          value="{{ $item_value }}"
                  >
                         {{ $item_key }}
                  </option>
           @endforeach
    </select>

    @if($addon)
        <div class="input-group-addon">
            @if($url_view_preselected_item)
                <a href="{{ $url_view_preselected_item }}" target="_blank"><i class="{{ $addon_icon_classes }}"></i></a>
            @else
                <span><i class="{{ $addon_icon_classes }}"></i></span>
            @endif
        </div>
    @endif

    @includeIf('layouts.includes.form.invalid-feedback-message', ['name' => $error_key])
</div>

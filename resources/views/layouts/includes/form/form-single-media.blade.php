@php
    $is_card = $is_card ?? false;
    $media_id = $media_id ?? 0;
    $name = $name ?? 'media';
    $error_key = $error_key ?? $name;
    $form_id = $form_id ?? random_int(1, 999999);
    $form_classes = $form_classes ?? 'form form-single-file btn-ctrl-media-container';
    $method = $method ?? 'POST';
    $action = $action ?? '';
    $enctype = $enctype ?? 'application/x-www-form-urlencoded';
    $data_loader_type = $data_loader_type ?? 'progress';
    $data_btn_submit_selector = $data_btn_submit_selector ?? '.l-submit';
    $btn_upload_label = $btn_upload_label ?? '';
    $btn_delete_label = $btn_delete_label ?? '';
    $as_btn_upload = $as_btn_upload ?? false;

    $media_url = $media_url ?? '';
    $link_url = $link_url ?? '';
    $modal_selector = $modal_selector ?? '';
    $modal_body = $modal_body ?? '';
@endphp

@if($is_card)
    <div class="card mb-5">
    <div class="card-body">
@endif

    <form id="{{ $form_id }}"
          class="{{ $form_classes }}"
          data-loader-type="{{ $data_loader_type }}"
          data-btn-submit-selector="{{ $data_btn_submit_selector }}"
          method="{{ $method }}"
          enctype="{{ $enctype }}"
          action="{{ $action }}"
    >

        @csrf

        @if($media_id && $action)

            <button class="btn btn-danger btn-delete btn-ctrl-media l-submit fa fa-minus"
                    data-target-form="#{{ $form_id }}"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    data-confirm-message="{{ __('Are you sure to Delete this Media?') }}"
                    title="{{ __('Delete') }}"> {{ $btn_delete_label }}</button>

            @if($media_id === old('media'))
                @includeIf('layouts.includes.form.invalid-feedback-message', ['name' => 'media'])
            @endif

        @else

            @if($action)
                <button class="btn btn-success btn-upload l-submit fa fa-plus @if(!$as_btn_upload) btn-ctrl-media @endif"
                        data-target-input="#input-{{ $form_id }}"
                        data-target-form="#{{ $form_id }}"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        title="{{ $btn_upload_label ?: __('Upload') }}"
                > {{ $btn_upload_label }}</button>
            @endif

            <input id="input-{{ $form_id }}"
                   class="form-control hidden hidden-input-file @error($error_key) is-invalid @enderror"
                   type="file"
                   name="{{ $name }}"
                   aria-describedby="@camelcase($name)Help"
            />
            @includeIf('layouts.includes.form.invalid-feedback-message', ['name' => $error_key])

        @endif

        @if($link_url)
            <a href="{{ $link_url }}" title="{{ $name }}" data-toggle="modal" data-target="{{ $modal_selector }}" data-modal-body="{{ $modal_body }}">
                <img src="{{ $media_url }}" alt="{{ $name }}" class="media card {{ $name }} @if(!$media_url || $as_btn_upload) hidden @endif" />
            </a>
        @else
            <img src="{{ $media_url }}" alt="{{ $name }}" class="media card {{ $name }} @if(!$media_url || $as_btn_upload) hidden @endif" />
        @endif

    </form>

@if($is_card)
    </div>
    </div>
@endif

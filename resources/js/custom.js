(function($) {
    "use strict";
    $(function () {
        var LOYALTY = window.LOYALTY || {};
        LOYALTY['trans'] = function ($key) {
            return LOYALTY['language'][$key] || $key;
        };
        LOYALTY['document'] = $(document);
        LOYALTY['body'] = LOYALTY.document.find('body');
        LOYALTY['defaultLocale'] = 'en';
        LOYALTY['loaderTypes'] = ['spinner', 'progress'];
        LOYALTY['spinner'] = $('<div class="loader-wrapper l-spinner"></div>');
        LOYALTY['progress'] = $('<div class="loader-wrapper l-progress"><progress max="100"></progress></div>');

        LOYALTY['viewBtn'] = $('<a class="btn btn-success data-table-edit-row-btn mr-1" data-toggle="tooltip" data-placement="left" title="view"><i class="fa fa-eye"></i></a>');
        LOYALTY['restoreBtn'] = $('<a class="btn btn-warning data-table-restore-row-btn mr-1" data-confirm-message="Are you sure to Restore?" data-toggle="tooltip" data-placement="left" title="restore"><i class="fa fa-refresh"></i></a>');
        LOYALTY['deleteBtn'] = $('<a class="btn btn-danger data-table-delete-row-btn mr-1 text-white" data-confirm-message="Are you sure to Delete?" data-toggle="tooltip" data-placement="left" title="delete"><i class="fa fa-times-circle"></i></a>');

        LOYALTY['dateFormat'] = function (datetime, format) {
            if (datetime.constructor.name == 'String') {
                datetime = new Date(datetime);
            }
            var format = format || 'Y-m-d / H:i:s';
            var result = format;
            var year = datetime.getFullYear() + '';
            var month = ("0" + (1 + datetime.getMonth())).slice(-2);
            var date = ("0" + (1 + datetime.getDate())).slice(-2);
            var hours = ("0" + (1 + datetime.getHours())).slice(-2);
            var minutes = ("0" + (1 + datetime.getMinutes())).slice(-2);
            var seconds = ("0" + (1 + datetime.getSeconds())).slice(-2);

            result = result.replace('Y', year);
            result = result.replace('m', month);
            result = result.replace('d', date);
            result = result.replace('H', hours);
            result = result.replace('i', minutes);
            result = result.replace('s', seconds);

            return result;
        };

        var onToggleSidebarNav = function (e) {
            e.preventDefault();
            LOYALTY.body.toggleClass('sb-sidenav-toggled');
        };

        var onToggleShowHidePasswordInput = function(e) {
            e.preventDefault();
            var $eye = $(this);
            var $eyeIcon = $eye.find('i');
            var $group = $eye.closest('.show-hide-password-group');
            var $input = $group.find('.form-control');

            if ($input.attr('type') == 'text') {
                $input.attr('type', 'password');
                $eyeIcon.addClass('fa-eye-slash').removeClass('fa-eye');
            } else if ($input.attr('type') == 'password') {
                $input.attr('type', 'text');
                $eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        };

        var onBtnUploadClick = function (e) {
            e.preventDefault();
            var $btn = $(this);
            var formSelector = $btn.data('target-form');
            var inputSelector = $btn.data('target-input');
            var $form = formSelector ? $(formSelector) : $btn.closest('.form');
            var $input = inputSelector ? $(inputSelector) : $form.find('input:file').first();
            $input.trigger('click');
        };

        var onInputFileChange = function (e) {
            var $input = $(this);
            var $form = $input.closest('form');
            $form.submit();
        };

        var onFormSubmit = function (e) {
            var $form = $(this);
            var loaderType = $form.data('loader-type') || LOYALTY['loaderTypes'][0];
            var submitBtnSelector = $form.data('btn-submit-selector');
            $form
                .prepend(LOYALTY[loaderType].clone())
                .find(submitBtnSelector).toggleClass('l-spinner');
            ;
        };

        var onBtnRestoreTableRow = function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var url = $btn.attr('href');
            var confirm = window.confirm($btn.data('confirm-message'));

            if (confirm) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + $('meta[name="jwt-token"]').attr('content')
                    },
                    beforeSend: function () {
                        $btn.addClass('l-spinner');
                    },
                    success: function () {
                        $row.fadeOut();
                        $btn.removeClass('l-spinner');
                    },
                    complete: function () {
                        $btn.removeClass('l-spinner');
                    }
                });
            }
        };

        var onBtnDeleteTableRow = function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var url = $btn.attr('href') || '';
            var confirm = window.confirm($btn.data('confirm-message'));

            if (confirm) {
                $row.fadeOut();
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + $('meta[name="jwt-token"]').attr('content')
                    },
                    beforeSend: function () {
                        $btn.addClass('l-spinner');
                    },
                    success: function () {
                        $row.fadeOut();
                        $btn.removeClass('l-spinner');
                    },
                    error: function () {
                        $row.fadeIn();
                    },
                    complete: function () {
                        $btn.removeClass('l-spinner');
                    }
                });
            }
        };

        var onBtnAdd = function (e) {
            e.preventDefault();
            var $btn = $(this);
            var startIndex = $btn.attr('data-start-index') || 0;
            var containerSelector = $btn.data('container-selector') || '';
            var $container = $(containerSelector);
            var templateSelector = $btn.data('template-selector') || '';
            var $templateWrap = $(templateSelector).clone().wrap('<div class="wrap-template"></div>').closest('.wrap-template');
            var templateHtml = $templateWrap.html().replace(/%index%/ig, startIndex);
            var $template = $templateWrap.html(templateHtml).children();

            $template
                .removeClass('hidden')
                .removeClass('_template_')
                .find(':input')
                .attr('disabled', false)
            ;

            $container.append($template);

            $btn.attr('data-start-index', 1*startIndex + 1);
        };

        var onBtnDelete = function (e) {
            e.preventDefault();
            var $btn = $(this);
            var targetSelector = $btn.data('target-selector') || '';
            var $target = $(targetSelector);
            var formSelector = $btn.data('target-form') || '';
            var $form = $(formSelector);
            var url = $btn.attr('href') || '';
            var confirm = window.confirm($btn.data('confirm-message'));
            var onlyHide = $btn.data('only-hide') || false;

            if (confirm) {
                $target.fadeOut(function () {
                    if (onlyHide) {
                        $(this).addClass('hidden');
                    } else {
                        $(this).remove();
                    }
                });
                $form.submit();
            }
        };

        var onModalShowing = function (e) {
            var $modal = $(this);
            var $modalBody = $modal.find('.modal-body').empty();
            var encodedContent = $(e.relatedTarget).data('modal-body') || '';
            var decodeEntitiesContent = $('<div/>').html(encodedContent).text();

            if (encodedContent.length) {
                $modalBody.html(decodeEntitiesContent);
            }
        };

        var onModalHidden = function (e) {
            var $modal = $(this);
            $modal.find('.modal-body').empty();
        };

        // Global settings for ajax request
        $.ajaxSetup({
            headers: {
                'Authorization': 'Bearer ' + $('meta[name="jwt-token"]').attr('content')
            },
        });

        // Init Tooltips
        LOYALTY.body.find('[data-toggle="tooltip"]').tooltip();

        // Init Tooltips after ajax requests, if not already done
        LOYALTY.document.ajaxComplete(function (event, request, settings) {
            LOYALTY.body
                .find('[data-toggle="tooltip"]')
                .not('[data-original-title]')
                .tooltip()
            ;
        });

        // Init Carousel
        LOYALTY.body.find('.js-owl-carousel').owlCarousel({
            loop:false,
            autoHeight:true,
            nav:true,
            margin:10,
            items:3,
            responsiveClass:true,
            responsive:{
                0:{
                    items:1,
                },
                600:{
                    items:3,
                },
            }
        });

        // Init Select2
        LOYALTY.body.find('.js-select2').each(function () {
            var $select = $(this);
            var minimumInputLength = $select.data('min-input-length') || 1;
            var responseDataFilter = function (items, $select) {
                return items.map(function (itemData) {
                    var idKey = $select.data('item-id-key') || 'id';
                    var textKeys = ($select.data('item-text-keys') || '')
                        .split(';')
                        .map(function (key) {
                            var itemValue = itemData[key] || '';
                            return itemValue ? ' | ' + itemValue : '';
                        })
                    ;
                    return {
                        id: itemData[idKey],
                        text: 'ID#' + itemData[idKey] + textKeys.join(''),
                        data: itemData
                    };
                })
            };

            // Set up the Select2 control
            $select.select2({
                allowClear: true,
                ajax: {
                    url: $select.data('ajax--url'),
                    type: $select.data('ajax--type') || 'GET',
                    cache: true,
                    dataType: 'json',
                    delay: 500,
                    data: function (params) {
                        return {
                            search: params.term,
                            search_fields: $select.data('search-fields'),
                            _type: 'query_append',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: responseDataFilter(data.data, $select),
                            pagination: {
                                more: (params.page * data.meta.pagination.per_page) < data.meta.pagination.total
                            }
                        };
                    },
                },
                templateSelection: function (data, container) {
                    return data.text;
                },
                theme: $select.data('theme') || 'bootstrap',
                minimumInputLength: minimumInputLength,
                placeholder: LOYALTY.trans($select.data('language-placeholder') || 'Search/Select'),
                width: '100%',
                language: {
                    inputTooShort: function () {
                        return LOYALTY.trans($select.data('language-input-too-short') || 'Please enter more characters');
                    },
                    loadingMore: function () {
                        return LOYALTY.trans($select.data('language-loading-more') || 'Loading more results…');
                    },
                    noResults: function () {
                        return LOYALTY.trans($select.data('language-no-results') || 'No results found');
                    },
                    errorLoading: function () {
                        return LOYALTY.trans($select.data('language-error-loading') || 'The results could not be loaded.');
                    },
                    searching: function () {
                        return LOYALTY.trans($select.data('language-searching') || 'Searching…');
                    }
                }
            });

            // Fetch the preselected item, and add to the control
            if ($select.data('ajax--url-preselected-item')) {
                $.ajax({
                    url: $select.data('ajax--url-preselected-item'),
                    type: 'GET',
                }).then(function (data) {
                    var results = responseDataFilter([data.data], $select);
                    // create the option and append to Select2
                    var option = new Option(results[0].text, results[0].id, true, true);
                    $select.append(option).trigger('change');
                    $select.trigger({
                        type: 'select2:select',
                        params: {
                            data: data
                        }
                    });
                });
            }
        });

        // Init CKEditor
        LOYALTY.body.find('.js-ckeditor').each(function () {
            ClassicEditor
                .create(this, {
                    removePlugins: ['EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload'],
                    toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','indent','outdent','|','blockQuote','insertTable','mediaEmbed','undo','redo'],
                    language: LOYALTY.document.children('html').attr('lang') || LOYALTY['defaultLocale']
                })
                .then(function (editor) {
                    editor.ui.view.element.classList.add('form-control');
                    editor.isReadOnly = editor.sourceElement.disabled;
                })
                .catch(function (error) {
                    console.error(error);
                })
            ;
        });

        // Toggle sidebar nav panel
        LOYALTY.body.on('click', '#sidebarToggle', onToggleSidebarNav);

        // Toggle show\hide password for input form control
        LOYALTY.body.on('click', '.show-hide-password-group .l-eye', onToggleShowHidePasswordInput);

        // Btn Upload click handler
        LOYALTY.body.on('click', '.btn-upload', onBtnUploadClick);

        // Input File change handler (for upload)
        LOYALTY.body.on('change', '.hidden-input-file', onInputFileChange);

        // Form submit handler
        LOYALTY.body.on('submit', '.form', onFormSubmit);

        // Btn restore table row handler
        LOYALTY.body.on('click', '.data-table-restore-row-btn', onBtnRestoreTableRow);

        // Btn delete table row handler
        LOYALTY.body.on('click', '.data-table-delete-row-btn', onBtnDeleteTableRow);

        // Btn add template handler
        LOYALTY.body.on('click', '.btn-add', onBtnAdd);

        // Btn delete target handler
        LOYALTY.body.on('click', '.btn-delete', onBtnDelete);

        // On Modal Showing (during showing)
        LOYALTY.body.on('show.bs.modal', '.js-bs-modal', onModalShowing);

        // On Modal Hidden (has finished being hidden)
        LOYALTY.body.on('hidden.bs.modal', '.js-bs-modal', onModalHidden);

    }); // End Dom Ready
})(jQuery);

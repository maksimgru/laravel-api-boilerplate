(function($) {
    "use strict";
    $(function () {
        var LOYALTY = window.LOYALTY || {};
        var DataTables = LOYALTY.body.find('.dataTable');
        var lengthMenu = [10, 25, 50, 75, 100];

        var getTablePluginLanguage = function ($table) {
            return {
                emptyTable:     $table.data('label-empty-table')      || LOYALTY.trans('No data available in table'),
                info:           $table.data('label-info')             || LOYALTY.trans('Showing _START_ to _END_ of _TOTAL_ entries'),
                infoEmpty:      $table.data('label-info-empty')       || LOYALTY.trans('Showing 0 to 0 of 0 entries'),
                infoFiltered:   $table.data('label-info-filtered')    || LOYALTY.trans('(filtered from _MAX_ total entries)'),
                infoPostFix:    $table.data('label-info-postfix')     || '',
                thousands:      $table.data('label-thousands')        || ',',
                decimal:        $table.data('label-decimal')          || '.',
                lengthMenu:     $table.data('label-length-menu')      || LOYALTY.trans('Show _MENU_ entries'),
                loadingRecords: $table.data('label-loading')          || LOYALTY.trans('Loading...'),
                processing:     $table.data('label-processing')       || LOYALTY.trans('Processing...'),
                search:         $table.data('label-search')           || LOYALTY.trans('Search:'),
                zeroRecords:    $table.data('label-zero-records')     || LOYALTY.trans('No matching records found'),
                paginate: {
                    first:    $table.data('label-paginate-first')     || LOYALTY.trans('First'),
                    last:     $table.data('label-paginate-last')      || LOYALTY.trans('Last'),
                    next:     $table.data('label-paginate-next')      || LOYALTY.trans('Next'),
                    previous: $table.data('label-paginate-previous')  || LOYALTY.trans('Previous')
                },
            };
        };

        var getDefaultOrderOptions = function (columns, $table) {
            var result = [];
            var columns = columns || [];
            var defaultTableOrder = $table.data('default-order');
            var defaultTableOrderColumnName = defaultTableOrder[0] || null;
            var defaultTableOrderDirection = defaultTableOrder[1] || null;

            for (var indxColumn in columns) {
                if (defaultTableOrderColumnName && defaultTableOrderDirection && defaultTableOrderColumnName === columns[indxColumn]['name']) {
                    result.push([indxColumn, defaultTableOrderDirection]);
                    break;
                } else if (columns[indxColumn]['defaultTableOrderDirection']) {
                    result.push([indxColumn, columns[indxColumn]['defaultTableOrderDirection']]);
                    break;
                }
            }

            return result;
        };

        var highlightNumberCell = function ($table, cellSelector) {
            $table.find('tbody td.' + cellSelector)
                .each(function () {
                    var $td = $(this);
                    var tdValue = $td.text().replace(',', '');
                    if (tdValue < 0) {
                        $td.addClass('text-danger');
                    }
                    if (tdValue > 0) {
                        $td.addClass('text-success');
                    }
                    if (tdValue == 0) {
                        $td.addClass('text-warning');
                    }
                })
            ;
        };

        var buildRequestSearchFieldsParams = function (columns, searchValue) {
            var result = '';
            var columns = columns || [];
            var searchValue = searchValue || null;
            var searchConditionDefault = 'ilike';
            var searchCondition = searchConditionDefault;

            columns.forEach(function (column) {
                var isSearchable = column.searchable;
                if (column.type === 'num' && isNaN(searchValue)) {
                    isSearchable = false;
                }
                if (isSearchable) {
                    searchCondition = column.searchCondition || searchConditionDefault;
                    result = result + column.name + ':' + searchCondition + ';';
                }
            });

            return result.slice(0, -1); // trim last character ";"
        };

        var requestPrepareHandler = function (columns, $table) {
            var primaryRoleId = $table.data('primary-role-id');
            return function (request) { // request data
                $table.data('draw', request.draw);
                request.search = request.search['value'];
                request.searchFields = buildRequestSearchFieldsParams(columns, request.search);
                if (!request.search.length) {
                    delete request.search;
                }
                if (!request.searchFields.length) {
                    delete request.search;
                    delete request.searchFields;
                }
                if (primaryRoleId) {
                    request.primaryRoleId = primaryRoleId;
                }
                request.perPage = request.length;
                request.page = 1 + (request.start/request.length);
                request.orderBy = columns[request.order[0]['column']]['name'];
                request.sortedBy = request.order[0]['dir'];
            }
        };

        var responseHandler = function ($table) {
            return function (response) { // response data
                var response = $.parseJSON(response);
                response.draw = $table.data('draw');
                response.recordsTotal = response.meta.pagination.total;
                response.recordsFiltered = response.meta.pagination.total;
                return JSON.stringify(response); // return JSON string
            }
        };

        var actionsColumnRender = function ($table, options) {
            var options = options || {};
            return function (data, type, row) {
                var viewRowUrl = $table.data('url-view-row');
                var restoreRowUrl = $table.data('url-restore-row');
                var deleteRowUrl = $table.data('url-delete-row');

                viewRowUrl = viewRowUrl ? viewRowUrl.replace('%id%', row.id) : '';
                restoreRowUrl = restoreRowUrl ? $table.data('url-restore-row').replace('%id%', row.id) : '';
                deleteRowUrl = deleteRowUrl ? deleteRowUrl.replace('%id%', row.id) : '';

                var viewBtnLabel = LOYALTY.trans($table.data('label-view') || 'View/Edit');
                var restoreBtnLabel = LOYALTY.trans($table.data('label-restore') || 'Restore');
                var deleteBtnLabel = restoreRowUrl.length ?
                    LOYALTY.trans($table.data('label-force-delete') || 'Delete Permanently')
                    : LOYALTY.trans($table.data('label-delete') || 'In Trash')
                ;

                var restoreRowConfirmMessage = LOYALTY.trans($table.data('message-confirm-restore') || 'Are you sure to Restore?');
                var deleteRowConfirmMessage = LOYALTY.trans(
                    $table.data(restoreRowUrl.length ? 'message-confirm-force-delete' : 'message-confirm-delete') || 'Are you sure to Delete?'
                );

                var $viewBtn = LOYALTY['viewBtn'];
                var $restoreBtn = LOYALTY['restoreBtn'];
                var $deleteBtn = LOYALTY['deleteBtn'];

                var out = '';

                if (viewRowUrl.length) {
                    $viewBtn
                        .attr('href', viewRowUrl)
                        .attr('title', viewBtnLabel)
                    ;
                    if (options['view-btn-class-name'] || false) {
                        $viewBtn
                            .addClass(options['view-btn-class-name'])
                            .children('i')
                            .addClass('fa-eye-slash')
                        ;
                    }
                    out = out + $viewBtn.get(0).outerHTML;
                }

                if (restoreRowUrl.length) {
                    $restoreBtn
                        .attr('href', restoreRowUrl)
                        .attr('title', restoreBtnLabel)
                    ;
                    if (restoreRowConfirmMessage.length) {
                        $restoreBtn.attr('data-confirm-message', restoreRowConfirmMessage);
                    }
                    out = out + $restoreBtn.get(0).outerHTML;
                }

                if (deleteRowUrl.length) {
                    $deleteBtn
                        .attr('href', deleteRowUrl)
                        .attr('title', deleteBtnLabel)
                    ;
                    if (deleteRowConfirmMessage.length) {
                        $deleteBtn.attr('data-confirm-message', deleteRowConfirmMessage);
                    }
                    out = out + $deleteBtn.get(0).outerHTML;
                }

                return out;
            };
        };

        var userColumnRender = function ($table, dataSrc) {
            return function (data, type, row) {
                if ('display' === type) {
                    var user = row[dataSrc || ''] || null;
                    var user_id = user ? user['id'] : data;
                    var user_url_template = $table ? $table.data('url-user-profile') : '';
                    var user_url = user_url_template && user_id
                        ? user_url_template.replace('%id%', user_id)
                        : '#'
                    ;

                    return user ?
                        '<a href="' + user_url + '" title="' + user['username'] + '">ID#' + user_id + '</a>'
                        + ' <span class="small">' + user['username'] + '</span>'
                        : '<span>ID#' + user_id + '</span>'
                    ;
                }

                return data;
            };
        };

        var dateTimeColumnRender = function () {
            return function (data, type, row) {
                if (type === 'display') { // display, sort, filter, search
                    return LOYALTY['dateFormat'](data);
                }
                return data;
            };
        };

        var rowDetailsFormat = function (rowData, columns) {
            var detailsData = rowData.details || [];
            var out = '';

            out += '<table cellpadding="' + columns.length + '" cellspacing="0">';
            for (var indx in detailsData) {
                out +=
                    '<tr>' +
                        '<td class="font-weight-bold text-left">' + detailsData[indx]['link'] + ' ' + detailsData[indx]['label'] + '</td>' +
                        '<td class="font-italic text-left">' + detailsData[indx]['value'] + '</td>' +
                    '</tr>'
                ;
            }
            out += '</table>';

            return out;
        };

        var initSingleTable = function (initColumns) {
            return function (elemIndx, elem) {
                var $table = $(elem);
                var loadDataApiUrl = $table.data('api-url-load-data') || '';
                if (!loadDataApiUrl.length) {return $table;}
                var loaderType = $table.data('loader-type') || LOYALTY['loaderTypes'][0];
                var $loader = LOYALTY[loaderType].clone().addClass('hidden').prependTo($table);
                var pageLength = $table.data('per-page') || 10;
                var scrollX = !!$table.data('is-scroll-x');
                var language = getTablePluginLanguage($table);
                var columns = initColumns($table);
                var orderDefaultOptions = getDefaultOrderOptions(columns, $table);

                // Init DataTable plugin
                var dataTable = $table.DataTable({
                    ajax: {
                        url: loadDataApiUrl,
                        type: 'GET',
                        dataSrc: 'data',
                        data: requestPrepareHandler(columns, $table),
                        dataFilter: responseHandler($table),
                        beforeSend: function () {
                            $loader.removeClass('hidden');
                        },
                        complete: function () {
                            $loader.addClass('hidden');
                            highlightNumberCell($table, 'currency');
                        }
                    },
                    processing:  true,
                    serverSide:  true,
                    searchDelay: 500, // ms
                    scrollX:     scrollX,
                    pageLength:  pageLength,
                    language:    language,
                    lengthMenu:  lengthMenu,
                    order:       orderDefaultOptions,
                    columns:     columns
                });

                // Add event listener for opening and closing details
                $table.on('click', '.btn-details', function (e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var $tr = $btn.closest('tr');
                    var $row = dataTable.row($tr);

                    if ($row.child.isShown()) {
                        $row.child.hide();
                        $btn.removeClass('btn-danger');
                    } else {
                        $row.child(
                            rowDetailsFormat($row.data(), columns)
                        ).show();
                        $btn.addClass('btn-danger');
                    }
                    $btn.children('i')
                        .toggleClass('fa-eye-slash')
                    ;
                });
            };
        };

        // Init Users DataTables
        var initColumnsUsers = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'email', name: 'email', type: 'string', searchable: true},
                { data: 'username', name: 'username', type: 'string', searchable: true},
                { data: 'primary_role_id', name: 'roles..primary_role_id;roles.name', type: 'string', searchable: false,
                    render: function (data, type, row) {
                        return row['primary_role']['name'];
                    }
                },
                { data: 'is_active', name: 'is_active', searchable: false,
                    render: function (data, type, row) {
                        if (type === 'display') { // display, sort, filter, search
                            return data
                                ? '<span class="text-success">' + LOYALTY.trans($table.data('label-is-active') || 'Activated') + '</span>'
                                : '<span class="text-danger">' + LOYALTY.trans($table.data('label-is-not-active') || 'Deactivated') + '</span>'
                            ;
                        }
                        return data;
                    }
                },
                { data: 'properties.balance', name: 'properties->balance', type: 'num', className: 'currency font-weight-bold', searchable: true, searchCondition: '=',
                    render: $.fn.dataTable.render.number(
                        getTablePluginLanguage($table).thousands,
                        getTablePluginLanguage($table).decimal,
                        2,
                        '<span>',
                        '</span>'
                    )
                },
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, defaultTableOrderDirection: 'desc', render: dateTimeColumnRender()},
                { data: null, name: 'actions', type: 'html', searchable: false, orderable: false, render: actionsColumnRender($table)},
            ];
        };
        DataTables.filter('.users').each(initSingleTable(initColumnsUsers));

        // Init Visit Place Categories DataTables
        var initColumnsVisitPlaceCategories = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'title', name: 'title', type: 'string', searchable: true, defaultTableOrderDirection: 'asc'},
                { data: 'slug', name: 'slug', type: 'string', searchable: true},
                { data: 'description', name: 'description', type: 'string', className: 'text-left', searchable: true, orderable: false,
                    render: $.fn.dataTable.render.ellipsis(
                        $table.data('ellipsis') || 120,
                        true,
                        false,
                        true
                    )
                },
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
                { data: null, name: 'actions', type: 'html', searchable: false, orderable: false, render: actionsColumnRender($table)},
            ];
        };
        DataTables.filter('.visit-place-categories').each(initSingleTable(initColumnsVisitPlaceCategories));

        // Init Visit Places DataTables
        var initColumnsVisitPlaces = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'title', name: 'title', type: 'string', searchable: true, defaultTableOrderDirection: 'asc'},
                { data: 'slug', name: 'slug', type: 'string', searchable: true},
                { data: 'properties.cash_back', name: 'properties->cash_back', type: 'num', className: 'currency font-weight-bold', searchable: true, searchCondition: '=',
                    render: $.fn.dataTable.render.number(
                        getTablePluginLanguage($table).thousands,
                        getTablePluginLanguage($table).decimal,
                        1,
                        '<span>',
                        '</span>'
                    )
                },
                { data: 'category_id', name: 'visit_place_categories..category_id;visit_place_categories.title', type: 'string', searchable: false,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var category_url = $table.data('url-visit-place-category');
                            var category_id = row['category'] ? row['category']['id'] : null;
                            category_url = category_url && category_id ? category_url.replace('%id%', category_id) : '#';

                            return category_url ?
                                '<a href="' + category_url + '" title="' + row['category']['title'] + '">' + row['category']['title'] + '</a>'
                                : '-'
                            ;
                        }

                        return data;
                    }
                },
                { data: 'business_id', name: 'users..business_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'business')},
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
                { data: null, name: 'actions', type: 'html', searchable: false, orderable: false, render: actionsColumnRender($table)},
            ];
        };
        DataTables.filter('.visit-places').each(initSingleTable(initColumnsVisitPlaces));

        // Init Visit Place Comments DataTables
        var initColumnsVisitPlaceComments = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'content', name: 'content', type: 'string', className: 'text-left', searchable: true, orderable: false,
                    render: $.fn.dataTable.render.ellipsis(
                        $table.data('ellipsis') || 120,
                        true,
                        false,
                        true
                    )
                },
                { data: 'visit_place_id', name: 'visit_places..visit_place_id;visit_places.title', type: 'string', searchable: false,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var visit_place_url = $table.data('url-visit-place');
                            var visit_place_id = row['visit_place'] ? row['visit_place']['id'] : null;
                            visit_place_url = visit_place_url && visit_place_id ? visit_place_url.replace('%id%', visit_place_id) : '#';

                            return visit_place_url ?
                                '<a href="' + visit_place_url + '" title="' + row['visit_place']['title'] + '">' + row['visit_place']['title'] + '</a>'
                                : '-'
                            ;
                        }

                        return data;
                    }
                },
                { data: 'user_id', name: 'users..user_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'author')},
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
                { data: null, name: 'actions', type: 'html', searchable: false, orderable: false, render: actionsColumnRender($table)},
            ];
        };
        DataTables.filter('.visit-place-comments').each(initSingleTable(initColumnsVisitPlaceComments));

        // Init Visit Place Ratings DataTables
        var initColumnsVisitPlaceRatings = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'value', name: 'value', type: 'num', searchable: true},
                { data: 'visit_place_id', name: 'visit_places..visit_place_id;visit_places.title', type: 'string', searchable: false,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var visit_place_url = $table.data('url-visit-place');
                            var visit_place_id = row['visit_place'] ? row['visit_place']['id'] : null;
                            visit_place_url = visit_place_url && visit_place_id ? visit_place_url.replace('%id%', visit_place_id) : '#';

                            return visit_place_url ?
                                '<a href="' + visit_place_url + '" title="' + row['visit_place']['title'] + '">' + row['visit_place']['title'] + '</a>'
                                : '-'
                            ;
                        }

                        return data;
                    }
                },
                { data: 'user_id', name: 'users..user_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'user')},
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
            ];
        };
        DataTables.filter('.visit-place-ratings').each(initSingleTable(initColumnsVisitPlaceRatings));

        // Init Pages DataTables
        var initColumnsPages = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'title', name: 'title', type: 'string', searchable: true, defaultTableOrderDirection: 'asc'},
                { data: 'slug', name: 'slug', type: 'string', searchable: true},
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
                { data: 'updated_at', name: 'updated_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
                { data: null, name: 'actions', type: 'html', searchable: false, orderable: false, render: actionsColumnRender($table)},
            ];
        };
        DataTables.filter('.pages').each(initSingleTable(initColumnsPages));

        // Init Roles DataTables
        var initColumnsRoles = function ($table) {
            return [
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'name', name: 'name', type: 'string', searchable: true, defaultTableOrderDirection: 'asc'},
                { data: 'description', name: 'description', type: 'string', searchable: true},
            ];
        };
        DataTables.filter('.roles').each(initSingleTable(initColumnsRoles));

        // Init Transactions DataTables
        var initColumnsTransactions = function ($table) {
            return [
                { data: null, name: 'actions', type: 'html', searchable: false, orderable: false,
                    render: actionsColumnRender($table, {
                        'view-btn-class-name': 'btn-details',
                    })
                },
                { data: 'id', name: 'id', type: 'num', searchable: false},
                { data: 'pay_value', name: 'pay_value', type: 'num', className: 'currency font-weight-bold', searchable: true, searchCondition: '=',
                    render: $.fn.dataTable.render.number(
                        getTablePluginLanguage($table).thousands,
                        getTablePluginLanguage($table).decimal,
                        2,
                        '<span>',
                        '</span>'
                    )
                },
                { data: 'status_id', name: 'status_id', searchable: false,
                    render: function (data, type, row) {
                        if (type === 'display') { // display, sort, filter, search
                            var statusNameToTextColorMap = {
                                'in-progress': 'text-warning',
                                'completed':   'text-success',
                                'failed':      'text-danger',
                            };
                            var statusName = row['status'] ? row['status']['name'] : 'in-progress';
                            var textColorClass = statusNameToTextColorMap[statusName] || '';
                            return '<span class="' + textColorClass + '">' + LOYALTY.trans($table.data('label-status-' + statusName) || 'unknown') + '</span>';
                        }
                        return data;
                    }
                },
                { data: 'visit_place_id', name: 'visit_places..visit_place_id;visit_places.title', type: 'string', searchable: false,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var visit_place = row['visit_place'] || null;
                            var visit_place_id = visit_place ? visit_place['id'] : data;
                            var visit_place_url_template = $table.data('url-visit-place');
                            var visit_place_url = visit_place_url_template && visit_place_id
                                ? visit_place_url_template.replace('%id%', visit_place_id)
                                : '#'
                            ;

                            return visit_place ?
                                '<a href="' + visit_place_url + '" title="' + visit_place['title'] + '">' + visit_place['title']  + '</a>'
                                : '<span>ID#' + visit_place_id + '</span>'
                            ;
                        }

                        return data;
                    }
                },
                { data: 'tourist_id', name: 'users..tourist_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'tourist')},
                { data: 'worker_id', name: 'users..worker_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'worker')},
                { data: 'referral_business_id', name: 'users..referral_business_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'referral_business')},
                { data: 'referral_manager_id', name: 'users..referral_manager_id;users.username', type: 'string', searchable: false, render: userColumnRender($table, 'referral_manager')},
                { data: 'created_at', name: 'created_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
                { data: 'updated_at', name: 'updated_at', type: 'date', searchable: false, render: dateTimeColumnRender()},
            ];
        };
        DataTables.filter('.transactions').each(initSingleTable(initColumnsTransactions));

    }); // End Dom Ready
})(jQuery);

@php
    $table_title = $table_title ?? __('Roles');
    $table_load_data_url = $table_load_data_url ?? '';
    $default_order = $default_order ?? [$route_constants::DEFAULT_ORDER_BY, $route_constants::DEFAULT_SORT_ORDER_DIRECTIONS];

    $url_view_row = $url_view_row ?? '';
    $url_restore_row = $url_restore_row ?? '';
    $url_delete_row = $url_delete_row ?? '';
@endphp

<div class="row mb-5 l-table l-table-roles">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><i class="fa fa-table mr-1"></i>{{ $table_title }}</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered dataTable roles"
                           width="100%"
                           cellspacing="0"

                           data-loader-type="progress"

                           data-label-empty-table=""
                           data-label-zero-records=""
                           data-label-info=""
                           data-label-info-empty=""
                           data-label-length-menu=""
                           data-label-loading=""
                           data-label-processing=""
                           data-label-search=""
                           data-label-paginate-first=""
                           data-label-paginate-last=""
                           data-label-paginate-next=""
                           data-label-paginate-previous=""

                           data-api-url-load-data="{{ $table_load_data_url }}"
                           data-per-page="{{ config('repository.pagination.limit') }}"
                           data-default-order="{{ json_encode($default_order) }}"
                           data-is-scroll-x="1"
                    >
                        <thead>
                            <th>{{ __('ID') }}</th>
                            <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Name') }}</span></th>
                            <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Description') }}</span></th>
                        </thead>
                        <tfoot>
                            <th>{{ __('ID') }}</th>
                            <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Name') }}</span></th>
                            <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Description') }}</span></th>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

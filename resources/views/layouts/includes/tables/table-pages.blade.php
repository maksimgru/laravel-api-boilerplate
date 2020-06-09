@php
    $table_title = $table_title ?? __('Pages');
    $table_load_data_url = $table_load_data_url ?? '';
    $default_order = $default_order ?? [$route_constants::DEFAULT_ORDER_BY, $route_constants::DEFAULT_SORT_ORDER_DIRECTIONS];

    $url_view_row = $url_view_row ?? '';
    $url_restore_row = $url_restore_row ?? '';
    $url_delete_row = $url_delete_row ?? '';
@endphp

<div class="row mb-5 l-table l-table-pages">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><i class="fa fa-table mr-1"></i>{{ $table_title }}</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered dataTable pages"
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

                           data-label-view=""
                           data-label-restore=""
                           data-label-delete=""
                           data-label-force-delete=""

                           data-message-confirm-restore="{{ __('Are you sure to Restore this page?') }}"
                           data-message-confirm-delete="{{ __('Are you sure to move into Trash this page?') }}"
                           data-message-confirm-force-delete="{{ __('Are you sure to Delete Permanently this page?') }}"

                           data-api-url-load-data="{{ $table_load_data_url }}"
                           data-url-view-row="{{ $url_view_row }}"
                           data-url-restore-row="{{ $url_restore_row }}"
                           data-url-delete-row="{{ $url_delete_row }}"
                           data-per-page="{{ config('repository.pagination.limit') }}"
                           data-default-order="{{ json_encode($default_order) }}"
                           data-is-scroll-x="1"
                    >
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Title') }}</span></th>
                                <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Slug') }}</span></th>
                                <th>{{ __('CreatedAt') }}</th>
                                <th>{{ __('UpdatedAt') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Title') }}</span></th>
                                <th><span data-toggle="tooltip" data-placement="top" title="{{ __('Searchable') }}">{{ __('Slug') }}</span></th>
                                <th>{{ __('CreatedAt') }}</th>
                                <th>{{ __('UpdatedAt') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

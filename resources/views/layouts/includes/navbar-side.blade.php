@php
    $active_route_name = request()->route()->getName();
    $queried_primary_role_id = (int) request()->query('primary_role_id');
    $roles_map = \Role::getRolesMap();

    $is_route_home = $active_route_name === $route_constants::ROUTE_NAME_WEB_HOME;

    $is_route_users = $active_route_name === $route_constants::ROUTE_NAME_WEB_USERS;
    $is_route_all_users = $is_route_users && !$queried_primary_role_id;
    $is_route_admin_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_ADMIN];
    $is_route_manager_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_MANAGER];
    $is_route_business_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_BUSINESS];
    $is_route_worker_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_WORKER];
    $is_route_tourist_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_TOURIST];
    $is_route_visited_tourist_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_TOURIST] && (bool) request()->query('visited');
    $is_route_referrals_tourist_users = $is_route_users && $queried_primary_role_id === $roles_map[$role_constants::ROLE_TOURIST] && (bool) request()->query('referrals');

    $is_route_visit_places = $active_route_name === $route_constants::ROUTE_NAME_WEB_VISIT_PLACES;
    $is_route_visit_place_categories = $active_route_name === $route_constants::ROUTE_NAME_WEB_VISIT_PLACE_CATEGORIES;

    $is_route_roles = $active_route_name === $route_constants::ROUTE_NAME_WEB_ROLES;
    $is_route_pages = $active_route_name === $route_constants::ROUTE_NAME_WEB_PAGES;
    $is_route_transactions = $active_route_name === $route_constants::ROUTE_NAME_WEB_TRANSACTIONS;

    $is_route_charts = $active_route_name === $route_constants::ROUTE_NAME_WEB_CHARTS;
    $is_route_comments = $active_route_name === $route_constants::ROUTE_NAME_WEB_COMMENTS;
    $is_route_settings = $active_route_name === $route_constants::ROUTE_NAME_WEB_SETTINGS;
    $is_route_trash = $active_route_name === $route_constants::ROUTE_NAME_WEB_TRASH;
@endphp

<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">


            {{-- CORE --}}
            <div class="sb-sidenav-menu-heading">{{ __('Core') }}</div>

            <a class="nav-link @if($is_route_home) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_HOME) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-tachometer"></i></div>
                {{ __('Dashboard') }}
            </a>


            {{-- INTERFACE --}}
            <div class="sb-sidenav-menu-heading">{{ __('Interface') }}</div>

            <a class="nav-link @if(!$is_route_users) collapsed @else active @endif"
               href="#"
               data-toggle="collapse"
               data-target="#collapseUsers"
               aria-expanded="false"
               aria-controls="collapseVisitPlaces"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-user"></i></div>
                {{ __('Users') }}
                <div class="sb-sidenav-collapse-arrow"><i class="fa fa-angle-down"></i></div>
            </a>
            <div class="collapse @if($is_route_users) show @endif"
                 id="collapseUsers"
                 data-parent="#sidenavAccordion"
                 aria-labelledby="headingOne"
            >
                <nav class="sb-sidenav-menu-nested nav">
                    @if(Auth::user()->isAdmin())
                        <a class="nav-link @if($is_route_all_users) active @endif"
                           href="{{ route($route_constants::ROUTE_NAME_WEB_USERS) }}"
                        >
                            {{ __('All') }}
                        </a>
                        <a class="nav-link @if($is_route_admin_users) active @endif"
                           href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_ADMIN)]) }}"
                        >
                            {{ __('Admins') }}
                        </a>
                        <a class="nav-link @if($is_route_manager_users) active @endif"
                           href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_MANAGER)]) }}"
                        >
                            {{ __('Managers') }}
                        </a>
                    @endif

                    @if(!Auth::user()->isPrimaryRoleBusiness())
                        <a class="nav-link @if($is_route_business_users) active @endif"
                           href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_BUSINESS)]) }}"
                        >
                            {{ __('Businesses') }}
                        </a>
                    @endif

                    <a class="nav-link @if($is_route_worker_users) active @endif"
                       href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_WORKER)]) }}"
                    >
                        {{ __('Workers') }}
                    </a>
                    <a class="nav-link @if($is_route_tourist_users) active @else collapsed @endif"
                       @if(!Auth::user()->isAdmin())
                           data-toggle="collapse"
                           data-target="#collapseTourists"
                       @endif
                       href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_TOURIST)]) }}"
                    >
                        {{ __('Tourists') }}
                        @if(!Auth::user()->isAdmin())
                            <div class="sb-sidenav-collapse-arrow"><i class="fa fa-angle-down"></i></div>
                        @endif
                    </a>
                        @if(!Auth::user()->isAdmin())
                            <div class="collapse @if($is_route_tourist_users) show @endif"
                                 id="collapseTourists"
                                 data-parent="#collapseUsers"
                                 aria-labelledby="headingOne"
                            >
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link @if($is_route_visited_tourist_users) active @endif"
                                       href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_TOURIST), 'visited' => 1]) }}"
                                    >
                                        {{ __('Visited Tourists') }}
                                    </a>
                                    <a class="nav-link @if($is_route_referrals_tourist_users) active @endif"
                                       href="{{ route($route_constants::ROUTE_NAME_WEB_USERS, ['primary_role_id' => \Role::getIdBy($role_constants::ROLE_TOURIST), 'referrals' => 1]) }}"
                                    >
                                        {{ __('Referral Tourists') }}
                                    </a>
                                </nav>
                            </div>
                        @endif
                </nav>
            </div>

            <a class="nav-link
               @if(!\in_array(
                     $active_route_name,
                     [$route_constants::ROUTE_NAME_WEB_VISIT_PLACES, $route_constants::ROUTE_NAME_WEB_VISIT_PLACE_CATEGORIES],
                     true
                  )
               ) collapsed @else active @endif"
               href="#"
               data-toggle="collapse"
               data-target="#collapseVisitPlaces"
               aria-expanded="false"
               aria-controls="collapseVisitPlaces"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-map-marker"></i></div>
                {{ __('Visit Places') }}
                <div class="sb-sidenav-collapse-arrow"><i class="fa fa-angle-down"></i></div>
            </a>
            <div class="collapse
                 @if(\in_array(
                      $active_route_name,
                      [$route_constants::ROUTE_NAME_WEB_VISIT_PLACES, $route_constants::ROUTE_NAME_WEB_VISIT_PLACE_CATEGORIES],
                      true
                    )
                 ) show @endif"
                 id="collapseVisitPlaces"
                 data-parent="#sidenavAccordion"
                 aria-labelledby="headingOne"
            >
                <nav class="sb-sidenav-menu-nested nav">
                    <a class="nav-link @if($is_route_visit_places) active @endif"
                       href="{{ route($route_constants::ROUTE_NAME_WEB_VISIT_PLACES) }}"
                    >
                        {{ __('Visit Places') }}
                    </a>
                    <a class="nav-link @if($is_route_visit_place_categories) active @endif"
                       href="{{ route($route_constants::ROUTE_NAME_WEB_VISIT_PLACE_CATEGORIES) }}"
                    >
                        {{ __('Categories') }}
                    </a>
                </nav>
            </div>

            @if(Auth::user()->isAdmin())
            <a class="nav-link @if($is_route_roles) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_ROLES) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-lock"></i></div>
                {{ __('Roles') }}
            </a>
            <a class="nav-link @if($is_route_pages) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_PAGES) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-pencil"></i></div>
                {{ __('Pages') }}
            </a>
            @endif

            <a class="nav-link @if($is_route_comments) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_COMMENTS) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-comment"></i></div>
                {{ __('Comments') }}
            </a>

            <a class="nav-link @if($is_route_transactions) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_TRANSACTIONS) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-bank"></i></div>
                {{ __('Transactions') }}
            </a>


            {{-- ADDONS --}}
            <div class="sb-sidenav-menu-heading">{{ __('Addons') }}</div>

            <a class="hidden nav-link @if($is_route_charts) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_CHARTS) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-bar-chart-o"></i></div>
                {{ __('Metrics') }}
            </a>

            @if(Auth::user()->isAdmin())
            <a class="nav-link @if($is_route_settings) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_SETTINGS) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-gears"></i></div>
                {{ __('Settings') }}
            </a>
            <a class="nav-link @if($is_route_trash) active @endif"
               href="{{ route($route_constants::ROUTE_NAME_WEB_TRASH) }}"
            >
                <div class="sb-nav-link-icon"><i class="fa fa-trash"></i></div>
                {{ __('Trash') }}
            </a>
            @endif

        </div>
    </div>

    <div class="sb-sidenav-footer"
         data-toggle="tooltip"
         data-placement="top"
         title="{{ __('Go to profile') }}"
         onclick="window.location='{{route($route_constants::ROUTE_NAME_WEB_MY_PROFILE)}}'"
    >
        <div class="small">{{__('Logged in as:')}}</div>
        <div><strong>{{Auth::user()->primaryRole->name}}</strong></div>
        {{Auth::user()->email}}
    </div>

</nav>

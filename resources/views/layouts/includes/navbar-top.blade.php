<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand w-auto" href="{{route($route_constants::ROUTE_NAME_WEB_HOME)}}">
        {{ config('app.name') }}
    </a>
    <button class="btn btn-link btn-sm order-0" id="sidebarToggle">
        <i class="fa fa-bars"></i>
    </button>
    <ul class="navbar-nav d-md-inline-block form-inline ml-auto mr-0 my-2 my-md-0">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle nav-avatar"
               id="userDropdown"
               href="#"
               role="button"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false"
            >
                <img src="{{Auth::user()->getFirstMediaThumbsUrls('avatar')['thumbs']['thumb-small']}}"
                     data-toggle="tooltip"
                     data-placement="bottom"
                     alt="{{Auth::user()->username}}"
                     title="{{Auth::user()->username}}"
                />
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item @if(request()->route()->getName() === $route_constants::ROUTE_NAME_WEB_MY_PROFILE) active @endif"
                   href="{{route($route_constants::ROUTE_NAME_WEB_MY_PROFILE)}}">
                    {{ __('Profile') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item"
                   href="{{route($route_constants::ROUTE_NAME_WEB_LOGOUT)}}"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                >
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" class="hidden" action="{{route($route_constants::ROUTE_NAME_WEB_LOGOUT)}}" method="POST">@csrf</form>
            </div>
        </li>
    </ul>
</nav>

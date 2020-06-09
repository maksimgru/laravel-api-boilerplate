<?php

namespace App\Constants;

/**
 * Class RouteConstants
 *
 * @package App\Constants
 */
class RouteConstants
{
    /**
     * Web
     */
    public const ROUTE_NAME_WEB_LOGIN = 'login';
    public const ROUTE_NAME_WEB_LOGOUT = 'logout';

    public const ROUTE_NAME_WEB_PASSWORD_REQUEST = 'password.request';
    public const ROUTE_NAME_WEB_PASSWORD_RESET = 'password.reset';
    public const ROUTE_NAME_WEB_PASSWORD_EMAIL = 'password.email';
    public const ROUTE_NAME_WEB_PASSWORD_CONFIRM = 'password.confirm';
    public const ROUTE_NAME_WEB_PASSWORD_UPDATE = 'password.update';

    public const ROUTE_NAME_WEB_EMAIL_VERIFY = 'verification.verify';
    public const ROUTE_NAME_WEB_EMAIL_VERIFY_NOTICE = 'verification.notice';
    public const ROUTE_NAME_WEB_EMAIL_VERIFY_RESEND = 'verification.resend';

    public const ROUTE_NAME_WEB_HOME = 'home';

    public const ROUTE_NAME_WEB_USERS = 'users';
    public const ROUTE_NAME_WEB_MY_PROFILE = 'my-profile';
    public const ROUTE_NAME_WEB_UPDATE_MY_PROFILE = 'update-my-profile';
    public const ROUTE_NAME_WEB_USER_PROFILE = 'user-profile';
    public const ROUTE_NAME_WEB_UPDATE_USER_PROFILE = 'update-user-profile';
    public const ROUTE_NAME_WEB_NEW_USER_PROFILE = 'new-user-profile';
    public const ROUTE_NAME_WEB_CREATE_NEW_USER_PROFILE = 'create-new-user-profile';
    public const ROUTE_NAME_WEB_DELETE_USER_MEDIA = 'delete-user-media';


    public const ROUTE_NAME_WEB_PAGES = 'pages';
    public const ROUTE_NAME_WEB_PAGE_VIEW = 'page-view';
    public const ROUTE_NAME_WEB_PAGE_UPDATE = 'page-update';
    public const ROUTE_NAME_WEB_PAGE_VIEW_NEW = 'page-view-new';
    public const ROUTE_NAME_WEB_PAGE_CREATE_NEW = 'page-create-new';
    public const ROUTE_NAME_WEB_DELETE_PAGE_MEDIA = 'delete-page-media';

    public const ROUTE_NAME_WEB_ROLES = 'roles';
    public const ROUTE_NAME_WEB_CHARTS = 'charts';
    public const ROUTE_NAME_WEB_COMMENTS = 'comments';
    public const ROUTE_NAME_WEB_SETTINGS = 'settings';
    public const ROUTE_NAME_WEB_TRASH = 'trash';

    /**
     * Api
     */
    public const ROUTE_NAME_AUTH_BASIC_TOKEN = 'api-auth-basic-token';
    public const ROUTE_NAME_LOGIN = 'api-login';
    public const ROUTE_NAME_LOGOUT = 'api-logout';
    public const ROUTE_NAME_REFRESH_TOKEN = 'api-refresh-token';
    public const ROUTE_NAME_PASSWORD_RESET = 'api-password-reset';
    public const ROUTE_NAME_PASSWORD_RESTORE = 'api-password-restore';
    public const ROUTE_NAME_EMAIL_VERIFY = 'verification-verify';
    public const ROUTE_NAME_EMAIL_VERIFY_NOTICE = 'verification-notice';
    public const ROUTE_NAME_EMAIL_VERIFY_RESEND = 'verification-resend';
    public const ROUTE_NAME_SOCIAL_LOGIN = 'api-social-login';
    public const ROUTE_NAME_SOCIAL_PROVIDER_CALLBACK = 'api-provider-callback';
    public const ROUTE_NAME_SOCIAL_PROVIDER_BY_TOKEN = 'api-provider--by-token';

    public const ROUTE_NAME_USERS = 'api-users';
    public const ROUTE_NAME_USER = 'api-user';
    public const ROUTE_NAME_TOURIST_USERS = 'api-tourist-users';
    public const ROUTE_NAME_TOURIST_USER = 'api-tourist-user';
    public const ROUTE_NAME_DELETED_USERS = 'api-users-soft-deleted';
    public const ROUTE_NAME_DELETE_USER = 'api-user-soft-delete';
    public const ROUTE_NAME_FORCE_DELETE_USER = 'api-user-force-delete';
    public const ROUTE_NAME_RESTORE_USER = 'api-user-restore';

    public const ROUTE_NAME_MANAGER_OWN_BUSINESS_USERS = 'api-manager-own-business-users';
    public const ROUTE_NAME_MANAGER_OWN_BUSINESS_USER = 'api-manager-own-business-user';
    public const ROUTE_NAME_MANAGER_OWN_BUSINESS_WORKER_USERS = 'api-manager-own-business-worker-users';
    public const ROUTE_NAME_MANAGER_VISITED_TOURIST_USERS = 'api-manager-visited-tourist-users';
    public const ROUTE_NAME_MANAGER_REFERRAL_TOURIST_USERS = 'api-manager-referral-tourist-users';
    public const ROUTE_NAME_BUSINESS_OWN_WORKER_USERS = 'api-business-own-worker-users';
    public const ROUTE_NAME_BUSINESS_VISITED_TOURIST_USERS = 'api-business-visited-tourist-users';
    public const ROUTE_NAME_BUSINESS_REFERRAL_TOURIST_USERS = 'api-business-referral-tourist-users';

    public const ROUTE_NAME_PAGES = 'api-pages';
    public const ROUTE_NAME_DELETED_PAGES = 'api-soft-deleted-pages';
    public const ROUTE_NAME_DELETE_PAGE = 'api-soft-delete-page';
    public const ROUTE_NAME_FORCE_DELETE_PAGE = 'api-force-delete-page';
    public const ROUTE_NAME_RESTORE_PAGE = 'api-restore-page';

    public const ROUTE_NAME_ROLES = 'roles';

    public const REQUEST_FIELD_NAME_PER_PAGE = 'per_page';
    public const REQUEST_FIELDS_NAMES_FOR_SANITIZE = [
        'email',
        'url',
    ];
    public const REQUEST_FIELDS_NAMES_EXCEPT_SANITIZE = [
        'password',
        'password_confirmation',
    ];

    public const AVAILABLE_SEARCH_JOIN = ['and', 'or'];
    public const DEFAULT_SEARCH_JOIN = 'or';

    public const AVAILABLE_SORT_ORDER_DIRECTIONS = ['asc', 'desc'];
    public const DEFAULT_SORT_ORDER_DIRECTIONS = 'asc';
    public const DEFAULT_ORDER_BY = 'id';
}

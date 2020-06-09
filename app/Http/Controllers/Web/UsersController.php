<?php

namespace App\Http\Controllers\Web;

use App\Constants\RoleConstants;
use App\Constants\RouteConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Http\Requests\User\DeleteUserMediaRequest;
use App\Http\Requests\User\Web\GetUserRequest;
use App\Http\Requests\User\Web\UpdateUserRequest;
use App\Http\Tasks\User\RegisterUserTask;
use App\Http\Tasks\User\UpdateUserTask;
use App\Models\Role;
use App\Models\User;
use App\Transformers\UserTransformer;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Spatie\MediaLibrary\Models\Media;

class UsersController extends Controller
{
    /**
     * @return Redirector | Renderable
     * @throws Exception
     */
    public function index() {
        /** @var User $authUser */
        $authUser = auth()->user();
        $availableRoles = Role::getRolesMap();
        $queriedPrimaryRoleId = (int) request()->query('primary_role_id');
        $queriedPrimaryRole = Role::find($queriedPrimaryRoleId, ['name']);
        $queriedPrimaryRoleName = $queriedPrimaryRole ? $queriedPrimaryRole->name : '';
        $tableLoadDataUrl = null;

        $usersListPageTitle = $queriedPrimaryRoleName
            ? trans(':role Users', ['role' => ucfirst($queriedPrimaryRoleName)])
            : trans('All Users')
        ;

        $addNewUserBtnLabel = $queriedPrimaryRoleName
            ? trans('Add New :role', ['role' => ucfirst($queriedPrimaryRoleName)])
            : trans('Add New User')
        ;

        $addNewUserBtnHref = $queriedPrimaryRole
            ? route(RouteConstants::ROUTE_NAME_WEB_NEW_USER_PROFILE, ['primary_role_id' => $queriedPrimaryRoleId])
            : route(RouteConstants::ROUTE_NAME_WEB_NEW_USER_PROFILE)
        ;

        if ($authUser->isAdmin()) {
            $availableRoles[] = 0;
            $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_USERS);
        }

        if ($authUser->isPrimaryRoleManager()) {
            unset($availableRoles[RoleConstants::ROLE_ADMIN], $availableRoles[RoleConstants::ROLE_MANAGER]);
            if ($queriedPrimaryRoleName !== RoleConstants::ROLE_BUSINESS) {
                $addNewUserBtnHref = $addNewUserBtnLabel = null;
            }
            if ($queriedPrimaryRoleName === RoleConstants::ROLE_BUSINESS) {
                $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_MANAGER_OWN_BUSINESS_USERS);
            }
            if ($queriedPrimaryRoleName === RoleConstants::ROLE_WORKER) {
                $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_MANAGER_OWN_BUSINESS_WORKER_USERS);
            }
            if ($queriedPrimaryRoleName === RoleConstants::ROLE_TOURIST) {
                $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_MANAGER_VISITED_TOURIST_USERS);
                if ((bool) request()->query('referrals')) {
                    $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_MANAGER_REFERRAL_TOURIST_USERS);
                }
            }
        }

        if ($authUser->isPrimaryRoleBusiness()) {
            unset($availableRoles[RoleConstants::ROLE_ADMIN], $availableRoles[RoleConstants::ROLE_MANAGER], $availableRoles[RoleConstants::ROLE_BUSINESS]);
            if ($queriedPrimaryRoleName !== RoleConstants::ROLE_WORKER) {
                $addNewUserBtnHref = $addNewUserBtnLabel = null;
            }
            if ($queriedPrimaryRoleName === RoleConstants::ROLE_WORKER) {
                $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_BUSINESS_OWN_WORKER_USERS);
            }
            if ($queriedPrimaryRoleName === RoleConstants::ROLE_TOURIST) {
                $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_BUSINESS_VISITED_TOURIST_USERS);
                if ((bool) request()->query('referrals')) {
                    $tableLoadDataUrl = apiRoute(RouteConstants::ROUTE_NAME_BUSINESS_REFERRAL_TOURIST_USERS);
                }
            }
        }

        if (!\in_array($queriedPrimaryRoleId, $availableRoles, true)) {
            return redirect(route(RouteConstants::ROUTE_NAME_WEB_HOME));
        }

        return view('admin-pages.users', [
            'add_new_user_btn_label' => $addNewUserBtnLabel,
            'add_new_user_btn_href'  => $addNewUserBtnHref,
            'users_list_page_title'  => $usersListPageTitle,
            'table_load_data_url'    => $tableLoadDataUrl,
            'default_order'          => ['created_at', 'desc'],
            'url_view_row'           => route(RouteConstants::ROUTE_NAME_WEB_USER_PROFILE, ['user_id' => '%id%']),
            'url_delete_row'         => apiRoute(RouteConstants::ROUTE_NAME_DELETE_USER, ['user_id' => '%id%']),
        ]);
    }

    /**
     * @return Renderable
     * @throws Exception
     */
    public function showCurrentUser(): Renderable {
        /** @var User $authUser */
        /** @var User $user */
        $authUser = $user = auth()->user()->loadMissing(['parentManager', 'parentBusiness']);
        $userData = (new UserTransformer())->transform($user);

        return view('admin-pages.user-profile',
            [
                'user'                => $userData,
                'roles'               => Role::getRolesMap(),
                'can_edit_profile'    => $authUser->canEditProfileFor($user->getKey()),
                'can_edit_balance'    => $authUser->canEditBalanceFor($user->getKey()),
                'can_edit_commission' => $authUser->canEditCommissionFor($user->getKey()),
                'can_edit_role'       => $authUser->canEditRoleFor($user->getKey()),
                'can_activated'       => $authUser->canActivatedOf($user->getKey()),
                'has_verified_email'  => $user->hasVerifiedEmail(),
                'is_auth_user'        => true,
            ]
        );
    }

    /**
     * @param Request        $request
     * @param UpdateUserTask $updateUserTask
     *
     * @return RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     * @throws \Illuminate\Database\QueryException
     * @throws \Dingo\Api\Exception\StoreResourceFailedException
     * @throws \App\Exceptions\Validation\ValidationFailedException
     * @throws \LogicException
     */
    public function updateCurrentUser(
        Request $request,
        UpdateUserTask $updateUserTask
    ): RedirectResponse {
        $user = $updateUserTask->run(
            $request->getSanitizedInputs(
                [
                    'except' => array_merge(auth()->user()::getClosedAttributes(), ['manager_id', 'business_id'])
                ]
            ),
            auth()->user()
        );

        return back()
            ->exceptInput('manager_id', 'business_id')
            ->withErrors($user->getValidationErrors())
        ;
    }

    /**
     * @param GetUserRequest $request
     * @param int            $userId
     *
     * @return Renderable
     * @throws Exception
     */
    public function showUser(
        GetUserRequest $request,
        int $userId
    ): Renderable {
        /** @var User $authUser */
        /** @var User $user */
        $authUser = auth()->user();
        $user = User::with(['parentManager', 'parentBusiness'])->findOrFail($userId);
        $userData = (new UserTransformer())->transform($user);

        return view('admin-pages.user-profile',
            [
                'user'                => $userData,
                'roles'               => Role::getRolesMap(),
                'can_edit_profile'    => $authUser->canEditProfileFor($user->getKey()),
                'can_edit_balance'    => $authUser->canEditBalanceFor($user->getKey()),
                'can_edit_commission' => $authUser->canEditCommissionFor($user->getKey()),
                'can_edit_role'       => $authUser->canEditRoleFor($user->getKey()),
                'can_activated'       => $authUser->canActivatedOf($user->getKey()),
                'has_verified_email'  => $user->hasVerifiedEmail(),
                'is_auth_user'        => $authUser->getKey() === $userData['id'],
            ]
        );
    }

    /**
     * @param UpdateUserRequest $request
     * @param UpdateUserTask    $updateUserTask
     * @param int               $userId
     *
     * @return RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     * @throws \Illuminate\Database\QueryException
     * @throws \Dingo\Api\Exception\StoreResourceFailedException
     * @throws \App\Exceptions\Validation\ValidationFailedException
     * @throws \LogicException
     */
    public function updateUser(
        UpdateUserRequest $request,
        UpdateUserTask $updateUserTask,
        int $userId
    ): RedirectResponse {
        $user = User::findOrFail($userId);
        $closedAttrs = $user::getClosedAttributes($userId);
        $input = $request->getSanitizedInputs(['except' => $closedAttrs]);

        if (auth()->user()->isPrimaryRoleManager()) {
            $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS);
        }
        if (auth()->user()->isPrimaryRoleBusiness()) {
            $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_WORKER);
        }

        $updatedUser = $updateUserTask->run($input, $user);

        return back()
            ->withInput()
            ->withErrors($updatedUser->getValidationErrors())
        ;
    }

    /**
     * @return Renderable
     * @throws Exception
     */
    public function showNewUser(): Renderable
    {
        $queriedPrimaryRoleId = (int) request()->query('primary_role_id');
        $selectedRole = null;

        if (auth()->user()->isAdmin()) {
            $selectedRole = $queriedPrimaryRoleId ?: Role::getIdByRoleName(RoleConstants::ROLE_MANAGER);
        }

        if (auth()->user()->isPrimaryRoleManager()) {
            $selectedRole = Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS);
        }

        if (auth()->user()->isPrimaryRoleBusiness()) {
            $selectedRole = Role::getIdByRoleName(RoleConstants::ROLE_WORKER);
        }

        return view('admin-pages.user-new-profile',
            [
                'roles'         => Role::getRolesMap(),
                'selectedRole'  => $selectedRole,
                'can_edit_role' => auth()->user()->isAdmin(),
            ]
        );
    }

    /**
     * @param Request           $request
     * @param RegisterUserTask $registerUserTask
     *
     * @return RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     * @throws \Illuminate\Database\QueryException
     * @throws \Dingo\Api\Exception\StoreResourceFailedException
     * @throws \App\Exceptions\Validation\ValidationFailedException
     * @throws \LogicException
     */
    public function createNewUser(
        Request $request,
        RegisterUserTask $registerUserTask
    ): RedirectResponse {
        /** @var User $authUser */
        $authUser = auth()->user();
        $closedAttrs = $authUser::getClosedAttributes(0);
        $input = $request->getSanitizedInputs(['except' => $closedAttrs]);

        if (auth()->user()->isPrimaryRoleManager()) {
            $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS);
            $input['manager_id'] = auth()->user()->getKey();
        }

        if (auth()->user()->isPrimaryRoleBusiness()) {
            $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_WORKER);
            $input['business_id'] = auth()->user()->getKey();
        }

        $user = $registerUserTask->run($input, User::class);

        if ($user->getKey()) {
            return redirect(route(RouteConstants::ROUTE_NAME_WEB_USER_PROFILE, ['user_id' => $user->getKey()]));
        }

        return back()
            ->withInput()
            ->withErrors($user->getValidationErrors())
        ;
    }

    /**
     * @param DeleteUserMediaRequest $request
     * @param int                    $mediaId
     *
     * @return RedirectResponse
     */
    public function deleteUserMedia(
        DeleteUserMediaRequest $request,
        int $mediaId
    ): RedirectResponse {
        if ($request->validated()) {
            Media::findOrFail($mediaId)->delete();
        }

        return back()
            ->withInput()
            ->withErrors($request->validated())
        ;
    }
}

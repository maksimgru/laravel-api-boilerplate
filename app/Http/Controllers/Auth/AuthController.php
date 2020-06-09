<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\ResetPasswordErrorException;
use App\Exceptions\Auth\RestorePasswordErrorException;
use App\Exceptions\Auth\UserNotActivatedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Http\Requests\User\AuthRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\RestorePasswordRequest;
use App\Http\Tasks\Auth\LoginTask;
use App\Http\Tasks\Auth\ResetPasswordTask;
use App\Http\Tasks\Auth\RestorePasswordTask;
use App\Http\Tasks\Auth\Social\HandleProviderCallbackTask;
use App\Http\Tasks\Auth\Social\LoginTask as SocialLoginTask;
use Dingo\Api\Http\Response;
use Specialtactics\L5Api\Http\Controllers\Features\JWTAuthenticationTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    use JWTAuthenticationTrait;

    /**
     * Get a JWT via POST given credentials.
     *
     * @SWG\Post(
     *  path="/auth/jwt/login",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         @SWG\Property(property="email", type="string", example="admin@example.com", description=""),
     *         @SWG\Property(property="password", type="string", example="password", description=""),
     *     )
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJ", "tokenType": "bearer", "expiresIn": "1440"
     *             },
     *         }
     *     }
     *  ),
     *
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param AuthRequest $request
     * @param LoginTask   $loginTask
     *
     * @return Response
     * @throws UserNotActivatedException
     * @throws UnauthorizedHttpException
     */
    public function login(
        AuthRequest $request,
        LoginTask $loginTask
    ): Response {
        return $this->respondWithToken($loginTask->run($request));
    }

    /**
     * Get a JWT via GET given credentials (base64 encoded) in headers as BASIC AUTH.
     *
     * @SWG\Get(
     *  path="/auth/jwt/token",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Basic {base64(email:password)}",
     *     default="Basic {base64(email:password)}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJ", "tokenType": "bearer", "expiresIn": "1440"
     *             },
     *         }
     *     }
     *  ),
     *
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getToken(Request $request)
    {
        return $this->token($request);
    }

    /**
     * Refreshes a jwt (ie. extends it's TTL)
     *
     * Get a JWT via GET given credentials (base64 encoded) in headers as BASIC AUTH.
     *
     * @SWG\Get(
     *  path="/auth/jwt/refresh",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJ", "tokenType": "bearer", "expiresIn": "1440"
     *             },
     *         }
     *     }
     *  ),
     *
     *  @SWG\Response(response=400, description="Bad Request"),
     * )
     *
     * @return Response
     */
    public function refreshToken(): Response
    {
        return $this->refresh();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @SWG\Delete(
     *  path="/auth/jwt/logout",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=204, description="No-content"),
     * )
     *
     * @return Response
     */
    public function invalidateToken(): Response
    {
        return $this->logout();
    }

    /**
     * Reset User Password. Send Notifications with resetPasswordToken link.
     *
     * @SWG\Post(
     *  path="/auth/jwt/password/reset",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="required|email|exists:users,email"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=202, description="Reset Accepted",
     *     examples={
     *         "application/json": {
     *             "message": "We have e-mailed your password reset link!",
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param ResetPasswordRequest $resetPasswordRequest
     * @param ResetPasswordTask    $resetPasswordTask
     *
     * @return Response
     * @throws UnauthorizedHttpException
     * @throws ResetPasswordInvalidUserException
     * @throws ResetPasswordErrorException
     */
    public function resetPassword(
        ResetPasswordRequest $resetPasswordRequest,
        ResetPasswordTask $resetPasswordTask
    ): Response {
        $status = $resetPasswordTask->run($resetPasswordRequest['email']);
        $message = ['message' => $status ? trans('passwords.sent') : trans('passwords.reset-error')];

        return $this->response->accepted(null, $message);
    }

    /**
     * Restore (save new) User Password.
     *
     * @SWG\Post(
     *  path="/auth/jwt/password/restore",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="required|email|exists:users,email"),
     *        @SWG\Property(property="password", type="string", description="required|min:6"),
     *        @SWG\Property(property="passwordConfirm", type="string", description="required|min:6"),
     *        @SWG\Property(property="resetPasswordToken", type="string", description="required"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=200, description="Restored Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJ", "tokenType": "bearer", "expiresIn": "1440"
     *             },
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param RestorePasswordRequest $restorePasswordRequest
     * @param RestorePasswordTask    $restorePasswordTask
     *
     * @return Response
     * @throws UserNotActivatedException
     * @throws UnauthorizedHttpException
     * @throws RestorePasswordErrorException
     */
    public function restorePassword(
        RestorePasswordRequest $restorePasswordRequest,
        RestorePasswordTask $restorePasswordTask
    ): Response {
        return $this->respondWithToken($restorePasswordTask->run($restorePasswordRequest->input()));
    }

    /**
     * Social login user.
     *
     * @SWG\Get(
     *  path="/auth/jwt/social/{provider}/login",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="provider",
     *     description="Social provider google or facebook",
     *     in="query",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=400, description="Bad Request"),
     * )
     *
     * @param string $provider
     * @param SocialLoginTask $task
     *
     * @return RedirectResponse
     */
    public function socialLogin(
        string $provider,
        SocialLoginTask $task
    ): RedirectResponse {
        return $task->run($provider);
    }

    /**
     * Handle social provider callback.
     *
     * @SWG\Get(
     *  path="/auth/jwt/social/{provider}/callback",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="provider",
     *     description="Social provider google or facebook",
     *     in="query",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=400, description="Bad Request"),
     * )
     *
     * @param string $provider
     * @param HandleProviderCallbackTask $task
     *
     * @return Response
     */
    public function handleProviderCallback(
        string $provider,
        HandleProviderCallbackTask $task
    ): Response {
        return $this->respondWithToken($task->run($provider));
    }

    /**
     * Handle social provider by token.
     *
     * @SWG\Post(
     *  path="/auth/jwt/social/{provider}/token",
     *  tags={"Auth"},
     *
     *  @SWG\Parameter(
     *     name="provider",
     *     description="Social provider google or facebook",
     *     in="path",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         @SWG\Property(property="token", type="string", description="Token from socials side"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJ", "tokenType": "bearer", "expiresIn": "1440"
     *             },
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     * )
     *
     * @param string $provider
     * @param HandleProviderCallbackTask $task
     * @param Request $request
     * @return Response
     */
    public function handleProviderByToken(
        string $provider,
        HandleProviderCallbackTask $task,
        Request $request
    ): Response {
        return $this->respondWithToken($task->run($provider, $request->get('token')));
    }
}

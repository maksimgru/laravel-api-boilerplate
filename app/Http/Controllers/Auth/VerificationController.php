<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RestfulService;
use Dingo\Api\Http\Response;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Http\Requests\Request;
use Illuminate\Auth\Events\Verified;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class VerificationController
 *
 * This controller is responsible for handling email verification for any
 * user that recently registered with the application. Emails may also
 * be re-sent if the user did not receive the original email message.
 *
 * @package App\Http\Controllers
 */
class VerificationController extends Controller
{
    use VerifiesEmails;

    /**
     * Create a new controller instance.
     *
     * @param RestfulService $restfulService
     */
    public function __construct(RestfulService $restfulService)
    {
        parent::__construct($restfulService);
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Notice user after verification.
     *
     * @SWG\Get(
     *  path="/email/verify/notice",
     *  tags={"Users"},
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
     *  @SWG\Response(response=202, description="Accepted",
     *     examples={
     *         "application/json": {
     *             "message": "auth.email.verified"
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not Fount"),
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @throws HttpException
     */
    public function show(Request $request)
    {
        return $this->response->accepted(
            null,
            [
                'message' => $this->user()->hasVerifiedEmail()
                ? trans('auth.email.verified')
                : trans('auth.email.not_verified_notice')
            ]
        );
    }

    /**
     * Mark the user's email address as verified.
     *
     * @SWG\Get(
     *  path="/email/verify/user/{user_id}/{verification_token}",
     *  tags={"Users"},
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
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="Auth User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="verification_token",
     *     description="Email Verification Token",
     *     default="p3levh7pcch5pr47dvfu",
     *     in="path",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=202, description="Accepted",
     *     examples={
     *         "application/json": {
     *             "message": "auth.email.verified"
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not Fount"),
     * )
     *
     * @param Request $request
     * @param int     $userId
     * @param string  $verificationToken
     *
     * @return Response
     * @throws HttpException
     */
    public function verify(
        Request $request,
        int $userId,
        string $verificationToken
    ) {
        if ($userId === $this->user()->getKey()
            && $verificationToken === $this->user()->email_verification_token
            && $this->user()->markEmailAsVerified()
        ) {
            event(new Verified($this->user()));
            return $this->response->accepted(null, ['message' => trans('auth.email.verified')]);
        }

        return $this->response->errorBadRequest(trans('auth.email.not_verified'));
    }

    /**
     * Resend the email verification notification.
     *
     * @SWG\Get(
     *  path="/email/verify/resend",
     *  tags={"Users"},
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
     *  @SWG\Response(response=202, description="Accepted",
     *     examples={
     *         "application/json": {
     *             "message": "auth.email.verified"
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=401, description="Unauthorized"),
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @throws HttpException
     */
    public function resend(Request $request)
    {
        if ($this->user()->hasVerifiedEmail()) {
            return $this->response->accepted(null, ['message' => trans('auth.email.already_verified')]);
        }

        $this->user()->sendEmailVerificationNotification();

        return $this->response->accepted(null, ['message' => trans('auth.email.resend_verify')]);
    }
}

<?php

namespace App\Http\Controllers\Web\Auth;

use App\Constants\RouteConstants;
use App\Http\Controllers\Web\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('throttle:6,1')->only('show', 'verify', 'resend');
    }

    /**
     * Where to redirect users after verification.
     *
     * @return string
     */
    public function redirectTo(): string
    {
        return route(RouteConstants::ROUTE_NAME_WEB_MY_PROFILE);
    }

    /**
     * Show the email verification notice.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function show(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }

        if (!session('resent')) {
            $request->user()->sendEmailVerificationNotification();
        }

        return view('auth.verify')->with('resent', true);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param Request $request
     *
     * @return Redirector|RedirectResponse
     * @throws AuthorizationException
     */
    public function verify(Request $request)
    {
        if (! hash_equals((string) $request->route('id'), (string) $request->user()->getKey())) {
            throw new AuthorizationException;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($request->user()->getEmailVerificationToken()))) {
            throw new AuthorizationException();
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect($this->redirectPath())
            ->with([
                'verified' => true,
                'status' => trans('auth.email.verified')
           ])
        ;
    }
}

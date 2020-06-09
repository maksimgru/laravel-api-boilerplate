<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'email' => [
        'required'            => 'The "email" field is required.',
        'invalid'             => 'The "email" field must be a valid Email address',
        'exists'              => 'We can\'t find a user with that email address.',
        'verified'            => 'Email Verified!',
        'not_verified_notice' => 'Email not verified!',
        'not_verified'        => 'Email not verified! Invalid Verification Token or UserID!',
        'already_verified'    => 'User already have verified email!',
        'resend_verify'       => 'The email verification notification has been resubmitted!',
    ],
    'user' => [
        'activated'        => 'User is activated!',
        'not_activated'    => 'User is not activated!',
        'unauthorized'     => 'Unauthorized - not logged in!',
    ],
    'password'             => ['required' => 'The "password" field is required.'],
];

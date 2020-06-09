<?php

namespace App\Http\Tasks\Auth\Social;

use App\Http\Tasks\Task;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class LoginTask
 *
 * @package App\Http\Tasks\Auth\Social
 */
class LoginTask extends Task
{
    /**
     * @param string|null $provider
     *
     * @return RedirectResponse
     */
    public function run(
        string $provider
    ): RedirectResponse {
        return Socialite::driver($provider)->stateless()->redirect();
    }
}

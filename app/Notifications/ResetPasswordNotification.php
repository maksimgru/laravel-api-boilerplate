<?php

namespace App\Notifications;

use App\Constants\RouteConstants;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var User $user */
    protected $user;

    /** @var string $resetPasswordToken */
    protected $resetPasswordToken;

    /**
     * @param User   $user
     * @param string $resetPasswordToken
     */
    public function __construct(User $user, string $resetPasswordToken)
    {
        $this->user = $user;
        $this->resetPasswordToken = $resetPasswordToken;
    }

    /**
     * @param $notifiable
     *
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param $notifiable
     *
     * @return mixed
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(sprintf(trans('passwords.reset-subject'), config('app.name')))
            ->markdown('email-templates.user.reset-password', $this->toArray($notifiable))
        ;
    }

    /**
     * @param $notifiable
     *
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'user' => $this->user,
            'resetPasswordToken' => $this->resetPasswordToken,
            'resetPasswordUrl' => route(
                RouteConstants::ROUTE_NAME_WEB_PASSWORD_RESET,
                    ['token' => $this->resetPasswordToken, 'email' => $this->user->getEmailForPasswordReset()]
            ),
        ];
    }
}

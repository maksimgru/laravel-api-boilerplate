<?php

namespace App\Notifications;

use App\Constants\RoleConstants;
use App\Constants\RouteConstants;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var User $user
     */
    private $user;

    /**
     * @var array|null $requestInput
     */
    private $requestInput;

    /**
     * Create a new notification instance.
     *
     * @param User       $user
     * @param array|null $requestInput
     */
    public function __construct(User $user, ?array $requestInput)
    {
        $this->user = $user;
        $this->requestInput = $requestInput;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('Registration on :name', ['name' => config('app.name')]))
            ->markdown('email-templates.user.registered', [
                'user' => $this->user,
                'requestInput' => $this->requestInput,
                'loginUrl' => $this->loginUrl($notifiable)
            ])
        ;
    }

    /**
     * Get the login URL for the given notifiable.
     *
     * @param mixed $notifiable
     *
     * @return null|string
     */
    protected function loginUrl($notifiable): ?string
    {
        $url = null;
        if (\in_array($notifiable->primaryRole->name, RoleConstants::ALL_ADMIN_AREA_ROLES, true)) {
            $url = route(RouteConstants::ROUTE_NAME_WEB_LOGIN);
        }

        return $url;
    }
}

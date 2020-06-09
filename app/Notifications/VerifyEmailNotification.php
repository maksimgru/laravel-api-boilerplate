<?php

namespace App\Notifications;

use App\Constants\RouteConstants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

class VerifyEmailNotification extends VerifyEmailBase implements ShouldQueue
{
    use Queueable;

    /** @var string $verificationToken */
    protected $verificationToken;

    /**
     * @param string $verificationToken
     */
    public function __construct(string $verificationToken)
    {
        $this->verificationToken = $verificationToken;
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
            ->subject(trans('Verify Email Address On :name', ['name' => config('app.name')]))
            ->markdown('email-templates.user.email-verify', $this->toArray($notifiable))
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
            'verificationToken' => $this->verificationToken,
            'verificationUrl' => $this->verificationUrl($notifiable),
        ];
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     *
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return route(
           'verification.verify',
           [
               'id' => $notifiable->getKey(),
               'hash' => sha1($this->verificationToken),
           ]
       );
    }

    /**
     * Get the Front verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     *
     * @return string
     */
    protected function _verificationUrl($notifiable): string
    {
        $prefix = config('frontend.email_verify_url');
        $callbackApiURL = url(
            sprintf(
                '%1$s/%2$d/%3$s',
                '/email/verify/user',
                $notifiable->getKey(),
                $this->verificationToken
            )
        );

        return $prefix . urlencode($callbackApiURL);
    }
}

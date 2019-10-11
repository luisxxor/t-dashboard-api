<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    use Queueable;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via( $notifiable )
    {
        return [ 'mail' ];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail( $notifiable )
    {
        $verificationUrl = $this->verificationUrl( $notifiable );

        if ( static::$toMailCallback ) {
            return call_user_func( static::$toMailCallback, $notifiable, $verificationUrl );
        }

        return ( new MailMessage )
            ->subject( Lang::get( 'Tasing! | Verificación de correo electrónico' ) )
            ->line( Lang::get( 'Haga clic en el botón de abajo para verificar su dirección de correo electrónico.' ) )
            ->action(
                Lang::get( 'Verificar' ),
                $verificationUrl
            )
            ->line( Lang::get( 'Si no realizó esta solicitud, puede ignorar este correo electrónico.' ) );
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl( $notifiable )
    {
        $routeParameters = [
            'id' => $notifiable->getKey(),
            'hash' => sha1( $notifiable->getEmailForVerification() ),
        ];

        $verificationUrlAPI = URL::temporarySignedRoute(
            'api.verification.verify',
            Carbon::now()->addMinutes( Config::get( 'auth.verification.expire', 60 ) ),
            $routeParameters
        );

        $parameters = str_replace( route( 'api.verification.verify', $routeParameters ), '', $verificationUrlAPI );

        return Config::get( 'app.front_url' ) . 'email/verify/' . $routeParameters[ 'id' ] . '/' . $routeParameters[ 'hash' ] . '/' . $parameters;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing( $callback )
    {
        static::$toMailCallback = $callback;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray( $notifiable )
    {
        return [
            //
        ];
    }
}

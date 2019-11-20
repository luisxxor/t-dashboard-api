<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct( $token )
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
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
        $resetPasswordUrl = $this->resetPasswordUrl( $notifiable );

        if ( static::$toMailCallback ) {
            return call_user_func( static::$toMailCallback, $notifiable, $this->token );
        }

        return ( new MailMessage )
            ->subject( Lang::get( 'Tasing! | Solicitud de restablecimiento de contraseña' ) )
            ->line( Lang::get( 'Está recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta.' ) )
            ->line( Lang::get( 'Si no realizó esta solicitud, puede ignorar este correo electrónico.' ) )
            ->line( Lang::get( 'De lo contrario, haga clic en el enlace de abajo para completar el proceso.' ) )
            ->action( Lang::get( 'Restablecer contraseña' ), $resetPasswordUrl )
            ->line( Lang::get( 'Este enlace de restablecimiento de contraseña caducará en :count minutos.',
                [ 'count' => Config::get( 'auth.passwords.' . Config::get( 'auth.defaults.passwords' ) . '.expire' ) ]
            ) );
    }

    /**
     * Get the reset password URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetPasswordUrl( $notifiable )
    {
        $queryParameters = [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ];

        return Config::get( 'app.front_url' ) . '/password/reset/?token=' . $queryParameters[ 'token' ] . '&email=' . $queryParameters[ 'email' ];
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

<?php

namespace App\Providers;

use Laravel\Socialite\Two\GoogleProvider as GoogleProviderSocialite;
use Illuminate\Http\RedirectResponse;

class GoogleProvider extends GoogleProviderSocialite
{
    /**
     * @var string
     */
    protected $state = null;

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $state = null;

        if ( $this->usesState() ) {
            $state = $this->getState();
        }

        return new RedirectResponse( $this->getAuthUrl( $state ) );
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function getState()
    {
        return $this->state;
    }

    /**
     * Sets an arbitrary string designed to send a state to google and spected it.
     *
     * @param string $value
     *
     * @return string
     */
    public function setFakeState( string $value )
    {
        $this->state = $value;

        return $this;
    }
}

<?php

namespace App\Providers;

use Laravel\Socialite\Two\GoogleProvider as GoogleProviderSocialite;
use Illuminate\Http\RedirectResponse;

class GoogleProvider extends GoogleProviderSocialite
{
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
        return 'stevenYO';
    }
}

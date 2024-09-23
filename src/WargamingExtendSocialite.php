<?php

declare(strict_types=1);

namespace Noilty\SocialiteProviders\Wargaming;

use SocialiteProviders\Manager\SocialiteWasCalled;

class WargamingExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('wargaming', Provider::class);
    }
}

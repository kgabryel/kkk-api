<?php

namespace App\Service\Auth;

use League\OAuth2\Client\Provider\Facebook;

class FBFactory
{
    public static function getInstance(string $redirectUrl): Facebook
    {
        return new Facebook([
            'clientId' => $_ENV['FB_ID'],
            'clientSecret' => $_ENV['FB_SECRET'],
            'graphApiVersion' => 'v2.10',
            'redirectUri' => $redirectUrl
        ]);
    }
}

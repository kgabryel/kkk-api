<?php

namespace App\Tests\DataProvider;

class UserDataProvider
{
    public static function userTypesValues(): array
    {
        return [
            'typ facebook' => [
                'isStandardUser' => false,
                'userType' => 'facebook',
            ],
            'typ standard' => [
                'isStandardUser' => true,
                'userType' => 'standard',
            ],
        ];
    }
}

<?php

namespace App\Tests\DataProvider;

class LogDataProvider
{
    public static function logDataValues(): array
    {
        return [
            'brak contextu (tylko message i extra)' => [
                'logContext' => '',
                'logExtra' => 'stack trace',
                'logMessage' => 'Error occurred',
            ],
            'brak extra (tylko message i context)' => [
                'logContext' => 'system',
                'logExtra' => '',
                'logMessage' => 'Error occurred',
            ],
            'brak wiadomości (tylko context i extra)' => [
                'logContext' => 'system',
                'logExtra' => 'stack trace',
                'logMessage' => '',
            ],
            'pełne dane logu' => [
                'logContext' => 'system',
                'logExtra' => 'stack trace',
                'logMessage' => 'Error occurred',
            ],
            'wszystkie wartości puste' => [
                'logContext' => '',
                'logExtra' => '',
                'logMessage' => '',
            ],
        ];
    }
}

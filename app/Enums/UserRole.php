<?php

namespace App\Enums;

enum UserRole: string
{
    case EMPLOYER = 'employer';
    case APPLICANT = 'applicant';

    public static function values(): array
    {
        // to get all the enums value in an array format
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Components;


class TokenManager
{
    /**
     * @return string
     */
    public static function generateApiToken(): string
    {
        return base64_encode(str_random(32));
    }
}

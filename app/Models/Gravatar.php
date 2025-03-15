<?php

namespace App\Models;

class Gravatar
{
    public static function url($email)
    {
        return 'https://www.gravatar.com/avatar/' . md5($email) . '?s=50&d=retro&f=y';
    }
}

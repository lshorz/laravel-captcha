<?php

namespace Lshorz\LaravelCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class Captcha extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'captcha';
    }
}

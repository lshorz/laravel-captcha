<?php

namespace Lshorz\LaravelCaptcha;

use Illuminate\Support\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider {

    /**
     * Boot the service provider.
     *
     */
    public function boot()
    {
        // Publish configuration files
        $this->publishes([
            __DIR__.'/config/captcha.php' => config_path('captcha.php')
        ], 'config');

        // HTTP routing
        $this->app['router']->get('captcha/api/{config?}', '\Lshorz\LaravelCaptcha\CaptchaController@getCaptchaApi')->middleware('api')->name('laravel.captcha.api');
        $this->app['router']->get('captcha/{config?}', '\Lshorz\LaravelCaptcha\CaptchaController@getCaptcha')->middleware('web')->name('laravel.captcha');
        $this->registerValidator();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind captcha
        $this->app->bind('captcha', function($app)
        {
            return new Captcha(
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Illuminate\Config\Repository'],
                $app['Illuminate\Session\Store'],
                $app['Intervention\Image\ImageManager']
            );
        });
    }

    protected function registerValidator()
    {
        $this->app['validator']->extend('captcha', function($attribute, $value, $parameters)
        {
            return captcha_check($value, isset($parameters[0]) ? $parameters[0] : true, isset($parameters[1]) ? $parameters[1] : 'default');
        });

        $this->app['validator']->extend('captcha_api', function($attribute, $value, $parameters)
        {
            return captcha_api_check($value, $parameters[0]);
        });
    }

}

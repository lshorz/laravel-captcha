<?php

if ( ! function_exists('captcha')) {

    /**
     * @param string $config
     * @return mixed
     */
    function captcha($config = 'default')
    {
        return app('captcha')->create($config);
    }
}

if ( ! function_exists('captcha_src')) {
    /**
     * @param string $config
     * @return string
     */
    function captcha_src($config = 'default')
    {
        return app('captcha')->src($config);
    }
}

if ( ! function_exists('captcha_img')) {

    /**
     * @param string $config
     * @return mixed
     */
    function captcha_img($config = 'default')
    {
        return app('captcha')->img($config);
    }
}

if ( ! function_exists('captcha_check')) {
	/**
	 * @param $value
     * @param $once
     * @param $config
	 * @return bool
	 */
	function captcha_check($value, $once = true, $config = 'default')
	{
		return app('captcha')->check($value, $once, $config);
	}
}

if ( ! function_exists('captcha_api_check')) {
	/**
	 * @param $value
     * @param $key
	 * @return bool
	 */
	function captcha_api_check($value, $key)
	{
		return app('captcha')->check_api($value, $key);
	}
}

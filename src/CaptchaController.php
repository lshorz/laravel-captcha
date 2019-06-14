<?php

namespace Lshorz\LaravelCaptcha;

use Illuminate\Routing\Controller;

/**
 * Class CaptchaController
 * @package Mews\Captcha
 */
class CaptchaController extends Controller
{

	/**
	 * get CAPTCHA
	 *
	 * @param \Lshorz\LaravelCaptcha\Captcha $captcha
	 * @param string $config
     * @throws
     *
	 * @return \Intervention\Image\ImageManager->response
	 */
	public function getCaptcha(Captcha $captcha, $config = 'default')
	{
		if (ob_get_contents())
		{
			ob_clean();
		}
		return $captcha->create($config);
	}

	/**
	 * get CAPTCHA api
	 *
	 * @param \App\Lib\Captcha\Captcha $captcha
	 * @param string $config
     * @throws
     *
	 * @return \Intervention\Image\ImageManager->response
	 */
	public function getCaptchaApi(Captcha $captcha, $config = 'default')
	{
		return $captcha->create($config, true);
	}

}

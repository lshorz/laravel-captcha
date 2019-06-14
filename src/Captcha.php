<?php

namespace Lshorz\LaravelCaptcha;

use Exception;

class Captcha extends AbstractCaptcha
{
    /**
     * 生成的验证码字符
     * @var array
     */
    private $text = [];

    /**
     * 创建验证码
     *
     * @return array
     * @throws Exception
     */
    protected function generate()
    {
        $bag = '';

        if ($this->math) {
            $this->zh = false;
            $value = [random_int(1, 30), random_int(1, 30)];
            $expression = random_int(1, 2);
            if ($expression == 1) {
                $bag = "{$value[0]} + {$value[1]} = ";
                $key = $value[0] + $value[1];
            } else {
                if (($value[0] - $value[1]) > 0) {
                    $bag = "{$value[0]} - {$value[1]} = ";
                    $key = $value[0] - $value[1];
                } else {
                    $bag = "{$value[1]} - {$value[0]} = ";
                    $key = $value[1] - $value[0];
                }
            }
            $key .= '';
        } else {
            if ($this->zh) {
                $this->characters = isset($this->characters) ? $this->characters : config('captcha.charactersZh');
                $characters = preg_split('/(?<!^)(?!$)/u', $this->characters);
            } else {
                $this->characters = isset($this->characters) ? $this->characters : config('captcha.characters');
                $characters = str_split($this->characters);
            }

            for ($i = 0; $i < $this->length; $i++) {
                $bag .= $characters[mt_rand(0, count($characters) - 1)];
            }
            $key = mb_strtolower($bag, 'UTF-8');
        }

        $this->text = $this->zh ? preg_split('/(?<!^)(?!$)/u', $bag) : str_split($bag);

        $hash = password_hash($key, PASSWORD_BCRYPT, ['cost' => 10]);

        $this->session->put('captcha', ['key' => $hash]);
        return [
            'value' => $bag,
            'key' => $hash,
        ];
    }

    /**
     * 创建验证码
     *
     * @param null|string $config
     * @param bool $api
     *
     * @throws Exception
     * @return mixed
     */
    public function create($config = 'default', $api = false)
    {
        $this->configure($config);
        $generator = $this->generate();

        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );

        if ($this->imageBg) {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        } else {
            $this->image = $this->canvas;
        }

        //绘干扰曲线
        if ($this->curve) {
            $this->drawCurve();
        }
        //绘验证码字体
        $this->text();
        //绘干扰直线
        if ($this->lines > 0) {
            $this->drawLines($this->lines, $this->lineThickness);
        }
        //扭曲图像
        if ($this->distort) {
            $this->drawDistort($this->distortType, $this->distortScale);
        }

        if ($this->noise > 0) {//绘制杂点
            $this->drawNoise($this->noise);
        }
        if ($this->sharpen != 0) { //锐化
            $this->image->sharpen($this->sharpen);
        }
        if ($this->blur > 0) { //模糊
            $this->image->blur($this->blur);
        }
        if ($this->contrast != 0) { //对比度
            $this->image->contrast($this->contrast);
        }

        if ($api) {
            $image = $this->image->encode('data-url')->encoded;
            $this->image->destroy();
            return [
                'key' => $generator['key'],
                'img' => $image
            ];
        } else {
            $response = $this->image->response('png', $this->quality);
            $this->image->destroy();
            return $response;
        }
    }

    /**
     * 验证验证码是否正确
     *
     * @param string $value 用户验证码
     * @param boolean $once 是否只验证一次(如查是ajax则可设置为false)
     *
     * @return bool
     */
    public function check($value, $once = true)
    {
        if (!$this->session->has('captcha')) {
            return false;
        }

        $key = $this->session->get('captcha.key');
        $value = mb_strtolower($value, 'UTF-8');
        $res = password_verify($value, $key);

        if ($res && $once) {
            $this->session->remove('captcha');
        }
        return $res;
    }

    /**
     * Captcha check
     *
     * @param $value
     * @param string $key
     *
     * @return bool
     */
    public function check_api($value, $key)
    {
        return password_verify($value, $key);
    }

    /**
     * Generate captcha image source
     *
     * @param null $config
     *
     * @return string
     */
    public function src($config = null)
    {
        return url('captcha' . ($config ? '/' . $config : '/default')) . '?' . \Str::random(8);
    }

    /**
     * Generate captcha image html tag
     *
     * @param null $config
     * @param array $attrs HTML attributes supplied to the image tag where key is the attribute
     *                     and the value is the attribute value
     *
     * @return string
     */
    public function img($config = null, $attrs = [])
    {
        $attrs_str = '';
        foreach ($attrs as $attr => $value) {
            if ($attr == 'src') {
                //Neglect src attribute
                continue;
            }
            $attrs_str .= $attr . '="' . $value . '" ';
        }
        return '<img src="' . $this->src($config) . '" ' . trim($attrs_str) . '>';
    }

    /**
     * 验证码字
     */
    private function text()
    {
        $y = $this->math ? $this->image->height() / count($this->text) : $this->image->height() / $this->length;
        for ($i = 0; $i < count($this->text); $i++) {
            if ($this->math) {
                $x = $this->letterSpacing + ($i * ($this->image->width() - $this->letterSpacing) / count($this->text));
            } else {
                $x = $this->letterSpacing + ($i * ($this->image->width() - $this->letterSpacing) / $this->length);
            }
            $this->image->text($this->text[$i], $x, $y, function ($font) use ($i) {
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                if ($i == 0 && !$this->math) {
                    $font->angle(mt_rand(-10, 40));
                } else {
                    $font->angle($this->angle());
                }
            });
        }
    }
}
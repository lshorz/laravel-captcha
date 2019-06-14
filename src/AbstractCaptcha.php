<?php

namespace Lshorz\LaravelCaptcha;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\ImageManager;
use Illuminate\Session\Store as Session;

abstract class AbstractCaptcha
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var string
     */
    protected $characters;

    /**
     * @var ImageManager->canvas
     */
    protected $canvas;

    /**
     * @var ImageManager->image
     */
    protected $image;

    /**
     * 对比度
     *
     * @var int
     */
    protected $contrast = 0;

    /**
     * 图像质量
     *
     * @var int
     */
    protected $quality = 100;

    /**
     * 模糊
     *
     * @var int
     */
    protected $blur = 0;

    /**
     * 锐化
     *
     * @var int
     */
    protected $sharpen = 0;



    /**
     * 开启图像扭曲(仅distortType=1有效)
     * @var float
     */
    protected $distort = true;

    /**
     * 图像扭曲算法
     * @var int
     */
    protected $distortType = 1;

    /**
     * 图像扭曲度
     * @var float
     */
    protected $distortScale = 2.0;

    /**
     * 字体角度
     *
     * @var int
     */
    protected $angle = 0;

    /**
     * 是否开启中文验证码
     *
     * @var bool
     */
    protected $zh = false;

    /**
     * 是否算术验证码
     *
     * @var bool
     */
    protected $math = false;

    /**
     * 使用背景图片
     *
     * @var bool
     */
    protected $imageBg = false;

    /**
     * 混淆曲线
     *
     * @var bool
     */
    protected $curve = false;

    /**
     * 杂点数量
     *
     * @var int
     */
    protected $noise = 50;

    /**
     * 验证码干扰线
     *
     * @var int
     */
    protected $lines = 0;

    /**
     * 验证码干扰线粗细
     *
     * @var int
     */
    protected $lineThickness = 1;

    /**
     * 验证码图片高度
     *
     * @var int
     */
    protected $height = 40;

    /**
     * 验证码图片宽度
     *
     * @var integer
     */
    protected $width = 120;

    /**
     * 验证码位数
     *
     * @var integer
     */
    protected $length = 5;

    /**
     * 字体颜色，如果为空则自动生成
     * 也可以自己定义如下：['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548']
     *
     * @var array
     */
    protected $fontColors = [];

    /**
     * 背景颜色
     *
     * @var string
     */
    protected $bgColor = '#edf2f4';

    /**
     * @var int
     */
    protected $letterSpacing = 6;

    /**
     * Is the interpolation enabled ?
     *
     * @var bool
     */
    private $interpolation = false;

    public function __construct(Filesystem $filesystem, Repository $config, Session $session, ImageManager $imageManager)
    {
        $this->files = $filesystem;
        $this->config = $config;
        $this->session = $session;
        $this->imageManager = $imageManager;
    }

    /**
     * @param string $config
     *
     * @return void
     */
    protected function configure($config)
    {
        if ($this->config->has('captcha.' . $config)) {
            foreach ($this->config->get('captcha.' . $config) as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    /**
     * 获取背景图片
     */
    protected function background()
    {
        $backgrounds = $this->files->files(__DIR__ . '/assets/backgrounds');
        return $backgrounds[mt_rand(0, count($backgrounds) - 1)];
    }

    /**
     * 获取验证码字体
     *
     * @return string
     */
    protected function font()
    {
        if ($this->zh) {
            $fontPath = __DIR__ . '/assets/fonts/zh.ttf';
        } else {
            $fonts = $this->files->files(__DIR__ . '/assets/fonts');
            $fonts = array_map(function ($file) {
                return $file->getPathName();
            }, $fonts);
            $fontPath = $fonts[mt_rand(0, count($fonts) - 1)];
        }
        return $fontPath;
    }

    /**
     * 字体大小
     *
     * @return integer
     */
    protected function fontSize()
    {
        return $this->zh ? mt_rand($this->image->height() - 25, $this->image->height() - 10) : mt_rand($this->image->height() - 20, $this->image->height());
    }

    /**
     * 字体颜色
     *
     * @param int $min
     * @param int $max
     * @return array
     */
    protected function fontColor($min = 1, $max = 150)
    {
        if (!empty($this->fontColors)) {
            $color = $this->fontColors[array_rand($this->fontColors)];
        } else {
            $color = [mt_rand($min, $max), mt_rand($min, $max), mt_rand($min, $max)];
        }

        return $color;
    }

    /**
     * 角度
     *
     * @return int
     */
    protected function angle()
    {
        if ($this->math) {
            return 0;
        } elseif ($this->angle > 0) {
            return $this->angle;
        } else {
            return mt_rand(-30, 40);
        }
    }

    /**
     * 生成随机颜色
     *
     * @param int $min
     * @param int $max
     * @param float $alpha
     *
     * @return array
     */
    protected function color($min = 1, $max = 150, $alpha = 1.0)
    {
        $color = [mt_rand($min, $max), mt_rand($min, $max), mt_rand($min, $max), $alpha];
        return $color;
    }

    /**
     * 绘制干扰线
     * @param int num 数量
     * @param int $thickness 线条粗细
     */
    protected function drawLines($num, $thickness = 1)
    {
        $gd = $this->gd();
        for ($i = 0; $i < $num; $i++) {
            if (mt_rand(0, 1)) { // Horizontal
                $Xa = mt_rand(0, $this->width / 2);
                $Ya = mt_rand(0, $this->height);
                $Xb = mt_rand($this->width / 2, $this->width);
                $Yb = mt_rand(0, $this->height);
            } else { // Vertical
                $Xa = mt_rand(0, $this->width);
                $Ya = mt_rand(0, $this->height / 2);
                $Xb = mt_rand(0, $this->width);
                $Yb = mt_rand($this->height / 2, $this->height);
            }
            $color = $this->color();
            $tcol = imagecolorallocate($gd, $color[0], $color[1], $color[2]);
            imagesetthickness($gd, $thickness);
            imageline($gd, $Xa, $Ya, $Xb, $Yb, $tcol);
        }
    }

    /**
     * 画杂点往图片上写不同颜色的字母或数字
     * @param int $num 杂点数量
     */
    protected function drawNoise($num)
    {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHIJKLMN';
        for ($i = 0; $i < $num; $i++) {
            $this->image->text($codeSet[mt_rand(0, 29)], mt_rand(-10, $this->width), mt_rand(-10, $this->height), function ($font) {
                $font->file(mt_rand(1, 5));
                $font->color($this->color(150, 255, 0.7));
            });
        }
    }

    /**
     * 画一条曲线
     */
    protected function drawCurve()
    {
        $color = [mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150), 0.9];
        $px = $py = 0;
        // 曲线前部分
        $A = mt_rand(1, $this->height / 3); // 振幅
        $b = mt_rand(-$this->height / 4, $this->height / 4); // Y轴方向偏移量
        $f = mt_rand(-$this->height / 4, $this->height / 4); // X轴方向偏移量
        $T = mt_rand($this->height, $this->width * 4); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($this->width / 2, $this->width * 0.8); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->height / 2; // y = Asin(ωx+φ) + b
                $i = (int)($this->fontSize() / 5);
                while ($i > 0) {
                    $this->image->pixel($color, intval($px + $i), intval($py + $i));
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A = mt_rand(1, $this->height / 2); // 振幅
        $f = mt_rand(-$this->height / 4, $this->height / 4); // X轴方向偏移量
        $T = mt_rand($this->height, $this->width * 2); // 周期
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $this->height / 2;
        $px1 = $px2;
        $px2 = $this->width;

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->height / 2; // y = Asin(ωx+φ) + b
                $i = (int)($this->fontSize() / 5);
                while ($i > 0) {
                    $this->image->pixel($color, intval($px + $i), intval($py + $i));
                    $i--;
                }
            }
        }
    }

    /**
     * 扭曲图像
     * @param int $type
     * @param float $scale
     */
    protected function drawDistort($type, $scale)
    {
        if ($type == 1) {
            $this->image->insert($this->distort($scale));
        } else {
            $this->image->insert($this->wave());
        }

    }

    /**
     * 图像扭曲算法1
     * @param float $scale 扭曲度
     * @return resource
     */
    private function distort($scale = 1.2)
    {
        $gd = $this->gd();
        $bg = is_array($this->bgColor) ? $this->bgColor : $this->hexToRgb($this->bgColor);
        $bg = imagecolorallocate($gd, $bg[0], $bg[1], $bg[2]);

        $contents = imagecreatetruecolor($this->width, $this->height);
        $X = mt_rand(0, $this->width);
        $Y = mt_rand(0, $this->height);
        $phase = mt_rand(0, 10);
        $scale = $scale + mt_rand(0, 10000) / 30000;
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $Vx = $x - $X;
                $Vy = $y - $Y;
                $Vn = sqrt($Vx * $Vx + $Vy * $Vy);
                if ($Vn != 0) {
                    $Vn2 = $Vn + 4 * sin($Vn / 30);
                    $nX = $X + ($Vx * $Vn2 / $Vn);
                    $nY = $Y + ($Vy * $Vn2 / $Vn);
                } else {
                    $nX = $X;
                    $nY = $Y;
                }
                $nY = $nY + $scale * sin($phase + $nX * 0.2);
                if ($this->interpolation) {
                    $p = $this->interpolate(
                        $nX - floor($nX),
                        $nY - floor($nY),
                        $this->getCol($gd, floor($nX), floor($nY), $bg),
                        $this->getCol($gd, ceil($nX), ceil($nY), $bg),
                        $this->getCol($gd, floor($nX), floor($nY), $bg),
                        $this->getCol($gd, ceil($nX), ceil($nY), $bg)
                    );
                    $p = mt_rand(0, 1) ? $p : $bg;
                } else {
                    $p = $this->getCol($gd, round($nX), round($nY), $bg);
                }
                if ($p == 0) {
                    $p = $bg;
                }
                imagesetpixel($contents, $x, $y, $p);
            }
        }
        return $contents;
    }

    /**
     * 图像扭曲算法2
     * @return resource
     */
    private function wave()
    {
        $gd = $this->gd();
        $bg = is_array($this->bgColor) ? $this->bgColor : $this->hexToRgb($this->bgColor);
        $contents = imagecreatetruecolor($this->width, $this->height);

        $sxR1 = mt_rand(7, 10) / 120;
        $syR1 = mt_rand(7, 10) / 120;
        $sxR2 = mt_rand(7, 10) / 120;
        $syR2 = mt_rand(7, 10) / 120;

        $sxF1 = mt_rand(0, 314) / 100;
        $sxF2 = mt_rand(0, 314) / 100;
        $syF1 = mt_rand(0, 314) / 100;
        $syF2 = mt_rand(0, 314) / 100;

        $sxA = mt_rand(4, 6);
        $syA = mt_rand(4, 6);

        $newR = 255;
        $newG = 255;
        $newB = 255;

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $sx = $x + (sin($x * $sxR1 + $sxF1) + sin($y * $sxR2 + $sxF2)) * $sxA;
                $sy = $y + (sin($x * $syR1 + $syF1) + sin($y * $syR2 + $syF2)) * $syA;

                if ($sx < 0 || $sy < 0 || $sx >= $this->width - 1 || $sy >= $this->height - 1) {
                    $r = $rX = $rY = $rXY = $bg[0];
                    $g = $gX = $gY = $gXY = $bg[1];
                    $b = $bX = $bY = $bXY = $bg[2];
                } else {
                    $rgb = imagecolorat($gd, $sx, $sy);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $rgb = imagecolorat($gd, $sx + 1, $sy);
                    $rX = ($rgb >> 16) & 0xFF;
                    $gX = ($rgb >> 8) & 0xFF;
                    $bX = $rgb & 0xFF;

                    $rgb = imagecolorat($gd, $sx, $sy + 1);
                    $rY = ($rgb >> 16) & 0xFF;
                    $gY = ($rgb >> 8) & 0xFF;
                    $bY = $rgb & 0xFF;

                    $rgb = imagecolorat($gd, $sx + 1, $sy + 1);
                    $rXY = ($rgb >> 16) & 0xFF;
                    $gXY = ($rgb >> 8) & 0xFF;
                    $bXY = $rgb & 0xFF;
                }

                if ($r == $rX && $r == $rY && $r == $rXY && $g == $gX && $g == $gY && $g == $gXY && $b == $bX && $b == $bY && $b == $bXY) {
                    if ($r == $bg[0] && $g == $bg[1] && $b == $bg[2]) {
                        $newR = $bg[0];
                        $newG = $bg[1];
                        $newB = $bg[2];
                    }
                } else {
                    $frsx = $sx - floor($sx);
                    $frsy = $sy - floor($sy);
                    $frsx1 = 1 - $frsx;
                    $frsy1 = 1 - $frsy;

                    $newR = floor($r * $frsx1 * $frsy1 +
                        $rX * $frsx * $frsy1 +
                        $rY * $frsx1 * $frsy +
                        $rXY * $frsx * $frsy);
                    $newG = floor($g * $frsx1 * $frsy1 +
                        $gX * $frsx * $frsy1 +
                        $gY * $frsx1 * $frsy +
                        $gXY * $frsx * $frsy);
                    $newB = floor($b * $frsx1 * $frsy1 +
                        $bX * $frsx * $frsy1 +
                        $bY * $frsx1 * $frsy +
                        $bXY * $frsx * $frsy);
                }
                imagesetpixel($contents, $x, $y, imagecolorallocate($contents, $newR, $newG, $newB));
            }
        }
        return $contents;
    }

    /**
     * @param $x
     * @param $y
     * @param $nw
     * @param $ne
     * @param $sw
     * @param $se
     *
     * @return int
     */
    private function interpolate($x, $y, $nw, $ne, $sw, $se)
    {
        list($r0, $g0, $b0) = $this->getRGB($nw);
        list($r1, $g1, $b1) = $this->getRGB($ne);
        list($r2, $g2, $b2) = $this->getRGB($sw);
        list($r3, $g3, $b3) = $this->getRGB($se);

        $cx = 1.0 - $x;
        $cy = 1.0 - $y;

        $m0 = $cx * $r0 + $x * $r1;
        $m1 = $cx * $r2 + $x * $r3;
        $r = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $g0 + $x * $g1;
        $m1 = $cx * $g2 + $x * $g3;
        $g = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $b0 + $x * $b1;
        $m1 = $cx * $b2 + $x * $b3;
        $b = (int)($cy * $m0 + $y * $m1);

        return ($r << 16) | ($g << 8) | $b;
    }

    /**
     * @param resource $gd
     * @param $x
     * @param $y
     * @pram $background
     * @return array
     */
    private function getCol($gd, $x, $y, $background)
    {
        $L = imagesx($gd);
        $H = imagesy($gd);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return $background;
        }
        return imagecolorat($gd, $x, $y);
    }

    /**
     * @param $col
     *
     * @return array
     */
    private function getRGB($col)
    {
        return [
            (int)($col >> 16) & 0xff,
            (int)($col >> 8) & 0xff,
            (int)($col) & 0xff,
        ];
    }

    /**
     * 十六进制 转 RGB
     * @param string $hexColor
     * @return array
     */
    protected function hexToRgb($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = [
                hexdec(substr($color, 0, 2)),
                hexdec(substr($color, 2, 2)),
                hexdec(substr($color, 4, 2))
            ];
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = [
                hexdec($r),
                hexdec($g),
                hexdec($b)
            ];
        }
        return $rgb;
    }

    /**
     * @return resource
     */
    protected function gd()
    {
        return $this->image->getCore();
    }
}
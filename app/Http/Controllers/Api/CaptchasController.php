<?php

namespace App\Http\Controllers\Api;

use Gregwar\Captcha\PhraseBuilder;
use  Illuminate\Support\Str;
use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Requests\Api\CaptchaRequest;

class CaptchasController extends Controller
{
    public function store(Request $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha-'.Str::random(15);

        $result = [
            'captcha_key' => $key,
        ];

        return response()->json($result)->setStatusCode(201);
    }

    public function captcha($tmp)
    {
        ob_end_clean();

        $phrase = new PhraseBuilder;
        // 设置验证码位数
        $code = $phrase->build(4,123456789);
        // 生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder($code, $phrase);
        //生成验证码图片的Builder对象，配置相应属性
//        $builder = new CaptchaBuilder;
        //可以设置图片宽高及字体
        $builder->build($width = 100, $height = 40, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();
        $expiredAt = now()->addMinutes(2);

        //把内容存入session
        \Cache::put($tmp, ['code' => $phrase], $expiredAt);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/jpeg');
        $builder->output();
    }
}
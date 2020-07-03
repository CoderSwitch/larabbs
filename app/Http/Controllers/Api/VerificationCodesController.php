<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Auth\AuthenticationException;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request)
    {
        $captchaData = \Cache::get($request->captcha_key);

        if (!$captchaData) {
            return $this->output("",501,'图片验证码失效');
        }

        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            // 验证错误就清除缓存
            \Cache::forget($request->captcha_key);
            return $this->output('',500,'图片验证码错误');
        }

        $phone = $request->phone;

        // 生成4位随机数，左侧补0
        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);


        $key = 'verificationCode_'.Str::random(15);
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5分钟过期。
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);
        // 清除图片验证码缓存
        \Cache::forget($request->captcha_key);


        $result = array();
        $result['key'] = $key;
        $result['code'] = $code;
        $result['expired_at'] = $expiredAt->toDateTimeString();

        return $this->output($result,201,'');

//        return response()->json([
//            'key' => $key,
//            'code' => $code,
//            'expired_at' => $expiredAt->toDateTimeString(),
//        ])->setStatusCode(201);
    }
}
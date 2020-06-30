<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;


class UsersController extends Controller
{
    public function store(UserRequest $request)
    {
        $verifiData = \Cache::get($request->verification_key);
        if (!$verifiData){
            abort(403,'验证码失效');
        }

        if (!hash_equals($verifiData['code'],$request->verification_code)){
            // 返回401
            throw new AuthenticationException('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifiData['phone'],
            'password' => $request->password,
        ]);

        //清除验证码缓存
        \Cache::forget($request->verification_key);

        return new UserResource($user);
    }
}

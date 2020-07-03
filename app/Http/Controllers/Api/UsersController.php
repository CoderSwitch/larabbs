<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use App\Models\Image;
use Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

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

        return (new UserResource($user))->showSensitiveFields();
    }

    public function show(User $user, Request $request)
    {
        return new UserResource($user);
//        return response()->json($user)->setStatusCode(201);
    }

    public function showlist()
    {
//        $result = array();
//        $result['userlist'] = User::all();
        return $this->output(User::paginate(8),201,'');
    }

    public function me(Request $request)
    {
        return $this->output($request->user(),201,'');
//        $token = $request->headers->get('aaaa');
////        return $token;
//
//        $user = JWTAuth::toUser($token);;
//        return (new UserResource($user))->showSensitiveFields();
    }

    public function update(UserRequest $request)
    {
        $user = $request->user();

        $attributes = $request->only(['name', 'email', 'introduction']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);

            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return (new UserResource($user))->showSensitiveFields();
    }
}

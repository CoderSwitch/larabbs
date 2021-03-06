<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\AuthorizationRequest;
use Illuminate\Auth\AuthenticationException;

class AuthorizationsController extends Controller
{
    //
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;

        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['phone'] = $username;

        $credentials['password'] = $request->password;

        if (!$token = \Auth::guard('api')->attempt($credentials)) {
            return $this->output('',401,'用户名或密码错误');
//            throw new AuthenticationException('用户名或密码错误');
        }



        return $this->output($this->respondWithToken($token),201,'');

//        return $this->respondWithToken($token)->setStatusCode(201);

    }

    public function update()
    {
        $token = auth('api')->refresh();
        return $this->respondWithToken($token);
    }

    public function destroy()
    {
        auth('api')->logout();
        return response(null, 204);
    }

    protected function respondWithToken($token)
    {
        $result = array();
        $result['access_token'] = $token;
        $result['token_type'] = 'Bearer';
        $result['expires_in'] = auth('api')->factory()->getTTL() * 60;

        return $result;
//        return response()->json([
//            'access_token' => $token,
//            'token_type' => 'Bearer',
//            'expires_in' => auth('api')->factory()->getTTL() * 60
//        ]);
    }
}

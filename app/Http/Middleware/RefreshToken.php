<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

// 注意，我们要继承的是 jwt 的 BaseMiddleware
class RefreshToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $this->checkForToken($request);

        // 使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        try {
            // 检测用户的登录状态，如果正常则通过
            if ($this->auth->parseToken()->authenticate()) {
                return $next($request);
            }
            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        } catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
//                // 刷新用户的 token
//                $token = $this->auth->refresh();
//                // 使用一次性登录以保证此次请求的ß成功
//                Auth::guard('api')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
                //首先获得过期token 接着刷新token 再接着设置token并且验证token合法性
                $token = JWTAuth::refresh(JWTAuth::getToken());
                JWTAuth::setToken($token);
                $request->user = JWTAuth::authenticate($token);
                // token被刷新之后，保证本次请求在controller中需要根据token调取登录用户信息能够执行成功
                $request->headers->set('Authorization','Bearer '.$token);
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                $result = array('data'=>'','message'=>'请先登录','code'=>115);
                return response($result);
//                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage(),null,'115');
            }
        }

//        $request->headers->set('aaaa', $token);

        // 在响应头中返回新的 token
        return $this->setAuthenticationHeader($next($request), $token);
    }
}
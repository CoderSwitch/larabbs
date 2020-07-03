<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('v1')
    ->namespace('Api')
    ->name('api.v1.')
    ->group(function() {

        // 图片验证码
        Route::post('captchas', 'CaptchasController@store')
            ->name('captchas.store');

        Route::get('captchas/{tmp}', 'CaptchasController@captcha');

// 短信验证码
        Route::post('verificationCodes', 'VerificationCodesController@store')
            ->name('verificationCodes.store');

        // 登录
        Route::post('authorizations', 'AuthorizationsController@store')
            ->name('api.authorizations.store');

        Route::middleware('throttle:' . config('api.rate_limits.sign'))
        ->group(function () {


            // 用户注册
            Route::post('users', 'UsersController@store')
                ->name('users.store');

            // 刷新token
            Route::put('authorizations/current', 'AuthorizationsController@update')
                ->name('authorizations.update');
            // 删除token
            Route::delete('authorizations/current', 'AuthorizationsController@destroy')
                ->name('authorizations.destroy');
        });

        Route::middleware('throttle:' . config('api.rate_limits.access'))
            ->group(function () {
                // 游客可以访问的接口

                // 用户列表
                Route::get('userslist', 'UsersController@showlist')
                    ->name('users.showlist');

                // 某个用户的详情
                Route::get('users/{user}', 'UsersController@show')
                    ->name('users.show');

                // 分类列表
                Route::get('categories', 'CategoriesController@index')
                    ->name('categories.index');

                // 话题列表，详情
                Route::resource('topics', 'TopicsController')->only([
                    'index', 'show'
                ]);

                // 某个用户发布的话题
                Route::get('getusertopics', 'TopicsController@userIndex')
                    ->name('users.topics.index');

                // 登录后可以访问的接口
                Route::middleware(['token.canrefresh'])->group(function() {
                    // 当前登录用户信息
                    Route::get('user', 'UsersController@me')
                        ->name('user.show');
                    // 编辑登录用户信息
                    Route::patch('user', 'UsersController@update')
                        ->name('user.update');
                    // 上传图片
                    Route::post('images', 'ImagesController@store')
                        ->name('images.store');
                    // 发布话题
                    Route::resource('topics', 'TopicsController')->only([
                        'store'
                    ]);
                    //修改话题
                    Route::post('update', 'TopicsController@update')
                        ->name('topics.update');
                    //删除话题
                    Route::post('deletetopic', 'TopicsController@deletetopic')
                        ->name('topics.deletetopic');

                    // 发布回复
                    Route::post('topics/replies', 'RepliesController@store')
                        ->name('topics.replies.store');
                });
            });
});


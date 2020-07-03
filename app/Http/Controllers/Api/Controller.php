<?php

namespace App\Http\Controllers\Api;

use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    //
    /*
     * 组装请求返回结果json
     * **/
    public function output($data,$code,$msg=''){
        if(!empty($msg)){
            $result = [
                'data' 		=> $data,
                'code' 		=> $code,
                'message' 	=> $msg
            ];

            return response()->json($result);
        }else {
            $result = [
                'data' 		=> $data,
                'code' 		=> $code,
                'message' 	=> $msg
            ];

            return response()->json($result);
        }
    }
}
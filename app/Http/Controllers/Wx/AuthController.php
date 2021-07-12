<?php


namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. 获取参数
        $username = $request -> input('username');
        $password = $request -> input('password');
        $mobile = $request -> input('mobile');
        $code = $request -> input('code');
        if (empty($username) || empty($password) || empty($code) || empty($mobile)) {
            return [
                'errno' => 401,
                'errmsg' => '参数不对'
            ];
        }
        // 2. 验证参数是否为空
        // 3. 验证验证码是否正确
        // 4. 写入用户表
        // 5. 新用户发券
        // 6. 返回用户信息和token

    }
}

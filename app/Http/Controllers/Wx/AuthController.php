<?php


namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. 获取参数
        $username = $request -> input('username');
        $password = $request -> input('password');
        $mobile = $request -> input('mobile');
        $code = $request -> input('code');
        // 2. 验证参数是否为空
        if (empty($username) || empty($password) || empty($code) || empty($mobile)) {
            return [
                'errno' => 401,
                'errmsg' => '参数不对'
            ];
        }

        $user = (new UserServices())->getByUserName($username);
        if (!is_null($user)) {
            return [
                'errno' => 704,
                'errmsg' => '用户名已注册'
            ];
        }
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return [
                'errno' => 707,
                'errmsg' => '手机号格式不正确'
            ];
        }
        $user = (new UserServices())->getByMobile($mobile);
        if (!is_null($user)) {
            return [
                'errno' => 705,
                'errmsg' => '手机号已注册'
            ];
        }
        // 3. 验证验证码是否正确
        // todo 验证验证码是否正确
        $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        if (!$isPass) {
            return ['errno' => 703, 'errmsg' => '验证码错误'];
        }
        // 4. 写入用户表
        $user = new User();
        // 相当于 user.setUsername(username)
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->avatar = "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64";
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateString(); // 'Y-m-d H:i:s' 2020-05-17 16:17:34
        $user->last_login_ip = $request->getClientIp();
        $user->save();
        // 5. 新用户发券
        // 6. 返回用户信息和token
        return [
            'errno' => 0,
            'errmsg' => '成功',
            'data' => [
                'token'=>'',
                'userInfo'=> [
                    'nickName' => $username,
                    'avatarUrl' => $user->avatar
                ]
            ]
        ];
    }

    public function regCaptcha(Request $request)
    {
        // todo 获取手机号
        $mobile = $request->input('mobile');
        if (empty($mobile)) {
            return ['errno' => 401, 'errmsg' => '参数不对'];
        }
        // todo 验证手机号是否合法
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return [
                'errno' => 707,
                'errmsg' => '手机号格式不正确'
            ];
        }
        // todo 验证手机号是否已经被注册
        $user = (new UserServices())->getByMobile($mobile);
        if (!is_null($user)) {
            return [
                'errno' => 705,
                'errmsg' => '手机号已注册'
            ];
        }
        // todo 防刷验证，一分钟内只能请求一次， 当天值能请求 10 次
        $lock = Cache::add('register_captcha_lock_'.$mobile, 1, 60); // 如果缓存存在，则返回 false (put, 则直接覆盖), 把手机号锁 60 秒
        if (!$lock) {
            return [
                'errno' => 702,
                'errmsg' => '验证码未超过一分钟，不能发送'
            ];
        }
        // 为了单元测试方便，此时需要把这部分逻辑封装成一个函数, 放到 service 里
//        $countKey = 'register_captcha_count_'.$mobile;
//        if(Cache::has($countKey)){
//            $count = Cache::increment('register_captcha_count_'.$mobile);
//            if ($count > 10) {
//                return [
//                    'errno' => 702,
//                    'errmsg' => '验证码当天发送不能超过10次'
//                ];
//            }
//        } else {
            // 从当前时间到第二天 0 点的时间间隔
//            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
//        }
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return [
                'errno' => 702,
                'errmsg' => '验证码当天发送不能超过10次'
            ];
        }
        // todo 随机生成6位验证码
        // todo 保存手机号和验证码关系
        $code = (new UserServices())->setCaptcha(($mobile));
        (new UserServices())->sendCaptchaMsg($mobile, $code);
        // todo 发送短信 该部分可以封装成一个函数，在开发环境下，并不需要真正发送短信
        (new UserServices())->sendCaptchaMsg($mobile, $code);
//        Notification::route(
//            EasySmsChannel::class,
//            new PhoneNumber(13333333333, 86)
//        )->notify(new VerificationCode($code));

        return ['errno' => 0, 'errmsg' => '成功', 'data' => null];



    }


}

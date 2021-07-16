<?php


namespace App\Services;


use App\Models\User;

class UserServices
{
    /**
     * 根据用户名获取用户
     * @param $username
     * @return User|null|Model
     */
    public function getByUserName($username) {
        return User::query()->where('username', $username)->where('deleted', 0)->first();
    }

    /**
     * 根据手机号获取用户
     * @param $mobile
     * @return User|null|Model
     */
    public function getByMobile($mobile) {
        return User::query()->where('mobile', $mobile)->where('deleted', 0)->first();
    }


    /**
     * 验证手机号发送验证码是否达到限制条数
     * @param  string  $mobile
     * @return bool
     */
    public function checkMobileSendCaptchaCount(string $mobile)
    {
        $countKey = 'register_captcha_count_'.$mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            // 从当前时间到第二天 0 点的时间间隔
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
        return true;
    }

    /**
     * 发送验证码短信
     * @param  string  $mobile
     * @param  string  $code
     */
    public function sendCaptchaMsg(string $mobile, string $code) {
        if (app()->environment('testing')) {
            return;
        }
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber(13333333333, 86)
        )->notify(new VerificationCode($code));
    }

    /**
     * 验证短信验证码
     * @param  string  $mobile
     * @param  string  $code
     * @return bool
     */
    public function checkCaptcha(string $mobile, string $code) {
        $key = 'register_captcha_'.$mobile;
        $isPass = $code === Cache::get($key);
        // 如果验证通过，应该把 key 删除
        if ($isPass) {
            Cache::forget($key);
        }
        return $isPass;
    }

    /**
     * 设置手机短信验证码
     * @param  string  $mobile
     * @return string
     * @throws \Exception
     */
    public function setCaptcha(string $mobile) {
        // todo 随机生成6位验证码
        $code = random_int(100000, 999999); // 为了简单，10万起
        $code = strval($code);
        // todo 保存手机号和验证码关系
        // key: $mobile, value: $code, TTL: 10 mins
        Cache::put('register_captcha_'.$mobile, $code, 600); // 这里把 cellphone 和 验证码的关系 存在 redis 里面
        return $code;

    }

}

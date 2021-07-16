<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserServices;
use tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * 需要测试手机号被调用10次后为 false， 否则为 true
     */
    public function testCheckMobileSendCaptchaCount()
    {
        $mobile = '13111111111';
        foreach (range(0, 9) as $i) {
            $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
            $this->assertTrue($isPass);
        }
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);
        $countKey = 'register_captcha_count_'.$mobile;
        Cache::forget($countKey);
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        $this->assertTrue($isPass);
    }

    public function testCheckCaptcha() {
        $mobile = '13111111111';
        $code = (new UserServices())->setCaptcha($mobile);
        $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        $this->assertTrue($isPass);
        $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        $this->assertFalse($isPass);
    }
}

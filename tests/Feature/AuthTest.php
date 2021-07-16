<?php

namespace Tests\Feature;

use App\Services\User\UserServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // 在此执行的所有数据库事务都不会进行提交,避免产生脏数据
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    // 写用例的时候一定要有一个正确的用例多个异常的用例
    public function testRegister()
    {
        $response = $this->post('wx/auth/register',
            [
                'username' => 'tanfan2',
                'password' => '123456',
                'mobile' => '13111111111',
                'code' => '1234'
            ]);
        $response->assertStatus(200);
        // getContent 是 json, getOriginalContent 原始值，这里是数组
        $ret = $response->getOriginalContent();
        // 第一个参数： 预期值， 第二个参数： 实际值
        $this->assertEquals(0, $ret['errno']); //fail 0 != 704
        $this->assertNotEmpty($ret['data']);

    }

    public function testRegisterMobile()
    {
        $response = $this->post('wx/auth/register',
            [
                'username' => 'tanfan2',
                'password' => '123456',
                'mobile' => '13111111131',
                'code' => '1234'
            ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals(707, $ret['errno']); //fail 0 != 704
    }

    public function testRegCaptcha()
    {
        $response = $this->post('wx/auth/regCaptcha',
            [
                'mobile' => '13111112131',
            ]);
        // 可以直接验证 json, 比 Equals 更好，但是只会断言提供的字段
        $response->assertJson(['errno' => 0, 'errmsg' => '成功', 'data' => null]);
        // 测试一分钟之类发送两次
        $response = $this->post('wx/auth/regCaptcha',
            [
                'mobile' => '13111112131',
            ]);
        $response->assertJson(['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送']);
    }
}

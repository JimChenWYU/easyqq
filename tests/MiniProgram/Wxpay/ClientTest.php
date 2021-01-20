<?php

namespace EasyQQ\Tests\MiniProgram\Wxpay;

use EasyQQ\MiniProgram\Application;
use EasyQQ\MiniProgram\Auth\AccessToken;
use EasyQQ\MiniProgram\Wxpay\Client;
use EasyQQ\Kernel\Support;
use EasyQQ\Tests\TestCase;

/**
 * Class ClientTest
 *
 * @author JimChen <imjimchen@163.com>
 */
class ClientTest extends TestCase
{
    protected function app()
    {
        return new Application([
        	'app_id'        => 'qq123456',
            'wechat_app_id' => 'wx123456',
            'wechat_mch_id' => 'wechat-merchant-id',
            'notify_url' => 'http://easyqq.org/notify',
        ]);
    }

    public function testUnify()
    {
        $client = $this->mockApiClient(Client::class, ['paymentRequest'], $this->app());
        $token = $this->mockApiClient(AccessToken::class, ['getToken'], $this->app());
        $token->expects()->getToken()->andReturn(['access_token' => 'foobar']);
        $client->setAccessToken($token);
        $order = [
	        'total_fee' => 100,
	        'body' => 'test'
        ];
        $client->expects()->paymentRequest('wxpay/unifiedorder', array_merge($order, [
            'spbill_create_ip' => Support\Utils::getClientIp(),
            'trade_type' => 'MWEB',
            'appid' => 'wx123456',
            'notify_url' => 'https://api.q.qq.com/wxpay/notify',
        ]), 'post', [
            'query' => [
                'appid' => 'qq123456',
                'access_token' => 'foobar',
                'real_notify_url' => 'http://easyqq.org/notify'
            ],
        ])->andReturn('mock-result');

        self::assertSame('mock-result', $client->unify($order));
    }
}

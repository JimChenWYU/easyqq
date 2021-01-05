<?php

namespace EasyQQ\Tests\Payment\Base;

use EasyQQ\Payment\Application;
use EasyQQ\Payment\Base\Client;
use EasyQQ\Tests\TestCase;

class ClientTest extends TestCase
{
    public function testPay()
    {
        $app = new Application(['app_id' => 'mock-appid', 'key' => '88888888888888888888888888888888']);

        $client = $this->mockApiClient(Client::class, ['pay'], $app)->makePartial();

        $order = [
            'appid' => 'mock-appid',
            'foo' => 'bar',
        ];

        $client->expects()->request('pay/qpay_micro_pay.cgi', $order)->andReturn('mock-result');
        self::assertSame('mock-result', $client->pay($order));
    }
}

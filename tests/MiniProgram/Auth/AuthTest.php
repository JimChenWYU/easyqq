<?php

namespace EasyQQ\Tests\MiniProgram\Auth;

use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\MiniProgram\Auth\Client;
use EasyQQ\Tests\TestCase;

class AuthTest extends TestCase
{
    public function testGetSessionKey()
    {
        $client = $this->mockApiClient(
            Client::class,
            [],
            new ServiceContainer(['app_id' => 'app-id', 'secret' => 'mock-secret'])
        );

        $client->expects()->httpGet('sns/jscode2session', [
            'appid' => 'app-id',
            'secret' => 'mock-secret',
            'js_code' => 'js-code',
            'grant_type' => 'authorization_code',
        ])->andReturn('mock-result');

        self::assertSame('mock-result', $client->session('js-code'));
    }
}

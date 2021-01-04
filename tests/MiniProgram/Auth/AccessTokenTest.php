<?php

namespace EasyQQ\Tests\MiniProgram\Auth;

use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\MiniProgram\Auth\AccessToken;
use EasyQQ\Tests\TestCase;
use Mockery;

class AccessTokenTest extends TestCase
{
    public function testGetCredentials()
    {
        $app = new ServiceContainer([
            'app_id' => 'mock-app-id',
            'secret' => 'mock-secret',
        ]);
        $token = Mockery::mock(AccessToken::class, [$app])->makePartial()->shouldAllowMockingProtectedMethods();

        self::assertSame([
            'grant_type' => 'client_credential',
            'appid' => 'mock-app-id',
            'secret' => 'mock-secret',
        ], $token->getCredentials());
    }
}

<?php

namespace EasyQQ\Tests\Payment\Jssdk;

use EasyQQ\Payment\Application;
use EasyQQ\Payment\Jssdk\Client;
use EasyQQ\Tests\TestCase;

class ClientTest extends TestCase
{
    private function makeApp($config = [])
    {
        return new Application(array_merge([
            'app_id' => 'wx123456',
            'mch_id' => 'foo-mcherant-id',
            'key' => 'foo-mcherant-key',
            'sub_appid' => 'foo-sub-appid',
            'sub_mch_id' => 'foo-sub-mch-id',
        ], $config));
    }

    public function testBridgeConfig()
    {
        $app = new Application([
            'app_id' => 'wx123456',
            'mch_id' => 'foo-mcherant-id',
            'key' => 'foo-mcherant-key',
            'sub_mch_id' => 'foo-sub-mch-id',
        ]);

        $client = $this->mockApiClient(Client::class, 'bridgeConfig', $app)->makePartial();

        $prepayId = 'foo';

        // return json
        $config = json_decode($client->bridgeConfig($prepayId, '', '', true), true);
        self::assertArrayHasKey('tokenId', $config);
        self::assertArrayHasKey('pubAcc', $config);
        self::assertArrayHasKey('pubAccHint', $config);
        self::assertArrayHasKey('appInfo', $config);
        self::assertSame("appid#{$app['config']->app_id}|bargainor_id#{$app['config']->mch_id}|channel#wallet", $config['appInfo']);

        // return array
        $config = $client->bridgeConfig($prepayId, '', '', false);
        self::assertArrayHasKey('tokenId', $config);
        self::assertArrayHasKey('pubAcc', $config);
        self::assertArrayHasKey('pubAccHint', $config);
        self::assertArrayHasKey('appInfo', $config);
        self::assertSame("appid#{$app['config']->app_id}|bargainor_id#{$app['config']->mch_id}|channel#wallet", $config['appInfo']);
    }

    public function testAppConfig()
    {
        $app = $this->makeApp();

        $client = $this->mockApiClient(Client::class, 'appConfig', $app)->makePartial();

        $prepayId = 'foo';

        $config = $client->appConfig($prepayId);
        self::assertArrayHasKey('appId', $config);
        self::assertArrayHasKey('nonce', $config);
        self::assertArrayHasKey('timeStamp', $config);
        self::assertArrayHasKey('tokenId', $config);
        self::assertArrayHasKey('pubAcc', $config);
        self::assertArrayHasKey('pubAccHint', $config);
        self::assertArrayHasKey('bargainorId', $config);
        self::assertArrayHasKey('sigType', $config);
        self::assertArrayHasKey('sig', $config);

        self::assertSame($app['config']->app_id, $config['appId']);
        self::assertSame($app['config']->mch_id, $config['bargainorId']);
        self::assertSame($prepayId, $config['tokenId']);
    }
}

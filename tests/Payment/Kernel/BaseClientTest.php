<?php

namespace EasyQQ\Tests\Payment\Kernel;

use EasyQQ\Kernel\Http\Response;
use EasyQQ\Kernel\Support;
use EasyQQ\Payment\Application;
use EasyQQ\Payment\Kernel\BaseClient;
use EasyQQ\Tests\TestCase;
use Mockery;

class BaseClientTest extends TestCase
{
    public function testRequest()
    {
        $app = new Application(['key' => '88888888888888888888888888888888']);

        $client = $this->mockApiClient(BaseClient::class, ['performRequest', 'castResponseToType'], $app)->makePartial();

        $api = 'http://easyqq.org';
        $params = ['foo' => 'bar'];
        $method = Mockery::anyOf(['get', 'post']);
        $options = ['foo' => 'bar'];

        $mockResponse = new Response(200, [], 'response-content');

        $client->expects()->performRequest($api, $method, Mockery::on(function ($options) {
            self::assertSame('bar', $options['foo']);
            self::assertIsString($options['body']);

            $bodyInOptions = Support\XML::parse($options['body']);

            self::assertSame($bodyInOptions['foo'], $options['foo']);
            self::assertIsString($bodyInOptions['nonce_str']);
            self::assertIsString($bodyInOptions['sign']);

            return true;
        }))->times(3)->andReturn($mockResponse);

        $client->expects()->castResponseToType()
            ->with($mockResponse, Mockery::any())
            ->andReturn(['foo' => 'mock-bar']);

        // $returnResponse = false
        self::assertSame(['foo' => 'mock-bar'], $client->request($api, $params, $method, $options, false));

        // $returnResponse = true
        self::assertInstanceOf(Response::class, $client->request($api, $params, $method, $options, true));
        self::assertSame('response-content', $client->request($api, $params, $method, $options, true)->getBodyContents());
    }

    public function testRequestRaw()
    {
        $app = new Application();

        $client = $this->mockApiClient(BaseClient::class, ['request', 'requestRaw'], $app)->makePartial();

        $api = 'http://easyqq.org';
        $params = ['foo' => 'bar'];
        $method = Mockery::anyOf(['get', 'post']);
        $options = [];

        $client->expects()->request($api, $params, $method, $options, true)->andReturn('mock-result');

        self::assertSame('mock-result', $client->requestRaw($api, $params, $method, $options));
    }

    public function testSafeRequest()
    {
        $app = new Application([
            'app_id' => 'wx123456',
            'cert_path' => 'foo',
            'key_path' => 'bar',
        ]);

        $client = $this->mockApiClient(BaseClient::class, ['safeRequest'], $app)->makePartial();

        $api = 'http://easyqq.org';
        $params = ['foo' => 'bar'];
        $method = Mockery::anyOf(['get', 'post']);

        $client->expects()->request($api, $params, $method, Mockery::on(function ($options) use ($app) {
            self::assertSame($options['cert'], $app['config']->get('cert_path'));
            self::assertSame($options['ssl_key'], $app['config']->get('key_path'));

            return true;
        }))->andReturn('mock-result');

        self::assertSame('mock-result', $client->safeRequest($api, $params, $method));
    }

    /**
     * @dataProvider bodySignProvider
     */
    public function testBodySign($signType, $nonceStr, $sign)
    {
        $app = new Application([
            'key' => '88888888888888888888888888888888',
        ]);

        $client = $this->mockApiClient(BaseClient::class, ['performRequest'], $app)->makePartial();

        $api = 'http://easyqq.org';
        $params = [
            'foo' => 'bar',
            'nonce_str' => $nonceStr,
            'sign_type' => $signType,
        ];
        $method = Mockery::anyOf(['get', 'post']);
        $options = [];

        $mockResponse = new Response(200, [], 'response-content');

        $client->expects()->performRequest($api, $method, Mockery::on(function ($options) use ($sign) {
            $bodyInOptions = Support\XML::parse($options['body']);

            self::assertSame($sign, $bodyInOptions['sign']);

            return true;
        }))->andReturn($mockResponse);

        self::assertSame('response-content', $client->requestRaw($api, $params, $method, $options)->getBodyContents());
    }

    public function bodySignProvider()
    {
        return [
            ['', '5c3bfd3227348', '82125D68D3C25B2B78D53F66E12EC89A'],
            ['MD5', '5c3bfe0343bab', 'A9237F1A2DF77FF900CFFB7B432CD1A9'],
            ['HMAC-SHA256', '5c3bfe6716023', 'A890BD78E9B1563C546D07F21E8C8D96B146CFE5B18941C312678B5636263DE6'],
        ];
    }
}

<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyQQ\Tests\Kernel;

use EasyQQ\Kernel\AccessToken;
use EasyQQ\Kernel\BaseClient;
use EasyQQ\Kernel\Http\Response;
use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use Mockery;
use Monolog\Logger;
use function sprintf;

class BaseClientTest extends TestCase
{
    public function makeClient($methods = [], ServiceContainer $app = null, AccessToken $accessToken = null)
    {
        $methods = !empty($methods) ? sprintf('[%s]', implode(',', (array) $methods)) : '';
        $app = $app ?? Mockery::mock(ServiceContainer::class);
        $accessToken = $accessToken ?? Mockery::mock(AccessToken::class, [$app]);

        return Mockery::mock(BaseClient::class."{$methods}", [$app, $accessToken])->makePartial();
    }

    public function testHttpGet()
    {
        $client = $this->makeClient('request');
        $url = 'http://easyqq.org';
        $query = ['foo' => 'bar'];
        $client->expects()->request($url, 'GET', ['query' => $query])->andReturn('mock-result');
        self::assertSame('mock-result', $client->httpGet($url, $query));
    }

    public function testHttpPost()
    {
        $client = $this->makeClient('request');
        $url = 'http://easyqq.org';

        $data = ['foo' => 'bar'];
        $client->expects()->request($url, 'POST', ['form_params' => $data])->andReturn('mock-result');
        self::assertSame('mock-result', $client->httpPost($url, $data));
    }

    public function testHttpPostJson()
    {
        $client = $this->makeClient('request');
        $url = 'http://easyqq.org';

        $data = ['foo' => 'bar'];
        $query = ['appid' => 1234];
        $client->expects()->request($url, 'POST', ['query' => $query, 'json' => $data])->andReturn('mock-result');
        self::assertSame('mock-result', $client->httpPostJson($url, $data, $query));
    }

    public function testHttpUpload()
    {
        $client = $this->makeClient('request');
        $url = 'http://easyqq.org';
        $path = STUBS_ROOT.'/files/image.jpg';
        $files = [
            'media' => $path,
        ];
        $form = [
            'type' => 'image',
        ];
        $query = ['appid' => 1234];
        $client->expects()->request($url, 'POST', Mockery::on(function ($params) use ($query, $path) {
            self::assertArrayHasKey('query', $params);
            self::assertArrayHasKey('multipart', $params);
            self::assertSame($query, $params['query']);
            self::assertSame('media', $params['multipart'][0]['name']);
            self::assertIsResource($params['multipart'][0]['contents']);

            return true;
        }))->andReturn('mock-result');

        self::assertSame('mock-result', $client->httpUpload($url, $files, $form, $query));
    }

    public function testAccessToken()
    {
        $client = $this->makeClient();
        self::assertInstanceOf(AccessToken::class, $client->getAccessToken());

        $accessToken = Mockery::mock(AccessToken::class);
        $client->setAccessToken($accessToken);

        self::assertSame($accessToken, $client->getAccessToken());
    }

    public function testRequest()
    {
        $url = 'http://easyqq.org';
        $app = new ServiceContainer([
            'response_type' => 'array',
        ]);
        $client = $this->makeClient(['registerHttpMiddlewares', 'performRequest'], $app)
            ->shouldAllowMockingProtectedMethods();

        // default value
        $client->expects()->registerHttpMiddlewares();
        $client->expects()->performRequest($url, 'GET', [])->andReturn(new Response(200, [], '{"mock":"result"}'));
        self::assertSame(['mock' => 'result'], $client->request($url));

        // return raw with custom arguments
        $options = ['foo' => 'bar'];
        $response = new Response(200, [], '{"mock":"result"}');
        $client->expects()->registerHttpMiddlewares();
        $client->expects()->performRequest($url, 'POST', $options)->andReturn($response);
        self::assertSame($response, $client->request($url, 'POST', $options, true));
    }

    public function testRequestRaw()
    {
        $url = 'http://easyqq.org';
        $response = new Response(200, [], '{"mock":"result"}');
        $client = $this->makeClient('request');
        $client->expects()->request($url, 'GET', [], true)->andReturn($response);

        self::assertInstanceOf(Response::class, $client->requestRaw($url));
    }

    public function testHttpClient()
    {
        // default
        $app = new ServiceContainer();
        $client = $this->makeClient('request', $app);
        self::assertInstanceOf(Client::class, $client->getHttpClient());

        // custom client
        $http = new Client(['base_uri' => 'http://easyqq.com']);
        $app = new ServiceContainer([], [
            'http_client' => $http,
        ]);

        $client = $this->makeClient('request', $app);
        self::assertSame($http, $client->getHttpClient());
    }

    public function testRegisterMiddlewares()
    {
        $client = $this->makeClient(['retryMiddleware', 'accessTokenMiddleware', 'logMiddleware', 'pushMiddleware'])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $retryMiddleware = function () {
            return 'retry';
        };
        $logMiddleware = function () {
            return 'log';
        };
        $accessTokenMiddleware = function () {
            return 'access_token';
        };
        $client->expects()->retryMiddleware()->andReturn($retryMiddleware);
        $client->expects()->accessTokenMiddleware()->andReturn($accessTokenMiddleware);
        $client->expects()->logMiddleware()->andReturn($logMiddleware);
        $client->expects()->pushMiddleware($retryMiddleware, 'retry');
        $client->expects()->pushMiddleware($accessTokenMiddleware, 'access_token');
        $client->expects()->pushMiddleware($logMiddleware, 'log');

        $client->registerHttpMiddlewares();
    }

    public function testAccessTokenMiddleware()
    {
        $app = new ServiceContainer([]);
        $accessToken = Mockery::mock(AccessToken::class.'[applyToRequest]', [$app]);
        $client = $this->makeClient(['accessTokenMiddleware'], $app, $accessToken)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $func = $client->accessTokenMiddleware();

        $request = new Request('GET', 'http://easyqq.com');
        $options = ['foo' => 'bar'];
        $accessToken->expects()->applyToRequest($request, $options)->andReturn($request);

        $middleware = $func(function ($request, $options) {
            return compact('request', 'options');
        });
        $result = $middleware($request, $options);

        self::assertSame($request, $result['request']);
        self::assertSame($options, $result['options']);
    }

    public function testLogMiddleware()
    {
        $app = new ServiceContainer([
            'http' => [
                'log_template',
            ],
        ]);
        $app['logger'] = new Logger('logger');
        $client = $this->makeClient(['accessTokenMiddleware'], $app)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        self::assertInstanceOf('Closure', $client->logMiddleware());
    }

    public function testRetryMiddleware()
    {
        // no retries configured
        $app = new ServiceContainer([]);
        $app['logger'] = $logger = Mockery::mock('stdClass');
        $accessToken = Mockery::mock(AccessToken::class, [$app]);
        $client = $this->makeClient(['retryMiddleware'], $app, $accessToken)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $func = $client->retryMiddleware();

        // default once with right response
        $logger->expects()->debug('Retrying with refreshed access token.');
        $accessToken->expects()->refresh();
        $handler = new MockHandler([
            new Response(200, [], '{"errcode":40001}'),
            new Response(200, [], '{"success": true}'),
        ]);
        $handler = $func($handler);
        $c = new Client(['handler' => $handler]);
        $p = $c->sendAsync(new Request('GET', 'http://easyqq.com'), []);
        $response = $p->wait();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"success": true}', $response->getBody()->getContents());

        // default once with error response
        $logger->expects()->debug('Retrying with refreshed access token.');
        $accessToken->expects()->refresh();
        $handler = new MockHandler([
            new Response(200, [], '{"errcode":40001}'),
            new Response(200, [], '{"errcode":42001}'),
            new Response(200, [], '{"success": true}'),
        ]);
        $handler = $func($handler);
        $c = new Client(['handler' => $handler]);
        $p = $c->sendAsync(new Request('GET', 'http://easyqq.com'), []);
        $response = $p->wait();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"errcode":42001}', $response->getBody()->getContents());

        // default once with configured retries
        $app['config']['http'] = ['max_retries' => 0];
        $logger->expects()->debug('Retrying with refreshed access token.')->never();
        $accessToken->expects()->refresh()->never();
        $handler = new MockHandler([
            new Response(200, [], '{"errcode":40001}'),
            new Response(200, [], '{"errcode":42001}'),
            new Response(200, [], '{"success": true}'),
        ]);
        $handler = $func($handler);
        $c = new Client(['handler' => $handler]);
        $p = $c->sendAsync(new Request('GET', 'http://easyqq.com'), []);
        $response = $p->wait();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"errcode":40001}', $response->getBody()->getContents());

        // 3 times
        $app['config']['http'] = [
            'max_retries' => 3,
            'retry_delay' => 1,
        ];
        $logger->expects()->debug('Retrying with refreshed access token.')->times(3);
        $accessToken->expects()->refresh()->times(3);
        $handler = new MockHandler([
            new Response(200, [], '{"errcode":40001}'),
            new Response(200, [], '{"errcode":42001}'),
            new Response(200, [], '{"errcode":40001}'),
            new Response(200, [], '{"success":true}'),
        ]);
        $handler = $func($handler);
        $c = new Client(['handler' => $handler]);
        $s = microtime(true);
        $p = $c->sendAsync(new Request('GET', 'http://easyqq.com'), []);
        $response = $p->wait();

        self::assertTrue(microtime(true) - $s >= 3 * ($app['config']['http']['retry_delay'] / 1000), 'delay time'); // times * delay
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"success":true}', $response->getBody()->getContents());
    }
}

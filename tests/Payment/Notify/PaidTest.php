<?php

namespace EasyQQ\Tests\Payment\Notify;

use EasyQQ\Kernel\Support\XML;
use EasyQQ\Payment\Application;
use EasyQQ\Payment\Kernel\Exceptions\InvalidSignException;
use EasyQQ\Payment\Notify\Paid;
use EasyQQ\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaidTest extends TestCase
{
    private function makeApp($config = [])
    {
        return new Application(array_merge([
            'key' => '88888888888888888888888888888888',
        ], $config));
    }

    public function testPaidNotify()
    {
        $app = $this->makeApp();
        $app['request'] = Request::create('', 'POST', [], [], [], [], '<xml>
<foo>bar</foo>
<sign>834A25C9A5B48305AB997C9A7E101530</sign>
</xml>');

        $notify = new Paid($app);

        $that = $this;
        $response = $notify->handle(function ($message) use ($that) {
            $that->assertSame([
                'foo' => 'bar',
                'sign' => '834A25C9A5B48305AB997C9A7E101530',
            ], $message);

            return true;
        });

        // return true.
        self::assertInstanceOf(Response::class, $response);
        self::assertSame([
            'return_code' => 'SUCCESS',
            'return_msg' => null,
        ], XML::parse($response->getContent()));

        // return false.
        $response = $notify->handle(function () {
            return false;
        });

        self::assertSame([
            'return_code' => 'FAIL',
            'return_msg' => null,
        ], XML::parse($response->getContent()));

        // empty return.
        $response = $notify->handle(function () {
        });

        self::assertSame([
            'return_code' => 'FAIL',
            'return_msg' => null,
        ], XML::parse($response->getContent()));

        $response = $notify->handle(function ($msg, $fail) {
            $fail('fails.');
        });

        self::assertSame([
            'return_code' => 'FAIL',
            'return_msg' => 'fails.',
        ], XML::parse($response->getContent()));
    }

    public function testInvalidSign()
    {
        $app = $this->makeApp();
        $app['request'] = Request::create('', 'POST', [], [], [], [], '<xml>
<foo>bar</foo>
<sign>invalid-sign</sign>
</xml>');
        $notify = new Paid($app);
        $this->expectException(InvalidSignException::class);
        $notify->handle(function () {
        });
    }
}

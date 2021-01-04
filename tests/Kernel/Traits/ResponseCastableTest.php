<?php

namespace EasyQQ\Tests\Kernel\Traits;

use EasyQQ\Kernel\Contracts\Arrayable;
use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Http\Response;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Kernel\Traits\ResponseCastable;
use EasyQQ\Tests\TestCase;
use Mockery;

class ResponseCastableTest extends TestCase
{
    public function testCastResponseToType()
    {
        $cls = Mockery::mock(DummyClassForResponseCastable::class);

        $response = new Response(200, [], '{"foo": "bar"}');

        // collection
        $collection = $cls->castResponseToType($response, 'collection');
        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame(['foo' => 'bar'], $collection->all());

        // array
        self::assertSame(['foo' => 'bar'], $cls->castResponseToType($response, 'array'));

        // object
        self::assertSame('bar', $cls->castResponseToType($response, 'object')->foo);

        // raw
        $raw = $cls->castResponseToType($response, 'raw');
        self::assertInstanceOf(Response::class, $raw);

        // 2. not exists
        $this->expectException(InvalidConfigException::class);
        $cls->castResponseToType($response, 'Not\Exists\ClassName');
        self::fail('failed to assert castResponseToType should throw an exception.');
    }

    public function testDetectAndCastResponseToType()
    {
        $cls = Mockery::mock(DummyClassForResponseCastable::class);

        // response
        $response = new Response(200, [], '{"foo": "bar"}');
        self::assertInstanceOf(Collection::class, $cls->detectAndCastResponseToType($response, 'collection'));

        // array
        $response = ['foo' => 'bar'];
        self::assertInstanceOf(Collection::class, $cls->detectAndCastResponseToType($response, 'collection'));
        self::assertSame(['foo' => 'bar'], $cls->detectAndCastResponseToType($response, 'collection')->all());

        // object
        $response = json_decode(json_encode(['foo' => 'bar']));
        self::assertSame(['foo' => 'bar'], $cls->detectAndCastResponseToType($response, 'array'));

        // string
        self::assertSame([], $cls->detectAndCastResponseToType('foobar', 'array'));
        self::assertSame('foobar', $cls->detectAndCastResponseToType('foobar', 'raw')->getBody()->getContents());

        // int
        self::assertSame([123], $cls->detectAndCastResponseToType(123, 'array'));
        self::assertSame('123', $cls->detectAndCastResponseToType(123, 'raw')->getBody()->getContents());

        // float
        self::assertSame([123.01], $cls->detectAndCastResponseToType(123.01, 'array'));
        self::assertSame('123.01', $cls->detectAndCastResponseToType(123.01, 'raw')->getBody()->getContents());

        // bool
        self::assertSame([], $cls->detectAndCastResponseToType(false, 'array'));
        self::assertSame('', $cls->detectAndCastResponseToType(false, 'raw')->getBody()->getContents());

        // custom response
        $response = new DummyClassForArrayableCast();
        self::assertSame(['hello' => 'world!'], $cls->detectAndCastResponseToType($response, 'array'));

        // exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported response type "NULL"');
        $cls->detectAndCastResponseToType(null, 'array');
    }
}

class DummyClassForResponseCastable
{
    use ResponseCastable;
}

class DummyClassForArrayableCast implements Arrayable
{
    public function toArray()
    {
        return [
            'hello' => 'world!',
        ];
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}

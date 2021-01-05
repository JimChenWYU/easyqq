<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyQQ\Payment\Notify;

use Closure;
use EasyQQ\Kernel\Exceptions\Exception;
use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Support;
use EasyQQ\Kernel\Support\XML;
use EasyQQ\Payment\Application;
use EasyQQ\Payment\Kernel\Exceptions\InvalidSignException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class Handler
{
    public const SUCCESS = 'SUCCESS';
    public const FAIL = 'FAIL';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $fail;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Check sign.
     * If failed, throws an exception.
     *
     * @var bool
     */
    protected $check = true;

    /**
     * Respond with sign.
     *
     * @var bool
     */
    protected $sign = false;

    /**
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Handle incoming notify.
     *
     * @return Response
     */
    abstract public function handle(Closure $closure);

    public function fail(string $message)
    {
        $this->fail = $message;
    }

    /**
     * @return $this
     */
    public function respondWith(array $attributes, bool $sign = false)
    {
        $this->attributes = $attributes;
        $this->sign = $sign;

        return $this;
    }

    /**
     * Build xml and return the response to WeChat.
     *
     * @throws InvalidArgumentException
     */
    public function toResponse(): Response
    {
        $base = [
            'return_code' => is_null($this->fail) ? static::SUCCESS : static::FAIL,
            'return_msg' => $this->fail,
        ];

        $attributes = array_merge($base, $this->attributes);

        if ($this->sign) {
            $attributes['sign'] = Support\Utils::generateSign($attributes, $this->app->getKey());
        }

        return new Response(XML::build($attributes));
    }

    /**
     * Return the notify message from request.
     *
     * @throws Exception
     */
    public function getMessage(): array
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        try {
            $message = XML::parse((string)$this->app['request']->getContent());
        } catch (Throwable $e) {
            throw new Exception('Invalid request XML: '.$e->getMessage(), 400);
        }

        if (!is_array($message) || empty($message)) {
            throw new Exception('Invalid request XML.', 400);
        }

        if ($this->check) {
            $this->validate($message);
        }

        return $this->message = $message;
    }

    /**
     * Decrypt message.
     *
     * @return string|null
     *
     * @throws Exception
     */
    public function decryptMessage(string $key)
    {
        $message = $this->getMessage();
        if (empty($message[$key])) {
            return null;
        }

        return Support\AES::decrypt(
            base64_decode($message[$key], true),
            md5($this->app['config']->key),
            '',
            OPENSSL_RAW_DATA,
            'AES-256-ECB'
        );
    }

    /**
     * Validate the request params.
     *
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     */
    protected function validate(array $message)
    {
        $sign = $message['sign'];
        unset($message['sign']);

        if (Support\Utils::generateSign($message, $this->app->getKey()) !== $sign) {
            throw new InvalidSignException();
        }
    }

    /**
     * @param mixed $result
     */
    protected function strict($result)
    {
        if (true !== $result && is_null($this->fail)) {
            $this->fail((string)$result);
        }
    }
}

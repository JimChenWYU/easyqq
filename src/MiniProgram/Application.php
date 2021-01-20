<?php

namespace EasyQQ\MiniProgram;

use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\MiniProgram\Auth\AccessToken;
use EasyQQ\MiniProgram\Wxpay\Client;

/**
 * Class Application
 *
 * @author JimChen <imjimchen@163.com>
 *
 * @property AccessToken $access_token
 * @property Auth\Client $auth
 * @property Client      $wxpay
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
        Wxpay\ServiceProvider::class,
    ];

    /**
     * @return string
     *
     * @throws \EasyQQ\Kernel\Exceptions\InvalidArgumentException
     */
    public function getWxKey()
    {
        $key = $this['config']->wechat_key;

        if (empty($key)) {
            throw new InvalidArgumentException('config key should not empty.');
        }

        if (32 !== strlen($key)) {
            throw new InvalidArgumentException(sprintf("'%s' should be 32 chars length.", $key));
        }

        return $key;
    }
}

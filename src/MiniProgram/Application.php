<?php

namespace EasyQQ\MiniProgram;

use EasyQQ\Kernel\ServiceContainer;

/**
 * Class Application
 *
 * @author JimChen <imjimchen@163.com>
 *
 * @property \EasyQQ\MiniProgram\Auth\AccessToken           $access_token
 * @property \EasyQQ\MiniProgram\Auth\Client                $auth
 * @property \EasyQQ\MiniProgram\Wxpay\Client               $wxpay
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
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->base->$method(...$args);
    }
}

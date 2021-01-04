<?php

namespace EasyQQ\MiniProgram;

use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\MiniProgram\Auth\AccessToken;
use EasyQQ\MiniProgram\Auth\Client;

/**
 * Class Application
 *
 * @author JimChen <imjimchen@163.com>
 *
 * @property AccessToken           $access_token
 * @property Client                $auth
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
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

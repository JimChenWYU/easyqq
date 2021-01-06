<?php

namespace EasyQQ\Payment\Jssdk;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @author JimChen <imjimchen@163.com>
 */
class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        !isset($app['jssdk']) && $app['jssdk'] = function ($app) {
            return new Client($app);
        };
    }
}

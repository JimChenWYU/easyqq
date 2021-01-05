<?php

namespace EasyQQ\Payment\Bill;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @author JimChen <imjimchen@163.com>
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        !isset($app['bill']) && $app['bill'] = function ($app) {
            return new Client($app);
        };
    }
}

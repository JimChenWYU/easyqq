<?php

namespace EasyQQ\Payment\Refund;

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
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        !isset($app['refund']) && $app['refund'] = function ($app) {
            return new Client($app);
        };
    }
}

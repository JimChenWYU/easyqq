<?php

namespace EasyQQ\MiniProgram\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @author JimChen <imjimchen@163.com>
 */
class ServiceProvider implements ServiceProviderInterface
{

	public function register(Container $pimple)
	{
		!isset($app['access_token']) && $app['access_token'] = function ($app) {
			return new AccessToken($app);
		};

		!isset($app['auth']) && $app['auth'] = function ($app) {
			return new Client($app);
		};
	}
}

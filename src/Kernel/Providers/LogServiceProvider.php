<?php

namespace EasyQQ\Kernel\Providers;

use EasyQQ\Kernel\Log\LogManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use function sys_get_temp_dir;

/**
 * Class LogServiceProvider
 *
 * @author JimChen <imjimchen@163.com>
 */
class LogServiceProvider implements ServiceProviderInterface
{

	/**
	 * Registers services on the given container.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param Container $pimple A container instance
	 */
	public function register(Container $pimple)
	{
		!isset($pimple['log']) && $pimple['log'] = function ($app) {
			$config = $this->formatLogConfig($app);

			if (!empty($config)) {
				$app->rebind('config', $app['config']->merge($config));
			}

			return new LogManager($app);
		};

		!isset($pimple['logger']) && $pimple['logger'] = $pimple['log'];
	}

	public function formatLogConfig($app)
	{
		if (!empty($app['config']->get('log.channels'))) {
			return $app['config']->get('log');
		}

		if (empty($app['config']->get('log'))) {
			return [
				'log' => [
					'default'  => 'null',
					'channels' => [
						'null' => [
							'driver' => 'null',
						],
					],
				],
			];
		}

		return [
			'log' => [
				'default'  => 'single',
				'channels' => [
					'single' => [
						'driver' => 'single',
						'path'   => $app['config']->get('log.file') ?: sys_get_temp_dir() . '/logs/easyqq.log',
						'level'  => $app['config']->get('log.level', 'debug'),
					],
				],
			],
		];
	}
}

<?php

namespace EasyQQ\MiniProgram\Auth;

use EasyQQ\Kernel\BaseClient;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @author JimChen <imjimchen@163.com>
 */
class Client extends BaseClient
{
	/**
	 * Get session info by code.
	 *
	 * @return ResponseInterface|Collection|array|object|string
	 *
	 * @throws InvalidConfigException
	 */
	public function session(string $code)
	{
		$params = [
			'appid'      => $this->app['config']['app_id'],
			'secret'     => $this->app['config']['secret'],
			'js_code'    => $code,
			'grant_type' => 'authorization_code',
		];

		return $this->httpGet('sns/jscode2session', $params);
	}
}

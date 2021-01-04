<?php

namespace EasyQQ\MiniProgram\Auth;

use EasyQQ\Kernel\AccessToken as BaseAccessToken;

/**
 * Class AccessToken
 *
 * @author JimChen <imjimchen@163.com>
 */
class AccessToken extends BaseAccessToken
{
	/**
	 * @var string
	 */
	protected $endpointToGetToken = 'https://api.q.qq.com/api/getToken';

	protected function getCredentials(): array
	{
		return [
			'grant_type' => 'client_credential',
			'appid'      => $this->app['config']['app_id'],
			'secret'     => $this->app['config']['secret'],
		];
	}
}

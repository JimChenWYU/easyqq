<?php

namespace EasyQQ\Kernel\Events;

use EasyQQ\Kernel\AccessToken;

/**
 * Class AccessTokenRefreshed
 *
 * @author JimChen <imjimchen@163.com>
 */
class AccessTokenRefreshed
{
    /**
     * @var AccessToken
     */
    public $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }
}

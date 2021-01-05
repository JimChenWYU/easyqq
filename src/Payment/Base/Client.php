<?php

namespace EasyQQ\Payment\Base;

use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Payment\Kernel\BaseClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @author JimChen <imjimchen@163.com>
 */
class Client extends BaseClient
{
    /**
     * Pay the order.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function pay(array $params)
    {
        $params['appid'] = $this->app['config']->app_id;

        return $this->request($this->wrap('pay/qpay_micro_pay.cgi'), $params);
    }
}

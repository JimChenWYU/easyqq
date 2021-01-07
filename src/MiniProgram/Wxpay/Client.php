<?php

namespace EasyQQ\MiniProgram\Wxpay;

use EasyQQ\Kernel\BaseClient;
use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Kernel\Support\Utils;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @author JimChen <imjimchen@163.com>
 */
class Client extends BaseClient
{
    use PaymentClient;

    /**
     * @var string
     */
    protected $proxyNotifyUrl = 'https://api.q.qq.com/wxpay/notify';

    /**
     * Unify order.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function unify(array $params)
    {
        if (empty($params['spbill_create_ip'])) {
            $params['spbill_create_ip'] = Utils::getClientIp();
        }

        $params['trade_type'] = 'MWEB'; // h5
        $params['appid'] = $this->app['config']->app_id;
        $realNotifyUrl = $params['notify_url'] ?? $this->app['config']['notify_url'];
        $params['notify_url'] = $this->proxyNotifyUrl;

        $results = $this->accessToken->getToken();
        $query = [
            'appid' => $this->app['config']->app_id,
            'access_token' => $results['access_token'],
            'real_notify_url' => $realNotifyUrl,
        ];

        return $this->paymentRequest('wxpay/unifiedorder', $params, 'post', [
            'query' => $query
        ]);
    }
}

<?php

namespace EasyQQ\Payment\Order;

use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Kernel\Support\Utils;
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
            $params['spbill_create_ip'] = ('NATIVE' === $params['trade_type']) ? Utils::getServerIp() : Utils::getClientIp();
        }

        $params['appid'] = $this->app['config']->app_id;
        $params['notify_url'] = $params['notify_url'] ?? $this->app['config']['notify_url'];

        return $this->request($this->wrap('pay/qpay_unified_order.cgi'), $params);
    }

    /**
     * Query order by out trade number.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public function queryByOutTradeNumber(string $number)
    {
        return $this->query([
            'out_trade_no' => $number,
        ]);
    }

    /**
     * Query order by transaction id.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public function queryByTransactionId(string $transactionId)
    {
        return $this->query([
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function query(array $params)
    {
        $params['appid'] = $this->app['config']->app_id;

        return $this->request($this->wrap('pay/qpay_order_query.cgi'), $params);
    }

    /**
     * Close order by out_trade_no.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function close(string $tradeNo)
    {
        $params = [
            'appid' => $this->app['config']->app_id,
            'out_trade_no' => $tradeNo,
        ];

        return $this->request($this->wrap('pay/qpay_close_order.cgi'), $params);
    }
}

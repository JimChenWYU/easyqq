<?php

namespace EasyQQ\Payment\Refund;

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
     * Refund by out trade number.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     */
    public function byOutTradeNumber(string $number, string $refundNumber, int $totalFee, int $refundFee, array $optional = [])
    {
        return $this->refund($refundNumber, $totalFee, $refundFee, array_merge($optional, ['out_trade_no' => $number]));
    }

    /**
     * Refund by transaction id.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     */
    public function byTransactionId(string $transactionId, string $refundNumber, int $totalFee, int $refundFee, array $optional = [])
    {
        return $this->refund($refundNumber, $totalFee, $refundFee, array_merge($optional, ['transaction_id' => $transactionId]));
    }

    /**
     * Query refund by transaction id.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     */
    public function queryByTransactionId(string $transactionId)
    {
        return $this->query($transactionId, 'transaction_id');
    }

    /**
     * Query refund by out trade number.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     */
    public function queryByOutTradeNumber(string $outTradeNumber)
    {
        return $this->query($outTradeNumber, 'out_trade_no');
    }

    /**
     * Query refund by out refund number.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     */
    public function queryByOutRefundNumber(string $outRefundNumber)
    {
        return $this->query($outRefundNumber, 'out_refund_no');
    }

    /**
     * Query refund by refund id.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     */
    public function queryByRefundId(string $refundId)
    {
        return $this->query($refundId, 'refund_id');
    }

    /**
     * Refund.
     *
     * @param array $optional
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function refund(string $refundNumber, int $totalFee, int $refundFee, $optional = [])
    {
        $params = array_merge([
            'out_refund_no' => $refundNumber,
            'total_fee' => $totalFee,
            'refund_fee' => $refundFee,
            'appid' => $this->app['config']->app_id,
        ], $optional);

        return $this->safeRequest($this->wrap('pay/qpay_refund.cgi'), $params);
    }

    /**
     * Query refund.
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function query(string $number, string $type)
    {
        $params = [
            'appid' => $this->app['config']->app_id,
            $type => $number,
        ];

        return $this->request($this->wrap('pay/qpay_refund_query.cgi'), $params);
    }
}

<?php

namespace EasyQQ\Tests\Payment\Refund;

use EasyQQ\Payment\Application;
use EasyQQ\Payment\Refund\Client;
use EasyQQ\Tests\TestCase;

class ClientTest extends TestCase
{
    public function getApp()
    {
        return new Application(['app_id' => 'wx123456', 'key' => '88888888888888888888888888888888']);
    }

    public function testByOutTradeNumber()
    {
        $client = $this->mockApiClient(Client::class, ['safeRequest'], $this->getApp())->makePartial();

        $orderNo = 'foo';
        $refundNo = 'bar';
        $totalFee = 1;
        $refundFee = 1;
        $optional = ['foo' => 'bar'];

        $params = array_merge([
            'out_trade_no' => $orderNo,
            'out_refund_no' => $refundNo,
            'total_fee' => $totalFee,
            'refund_fee' => $totalFee,
            'appid' => 'wx123456',
        ], $optional);

        $client->expects()->safeRequest('pay/qpay_refund.cgi', $params)->andReturn('mock-result');

        self::assertSame('mock-result', $client->byOutTradeNumber($orderNo, $refundNo, $totalFee, $refundFee, $optional));
    }

    public function testByTransactionId()
    {
        $client = $this->mockApiClient(Client::class, ['safeRequest'], $this->getApp())->makePartial();

        $orderNo = 'foo';
        $refundNo = 'bar';
        $totalFee = 1;
        $refundFee = 1;
        $optional = ['foo' => 'bar'];

        $params = array_merge([
            'transaction_id' => $orderNo,
            'out_refund_no' => $refundNo,
            'total_fee' => $totalFee,
            'refund_fee' => $totalFee,
            'appid' => 'wx123456',
        ], $optional);

        $client->expects()->safeRequest('pay/qpay_refund.cgi', $params)->andReturn('mock-result');

        self::assertSame('mock-result', $client->byTransactionId($orderNo, $refundNo, $totalFee, $refundFee, $optional));
    }

    public function testQueryByTransactionId()
    {
        $client = $this->mockApiClient(Client::class, ['request'], $this->getApp())->makePartial();

        $client->expects()->request('pay/qpay_refund_query.cgi', [
            'transaction_id' => 'foobar',
            'appid' => 'wx123456',
        ])->andReturn('mock-result');

        self::assertSame('mock-result', $client->queryByTransactionId('foobar'));
    }

    public function testQueryByOutTradeNumber()
    {
        $client = $this->mockApiClient(Client::class, ['request'], $this->getApp())->makePartial();

        $client->expects()->request('pay/qpay_refund_query.cgi', [
            'out_trade_no' => 'foobar',
            'appid' => 'wx123456',
        ])->andReturn('mock-result');

        self::assertSame('mock-result', $client->queryByOutTradeNumber('foobar'));
    }

    public function testQueryByOutRefundNumber()
    {
        $client = $this->mockApiClient(Client::class, ['request'], $this->getApp())->makePartial();

        $client->expects()->request('pay/qpay_refund_query.cgi', [
            'out_refund_no' => 'foobar',
            'appid' => 'wx123456',
        ])->andReturn('mock-result');

        self::assertSame('mock-result', $client->queryByOutRefundNumber('foobar'));
    }

    public function testQueryByRefundId()
    {
        $client = $this->mockApiClient(Client::class, ['request'], $this->getApp())->makePartial();

        $client->expects()->request('pay/qpay_refund_query.cgi', [
            'refund_id' => 'foobar',
            'appid' => 'wx123456',
        ])->andReturn('mock-result');

        self::assertSame('mock-result', $client->queryByRefundId('foobar'));
    }
}

<?php

namespace EasyQQ\Tests\Payment\Bill;

use EasyQQ\Kernel\Http\Response;
use EasyQQ\Kernel\Http\StreamResponse;
use EasyQQ\Payment\Application;
use EasyQQ\Payment\Bill\Client;
use EasyQQ\Tests\TestCase;

class ClientTest extends TestCase
{
    public function testGet()
    {
        $app = new Application([
            'app_id' => 'mock-appid',
        ]);

        $client = $this->mockApiClient(Client::class, ['download'], $app)->makePartial();

        $params = [
            'appid' => 'mock-appid',
            'bill_date' => 20171010,
            'bill_type' => 'ALL',
        ];
        // stream response
        $client->expects()->requestRaw('sp_download/qpay_mch_statement_down.cgi', $params)->andReturn(new Response(200, ['text/plain'], 'mock-content'));
        self::assertInstanceOf(StreamResponse::class, $client->get('20171010'));

        $response = new Response(200, ['Content-Type' => ['text/plain']], '<xml><return_code><![CDATA[FAIL]]></return_code>
<return_msg><![CDATA[invalid bill_date]]></return_msg>
<error_code><![CDATA[20001]]></error_code>
</xml>');
        $client->expects()->requestRaw('sp_download/qpay_mch_statement_down.cgi', $params)->andReturn($response);

        $result = $client->get('20171010');
        self::assertArraySubset([
            'return_code' => 'FAIL',
            'return_msg' => 'invalid bill_date',
            'error_code' => 20001,
        ], $result);
    }
}

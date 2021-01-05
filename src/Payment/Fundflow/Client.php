<?php

namespace EasyQQ\Payment\Fundflow;

use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Http\Response;
use EasyQQ\Kernel\Http\StreamResponse;
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
     * Download fundflow history as a table file.
     *
     * @param array $options
     *
     * @return array|Response|Collection|object|ResponseInterface|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function get(string $date, string $type = 'CASH', $options = [])
    {
        $params = [
            'bill_date' => $date,
            'acc_type' => $type,
            'nonce_str' => uniqid('micro', false),
        ];
        $response = $this->requestRaw('sp_download/qpay_mch_acc_roll.cgi', $params, 'post', $options);

        if (0 === strpos($response->getBody()->getContents(), '<xml>')) {
            return $this->castResponseToType($response, $this->app['config']->get('response_type'));
        }

        return StreamResponse::buildFromPsrResponse($response);
    }
}

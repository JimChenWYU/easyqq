<?php

namespace EasyQQ\Payment\Bill;

use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
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
     * Download bill history as a table file.
     *
     * @return StreamResponse|ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function get(string $date, string $type = 'ALL', array $optional = [])
    {
        $params = [
                'appid' => $this->app['config']->app_id,
                'bill_date' => $date,
                'bill_type' => $type,
            ] + $optional;

        $response = $this->requestRaw($this->wrap('sp_download/qpay_mch_statement_down.cgi'), $params);

        if (0 === strpos($response->getBody()->getContents(), '<xml>')) {
            return $this->castResponseToType($response, $this->app['config']->get('response_type'));
        }

        return StreamResponse::buildFromPsrResponse($response);
    }
}

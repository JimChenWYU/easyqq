<?php

namespace EasyQQ\MiniProgram\Wxpay;

use EasyQQ\Kernel\BaseClient;
use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Arr;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Kernel\Support\Utils;
use EasyQQ\Kernel\Support\XML;
use EasyQQ\MiniProgram\Application;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait PaymentClient
 *
 * @mixin BaseClient
 * @property-read Application $app
 */
trait PaymentClient
{
    /**
     * Extra request params.
     *
     * @return array
     */
    protected function prepends()
    {
        return [];
    }

    /**
     * Make a API request.
     *
     * @param string $method
     * @param bool   $returnResponse
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    protected function paymentRequest(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
    {
        $base = [
            'mch_id' => $this->app['config']['mch_id'],
            'nonce_str' => uniqid('', false),
        ];

        $params = array_filter(array_merge($base, $this->prepends(), $params), 'strlen');

        $secretKey = $this->app->getKey();

        $encryptMethod = Utils::getEncryptMethod(Arr::get($params, 'sign_type', 'MD5'), $secretKey);

        $params['sign'] = Utils::generateSign($params, $secretKey, $encryptMethod);

        $options = array_merge([
            'body' => XML::build($params),
        ], $options);

        $this->pushMiddleware($this->logMiddleware(), 'log');

        $response = $this->performRequest($endpoint, $method, $options);

        return $returnResponse ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * Log the request.
     *
     * @return callable
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter);
    }
}

<?php

namespace EasyQQ\Payment\Kernel;

use Closure;
use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Arr;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Kernel\Support\Utils;
use EasyQQ\Kernel\Support\XML;
use EasyQQ\Kernel\Traits\HasHttpRequests;
use EasyQQ\Payment\Application;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BaseClient
 *
 * @author JimChen <imjimchen@163.com>
 */
class BaseClient
{
    use HasHttpRequests { request as performRequest; }

    /**
     * @var Application
     */
    protected $app;

    /**
     * Constructor.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->setHttpClient($this->app['http_client']);
    }

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
    protected function request(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
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
     * @return Closure
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter);
    }

    /**
     * Make a request and return raw response.
     *
     * @param string $method
     *
     * @return ResponseInterface
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    protected function requestRaw(string $endpoint, array $params = [], $method = 'post', array $options = [])
    {
        /** @var ResponseInterface $response */
        $response = $this->request($endpoint, $params, $method, $options, true);

        return $response;
    }

    /**
     * Make a request and return an array.
     *
     * @param string $method
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function requestArray(string $endpoint, array $params = [], $method = 'post', array $options = []): array
    {
        $response = $this->requestRaw($endpoint, $params, $method, $options);

        return $this->castResponseToType($response, 'array');
    }

    /**
     * Request with SSL.
     *
     * @param string $endpoint
     * @param string $method
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    protected function safeRequest($endpoint, array $params, $method = 'post', array $options = [])
    {
        $options = array_merge([
            'cert' => $this->app['config']->get('cert_path'),
            'ssl_key' => $this->app['config']->get('key_path'),
        ], $options);

        return $this->request($endpoint, $params, $method, $options);
    }

    /**
     * Wrapping an API endpoint.
     */
    protected function wrap(string $endpoint): string
    {
        return $endpoint;
    }
}

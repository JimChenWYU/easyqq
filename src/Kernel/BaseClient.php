<?php

namespace EasyQQ\Kernel;

use Closure;
use EasyQQ\Kernel\Exceptions\InvalidConfigException;
use EasyQQ\Kernel\Support\Collection;
use EasyQQ\Kernel\Traits\HasHttpRequests;
use EasyQQ\Kernel\Contracts\AccessTokenInterface;
use EasyQQ\Kernel\Http\Response;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;

/**
 * Class BaseClient
 *
 * @author JimChen <imjimchen@163.com>
 */
class BaseClient
{
	use HasHttpRequests {
		request as performRequest;
	}

	/**
	 * @var ServiceContainer
	 */
	protected $app;

	/**
	 * @var AccessTokenInterface
	 */
	protected $accessToken;

	/**
	 * @var string
	 */
	protected $baseUri;

	/**
	 * BaseClient constructor.
	 *
	 * @param ServiceContainer $app
	 */
	public function __construct(ServiceContainer $app, AccessTokenInterface $accessToken = null)
	{
		$this->app = $app;
		$this->accessToken = $accessToken ?? $this->app['access_token'];
	}

	/**
	 * GET request.
	 *
	 * @return ResponseInterface|Collection|array|object|string
	 *
	 * @throws InvalidConfigException
	 * @throws GuzzleException
	 */
	public function httpGet(string $url, array $query = [])
	{
		return $this->request($url, 'GET', ['query' => $query]);
	}

	/**
	 * POST request.
	 *
	 * @return ResponseInterface|Collection|array|object|string
	 *
	 * @throws InvalidConfigException
	 * @throws GuzzleException
	 */
	public function httpPost(string $url, array $data = [])
	{
		return $this->request($url, 'POST', ['form_params' => $data]);
	}

	/**
	 * JSON request.
	 *
	 * @return ResponseInterface|Collection|array|object|string
	 *
	 * @throws InvalidConfigException
	 * @throws GuzzleException
	 */
	public function httpPostJson(string $url, array $data = [], array $query = [])
	{
		return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
	}

	/**
	 * Upload file.
	 *
	 * @return ResponseInterface|Collection|array|object|string
	 *
	 * @throws InvalidConfigException
	 * @throws GuzzleException
	 */
	public function httpUpload(string $url, array $files = [], array $form = [], array $query = [])
	{
		$multipart = [];

		foreach ($files as $name => $path) {
			$multipart[] = [
				'name'     => $name,
				'contents' => fopen($path, 'r'),
			];
		}

		foreach ($form as $name => $contents) {
			$multipart[] = compact('name', 'contents');
		}

		return $this->request($url, 'POST', [
			'query'           => $query,
			'multipart'       => $multipart,
			'connect_timeout' => 30,
			'timeout'         => 30,
			'read_timeout'    => 30,
		]);
	}

	public function getAccessToken(): AccessTokenInterface
	{
		return $this->accessToken;
	}

	/**
	 * @return BaseClient
	 */
	public function setAccessToken(AccessTokenInterface $accessToken)
	{
		$this->accessToken = $accessToken;

		return $this;
	}

	/**
	 * @param bool $returnRaw
	 *
	 * @return ResponseInterface|Collection|array|object|string
	 *
	 * @throws InvalidConfigException
	 * @throws GuzzleException
	 */
	public function request(string $url, string $method = 'GET', array $options = [], $returnRaw = false)
	{
		if (empty($this->middlewares)) {
			$this->registerHttpMiddlewares();
		}

		$response = $this->performRequest($url, $method, $options);

		$this->app->events->dispatch(new Events\HttpResponseCreated($response));

		return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
	}

	/**
	 * @return Response
	 *
	 * @throws InvalidConfigException
	 * @throws GuzzleException
	 */
	public function requestRaw(string $url, string $method = 'GET', array $options = [])
	{
		return Response::buildFromPsrResponse($this->request($url, $method, $options, true));
	}

	/**
	 * Register Guzzle middlewares.
	 */
	protected function registerHttpMiddlewares()
	{
		// retry
		$this->pushMiddleware($this->retryMiddleware(), 'retry');
		// access token
		$this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');
		// log
		$this->pushMiddleware($this->logMiddleware(), 'log');
	}

	/**
	 * Attache access token to request query.
	 *
	 * @return Closure
	 */
	protected function accessTokenMiddleware()
	{
		return function (callable $handler) {
			return function (RequestInterface $request, array $options) use ($handler) {
				if ($this->accessToken) {
					$request = $this->accessToken->applyToRequest($request, $options);
				}

				return $handler($request, $options);
			};
		};
	}

	/**
	 * Log the request.
	 *
	 * @return Closure
	 */
	protected function logMiddleware()
	{
		$formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

		return Middleware::log($this->app['logger'], $formatter, LogLevel::DEBUG);
	}

	/**
	 * Return retry middleware.
	 *
	 * @return Closure
	 */
	protected function retryMiddleware()
	{
		return Middleware::retry(function (
			$retries,
			RequestInterface $request,
			ResponseInterface $response = null
		) {
			// Limit the number of retries to 2
			if ($retries < $this->app->config->get('http.max_retries',
					1) && $response && $body = $response->getBody()) {
				// Retry on server errors
				$response = json_decode($body, true);

				if (!empty($response['errcode']) && in_array(abs($response['errcode']), [40001, 40014, 42001], true)) {
					$this->accessToken->refresh();
					$this->app['logger']->debug('Retrying with refreshed access token.');

					return true;
				}
			}

			return false;
		}, function () {
			return abs($this->app->config->get('http.retry_delay', 500));
		});
	}
}

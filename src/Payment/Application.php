<?php

namespace EasyQQ\Payment;

use Closure;
use EasyQQ\Kernel\Exceptions\Exception;
use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\Payment\Refund\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Application
 *
 * @author JimChen <imjimchen@163.com>
 *
 * @property Bill\Client     $bill
 * @property Fundflow\Client $fundflow
 * @property Jssdk\Client    $jssdk
 * @property Order\Client    $order
 * @property Client          $refund
 *
 * @method mixed pay(array $attributes)
 */
class Application extends ServiceContainer
{
    protected $providers = [
        Base\ServiceProvider::class,
        Order\ServiceProvider::class,
        Refund\ServiceProvider::class,
        Jssdk\ServiceProvider::class,
        Bill\ServiceProvider::class,
        Fundflow\ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $defaultConfig = [
        'http' => [
            'base_uri' => 'https://qpay.qq.com/cgi-bin/',
        ],
    ];

    /**
     * @return Response
     *
     * @codeCoverageIgnore
     *
     * @throws Exception
     */
    public function handlePaidNotify(Closure $closure)
    {
        return (new Notify\Paid($this))->handle($closure);
    }

    /**
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function getKey()
    {
        $key = $this['config']->key;

        if (empty($key)) {
            throw new InvalidArgumentException('config key should not empty.');
        }

        if (32 !== strlen($key)) {
            throw new InvalidArgumentException(sprintf("'%s' should be 32 chars length.", $key));
        }

        return $key;
    }

    /**
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->base->$method(...$args);
    }
}

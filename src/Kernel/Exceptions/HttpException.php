<?php

namespace EasyQQ\Kernel\Exceptions;

use EasyWeChat\Kernel\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpException
 *
 * @author JimChen <imjimchen@163.com>
 */
class HttpException extends Exception
{
    /**
     * @var ResponseInterface|null
     */
    public $response;

    /**
     * @var ResponseInterface|Collection|array|object|string|null
     */
    public $formattedResponse;

    /**
     * HttpException constructor.
     *
     * @param string   $message
     * @param null     $formattedResponse
     * @param int|null $code
     */
    public function __construct($message, ResponseInterface $response = null, $formattedResponse = null, $code = null)
    {
        parent::__construct($message, $code);

        $this->response = $response;
        $this->formattedResponse = $formattedResponse;

        if ($response) {
            $response->getBody()->rewind();
        }
    }
}

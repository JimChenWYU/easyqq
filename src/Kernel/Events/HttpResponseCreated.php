<?php

namespace EasyQQ\Kernel\Events;

use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpResponseCreated
 *
 * @author JimChen <imjimchen@163.com>
 */
class HttpResponseCreated
{
    /**
     * @var ResponseInterface
     */
    public $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
}

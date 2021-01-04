<?php

namespace EasyQQ\Kernel\Contracts;

use Psr\Http\Message\RequestInterface;

/**
 * Class AccessTokenInterface
 *
 * @author JimChen <imjimchen@163.com>
 */
interface AccessTokenInterface
{
    public function getToken(): array;

    /**
     * @return AccessTokenInterface
     */
    public function refresh(): self;

    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface;
}

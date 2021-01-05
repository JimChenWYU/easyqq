<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyQQ\Payment\Notify;

use Closure;
use EasyQQ\Kernel\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;
use function call_user_func;

class Paid extends Handler
{
    /**
     * @return Response
     *
     * @throws Exception
     */
    public function handle(Closure $closure)
    {
        $this->strict(
            call_user_func($closure, $this->getMessage(), [$this, 'fail'])
        );

        return $this->toResponse();
    }
}

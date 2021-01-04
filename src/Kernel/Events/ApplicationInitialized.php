<?php

namespace EasyQQ\Kernel\Events;

use EasyQQ\Kernel\ServiceContainer;

/**
 * Class ApplicationInitialized
 *
 * @author JimChen <imjimchen@163.com>
 */
class ApplicationInitialized
{
    /**
     * @var ServiceContainer
     */
    public $app;

    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }
}

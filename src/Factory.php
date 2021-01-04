<?php

namespace EasyQQ;

use EasyQQ\Kernel\ServiceContainer;
use EasyQQ\MiniProgram\Application;

/**
 * Class Factory
 *
 * @author JimChen <imjimchen@163.com>
 *
 * @method static Application        miniProgram(array $config)
 */
class Factory
{
    /**
     * @param string $name
     *
     * @return ServiceContainer
     */
    public static function make($name, array $config)
    {
        $namespace = Kernel\Support\Str::studly($name);
        $application = "\\EasyQQ\\{$namespace}\\Application";

        return new $application($config);
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}

<?php

namespace EasyQQ\Tests;

use EasyQQ\Factory;
use EasyQQ\MiniProgram\Application;

/**
 * Class FactoryTest
 *
 * @author JimChen <imjimchen@163.com>
 */
class FactoryTest extends TestCase
{
    public function testStaticCall()
    {
        self::assertInstanceOf(
            Application::class,
            Factory::miniProgram(['appid' => 'appid@789'])
        );
    }
}

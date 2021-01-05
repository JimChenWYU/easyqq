<?php

namespace EasyQQ\Tests\Payment\Fundflow;

use EasyQQ\Tests\TestCase;

class ClientTest extends TestCase
{
    public function testGet()
    {
        //单元测试未完成
        //检测返回结果
        self::assertArraySubset([
             'return_code' => 'FAIL',
             'return_msg' => 'invalid bill_date',
             'error_code' => 20001,
         ], [
            'return_code' => 'FAIL',
            'return_msg' => 'invalid bill_date',
            'error_code' => 20001,
        ]);
    }
}

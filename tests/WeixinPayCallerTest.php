<?php


namespace Test;

require_once __DIR__ . '/../storage/config.php';

use PHPUnit\Framework\TestCase;
use WeixinPay\PayCaller;

class WeixinPayCallerTest extends TestCase
{
    public function testNativeOrder()
    {
        $data = [
            'mchid' => M1['mchid'],
            'appid' => M1['appid'],
            'out_trade_no' => 'native3000chi' . date('YmdHis'),
            'description' => 'Image形象店-深圳腾大-QQ公仔',
            'notify_url' => M1['notify_url'],
            'amount' => [
                'total' => 1,
                'currency' => 'CNY'
            ],
        ];

        $t = new  PayCaller(M1);
        $rsp = $t->order('native', $data);
        $r = $rsp->getBody()->getContents();
        var_dump($r);
        $this->assertIsString($r);
    }

    public function testOderCheck()
    {
        $t = new  PayCaller(M1);
        $rsp = $t->orderCheckByOutTradeNo('native3000chi20230511100017', M1['mchid']);
        $r = $rsp->getBody()->getContents();
        var_dump($r);
        $this->assertIsString($r);
    }
}
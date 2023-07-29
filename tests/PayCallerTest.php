<?php


namespace Test;

require_once __DIR__ . '/../storage/config.php';

use PHPUnit\Framework\TestCase;
use ReqTencent\Weixin\Pay\PayCaller;


class PayCallerTest extends TestCase
{
    public function testJsapiOrder()
    {
        //o1U7r6d35h6GH-Ca9freZPg-BsCM
        $data = [
            "mchid" => M2['mchid'],
            "out_trade_no" => 'test' . date('YmdHis'),
            "appid" => M2['appid'],
            "description" => "Image形象店-深圳腾大-QQ公仔",
            "notify_url" => "http://zhsz.hy5188.com/pay/notify",
            "amount" => [
                "total" => 1,
                "currency" => "CNY"
            ],
            "payer" => [
                "openid" => "o1U7r6d35h6GH-Ca9freZPg-BsCM"
            ]
        ];

        $t = new  PayCaller(M2);
        $rsp = $t->order('jsapi', $data);
        $r = $rsp->getBody()->getContents();
        var_dump($r);
        $this->assertIsString("");
    }

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
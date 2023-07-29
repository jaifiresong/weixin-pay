<?php


namespace Test;


require_once __DIR__ . '/../storage/config.php';


use PHPUnit\Framework\TestCase;
use ReqTencent\Weixin\Pay\PayNotify;


class PayNotifyTest extends TestCase
{
    public function test()
    {
        $h = '{"Wechatpay-Timestamp":"1683770441","Pragma":"no-cache","Wechatpay-Signature-Type":"WECHATPAY2-SHA256-RSA2048","Wechatpay-Signature":"f271qZDCX4IB3Tm9V73JN7KMJVCYcZlL36eQ8I4lA+fw4NXYlnybR17b1ykR+OnV5mGty0djiMnpfsncrr\/v3zQS\/otWoOnJtHPkZP3MqotJoy+t9G3+eB+Jglu7ExR9E4o+LCLBFUx69tGkZDzx2c9HhV\/ewxiKsSV64D6IqriVF5qE05IeP\/xz9xZe6B83N85cCq0z2Q2VvXs7Ze\/bPUCWHFfUXURd\/WBND1wlNQwJI09C81mpuwNoQEKFLcHzGRJ3Puuusm\/dTCNPvQTC\/qDn\/nI5LtwMRbDCV0NFYcbfO6q1vmkz\/hp\/VDlSiKn54K4\/iGTKZcXvpGQhHsbT0w==","Wechatpay-Serial":"21BC5C0E19CF953DA421E5275B1AB7741FBF465C","Content-Type":"application\/json","Wechatpay-Nonce":"mDML06DFZQOU5GPcLfVlFUy3y4h9QMB0","Accept":"*\/*","User-Agent":"Mozilla\/4.0","Content-Length":"919","Connection":"close","X-Forwarded-For":"101.226.103.14","Host":"sdw.3000chi.cn","Remoteip":"101.226.103.14"}';
        $b = '{"id":"db7c08f8-be04-5c9b-b022-ba3d384ed1c9","create_time":"2023-05-11T10:00:40+08:00","resource_type":"encrypt-resource","event_type":"TRANSACTION.SUCCESS","summary":"支付成功","resource":{"original_type":"transaction","algorithm":"AEAD_AES_256_GCM","ciphertext":"itM3ZgJflzCJF+BNsNoThKnm8I/f/aWjKosMxtUUqiP/u9K+Xo02LYCwQIoH5ZGmXHSrFqrP5rThbSPbhpTG25M2LIumbY1yEz53uWn93Qd5A68yipAhy2jk9CM3U6oJ8riIopK2kdquHIiA+iSRrrRMQzhepgKfPfSM2XykrQ8gVHIs23il6cFLDFuW+/WxKWuZ+Ac0aF5qxJKSX2YQ8Pk+wd5H74VZNydpkc+g6LQ8tkl+xF854v+67KYuyoOOIiZaz2frRSmuFodVSW4XG/qV9pIPGEdxcc+MQC/0QDuS3CV5YY5C2TRADJeyLZVY7L6Y4L5yI+W3JV1kM78THwdNUKv4DJmJuPG9gdnTSH41+TGj90nyhk3WKBM1qEY2zVQzJbLTKLCqcn5WknNjjDB29zhvvm4ewxQKSmGzL64/7EZRXG6Hi4iOuXZrBAv0MqLKsntOLxaoSvYPAw1LXNB3yoqp7JY5J22Yqsc5Sq15h1V4Vr5hROjPbUkL1iep4y26UGhKUcX3lMovYsQJNdK+uYIKefR5nN/KZ4SP3VICmt1X8pQOrI+6f4KbXvB5zqp85tW10CrTvsk=","associated_data":"transaction","nonce":"gyYsJaQIiZ9x"}}';
        $t = new PayNotify(M1);
        $r = $t->receiveAndDecrypt(json_decode($h, true), $b);
        var_dump($r);
        $this->assertIsString("");
    }
}
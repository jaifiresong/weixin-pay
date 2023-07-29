<?php


namespace ReqTencent\Weixin\Pay;


use ReqTencent\Weixin\Pay\Support\Decipher;

class PayNotify
{
    private $decipher;

    public function __construct($conf)
    {
        $this->decipher = Decipher::summon($conf['v3Key'], $conf['wechatpay_certificate_path']);
    }

    //回调数据接收
    public function receiveAndDecrypt(array $headers = null, string $body = null)
    {
        if (is_null($headers)) {
            $headers = getallheaders();
        }
        if (is_null($body)) {
            $body = file_get_contents('php://input');
        }

        if (!$this->decipher->signature_verify($headers, $body)) {
            //签名验证失败
            throw new \RuntimeException('签名验证失败');
        }

        // 允许5分钟之内的偏移
        if (time() - $headers['Wechatpay-Timestamp'] > 300) {
            //超时
        }

        //解密
        $arr = (array)json_decode($body, true);
        [
            'resource' => [
                'ciphertext' => $ciphertext,
                'nonce' => $nonce,
                'associated_data' => $associated_data
            ]
        ] = $arr;

        return $this->decipher->decrypt($ciphertext, $nonce, $associated_data);
    }
}
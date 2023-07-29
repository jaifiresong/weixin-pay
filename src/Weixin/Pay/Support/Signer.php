<?php


namespace ReqTencent\Weixin\Pay\Support;


class Signer
{
    private static $instances;

    /**
     * @param string $merchantId 商户号
     * @param string $merchantSerialNumber 商户API证书序列号
     * @param string $merchantPrivateKey 商户私钥证书全路径
     * @return Signer
     */
    public static function summon(string $merchantId, string $merchantSerialNumber, string $merchantPrivateKey): Signer
    {
        if (!isset(self::$instances[$merchantId])) {
            self::$instances[$merchantId] = new self(
                $merchantId,
                $merchantSerialNumber,
                $merchantPrivateKey
            );
        }
        return self::$instances[$merchantId];
    }

    // 商户相关配置
    private $merchantId; // 商户号
    private $merchantSerialNumber; // 商户API证书序列号
    private $merchantPrivateKey; // 商户私钥证书

    private function __construct($merchantId, $merchantSerialNumber, $merchantPrivateKey)
    {
        $this->merchantId = $merchantId;
        $this->merchantSerialNumber = $merchantSerialNumber;
        $this->merchantPrivateKey = file_get_contents($merchantPrivateKey);
    }

    /**
     * 签名
     * @param string $message 构造的签名串
     * @param string $nonce 随机字符串长度不超过32
     * @param string $timestamp 时间戳
     * @return string
     */
    public function sign($message, $timestamp, $nonce)
    {
        $sign = $this->sha256WithRSA($message);
        $token = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->merchantId, $nonce, $timestamp, $this->merchantSerialNumber, $sign
        );
        return 'WECHATPAY2-SHA256-RSA2048 ' . $token;
    }

    public function sha256WithRSA($message)
    {
        $binary_signature = "";
        openssl_sign(
            $message,
            $binary_signature,
            $this->merchantPrivateKey,
            'sha256WithRSAEncryption'
        );
        return base64_encode($binary_signature);
    }
}
<?php

namespace WeixinPay\Support;


class Decipher
{
    private static $instances;

    /**
     * @param string $v3Key //在商户平台上设置的APIv3密钥
     * @param string $wechatpayCertificate 微信支付平台证书全路径
     * @return Decipher|mixed
     */
    public static function summon($v3Key, string $wechatpayCertificate)
    {
        if (!isset(self::$instances[$v3Key])) {
            self::$instances[$v3Key] = new self($v3Key, $wechatpayCertificate);
        }
        return self::$instances[$v3Key];
    }

    private $v3Key;  // 在商户平台上设置的APIv3密钥
    private $wechatpayCertificate;  // 读取的平台证书内容

    private function __construct($v3Key, $wechatpayCertificate)
    {
        $this->v3Key = $v3Key;
        $this->wechatpayCertificate = file_get_contents($wechatpayCertificate);
    }

    public function signature_verify(array $headers, string $body): bool
    {
        //检查平台证书序列号
        //微信支付的平台证书序列号位于HTTP头Wechatpay-Serial。
        //验证签名前，请商户先检查序列号是否跟商户当前所持有的 微信支付平台证书的序列号一致。
        //如果不一致，请重新获取证书。否则，签名的私钥和证书不匹配，将无法成功验证签名。
        $sn = $headers['Wechatpay-Serial'] ?? '';  //平台证书序列号

        //构造验签名串
        $time = $headers['Wechatpay-Timestamp'] ?? '';
        $nonce = $headers['Wechatpay-Nonce'] ?? '';
        /*
        应答时间戳\n
        应答随机串\n
        应答报文主体\n
        */
        $message = $time . "\n" . $nonce . "\n" . $body . "\n";

        //对 Wechatpay-Signature 的字段值使用Base64进行解码，得到应答签名
        $signature = base64_decode($headers['Wechatpay-Signature'] ?? '');
        $result = openssl_verify($message, $signature, $this->wechatpayCertificate, OPENSSL_ALGO_SHA256);
        return 1 === $result;
    }


    const KEY_LENGTH_BYTE = 32;  //ApiV3Key，长度应为32个字节
    const AUTH_TAG_LENGTH_BYTE = 16;

    /**
     * Decrypt AEAD_AES_256_GCM ciphertext
     *
     * @param string $ciphertext AES GCM cipher text
     * @param string $nonce
     * @param string $associated_data AES GCM additional authentication data
     * @return string
     * @throws \RuntimeException
     */
    public function decrypt(string $ciphertext, string $nonce, string $associated_data): string
    {
        if (strlen($this->v3Key) !== self::KEY_LENGTH_BYTE) {
            throw new \RuntimeException('无效的 ApiV3Key');
        }

        $ciphertext = base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            throw new \RuntimeException('无效的加密文本');
        }

        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $this->v3Key);
        }

        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $this->v3Key);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);
            return \openssl_decrypt($ctext, 'aes-256-gcm', $this->v3Key, \OPENSSL_RAW_DATA, $nonce, $authTag, $associated_data);
        }
        throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }
}
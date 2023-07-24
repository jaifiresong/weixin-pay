# weixin-pay

# 下载平台证书参考
https://github.com/wechatpay-apiv3/wechatpay-php/blob/main/bin/README.md


# 配置参考
```
const M1 = [
    'appid' => '公众号或小程序的APPID',
    'mchid' => '商户ID',
    'v3Key' => '证书KEY',
    'sn' => '证书编号',
    'notify_url' => '支付回调通知地址',
    'private_key_path' => __DIR__ . '/证书私钥/apiclient_key.pem',
    'wechatpay_certificate_path' => __DIR__ . '/平台证书.pem',
];
```
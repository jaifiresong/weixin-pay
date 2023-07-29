<?php


namespace ReqTencent\Weixin\Pay;


use Psr\Http\Message\ResponseInterface;
use ReqTencent\Weixin\Pay\Support\HttpCli;
use ReqTencent\Weixin\Pay\Support\Signer;

/**
 * 产品能力概览
 * https://pay.weixin.qq.com/wiki/doc/apiv3/index.shtml
 */
class PayCaller
{
    private $signer;
    private $cli;
    private $conf;

    public function __construct($conf)
    {
        $this->signer = Signer::summon($conf['mchid'], $conf['sn'], $conf['private_key_path']);
        $this->cli = HttpCli::summon($this->signer);
        $this->conf = $conf;
    }

    /**
     * 下单
     * @param $way
     * @param $parameter
     * @return ResponseInterface
     */
    public function order($way, $parameter): ResponseInterface
    {
        $_ = [
            'jsapi' => '/v3/pay/transactions/jsapi',
            'native' => '/v3/pay/transactions/native',
        ];
        return $this->cli->post($_[$way], ['json' => $parameter]);
    }

    /**
     * 查询订单
     * 根据微信支付订单号查询
     * @param $transaction_id
     * @param $mchid
     * @return ResponseInterface
     */
    public function orderCheckByTransactionId($transaction_id, $mchid): ResponseInterface
    {
        return $this->cli->get("/v3/pay/transactions/id/$transaction_id?mchid=$mchid");
    }

    /**
     * 查询订单
     * 根据商户订单号查询
     * @param $out_trade_no
     * @param $mchid
     * @return ResponseInterface
     */
    public function orderCheckByOutTradeNo($out_trade_no, $mchid): ResponseInterface
    {
        return $this->cli->get("/v3/pay/transactions/out-trade-no/$out_trade_no?mchid=$mchid");
    }

    /**
     * 关闭订单
     * 注意：关单没有时间限制，建议在订单生成后间隔几分钟（最短5分钟）再调用关单接口，避免出现订单状态同步不及时导致关单失败。
     * 以下情况需要调用关单接口：
     * 1、商户订单支付失败需要生成新单号重新发起支付，要对原订单号调用关单，避免重复支付；
     * 2、系统下单后，用户支付超时，系统退出不再受理，避免用户继续，请调用关单接口。
     * @param $out_trade_no
     * @param $parameter
     * @return ResponseInterface
     */
    public function orderClose($out_trade_no, $parameter): ResponseInterface
    {
        return $this->cli->post("/v3/pay/transactions/out-trade-no/$out_trade_no/close", ['json' => $parameter]);
    }

    /**
     * 申请退款
     * @param $parameter
     * @return ResponseInterface
     */
    public function orderRefund($parameter): ResponseInterface
    {
        return $this->cli->post("/v3/refund/domestic/refunds", ['json' => $parameter]);
    }

    /**
     * 查询退款
     * @param $out_refund_no
     * @return ResponseInterface
     */
    public function refundCheck($out_refund_no): ResponseInterface
    {
        return $this->cli->get("/v3/refund/domestic/refunds/$out_refund_no");
    }

    public function jsApiPaySign($prepay_id): array
    {
        $res['appId'] = $this->conf['appid'];
        $res['timeStamp'] = (string)time(); //时间戳
        $res['nonceStr'] = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16); //随机字符串
        $res['signType'] = 'RSA'; //签名算法，暂只支持 RSA
        $res['package'] = 'prepay_id=' . $prepay_id; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
        $res['paySign'] = $this->signer->sha256WithRSA(
            $res['appId'] . "\n" . $res['timeStamp'] . "\n" . $res['nonceStr'] . "\n" . $res['package'] . "\n"
        );
        return $res;
    }
}
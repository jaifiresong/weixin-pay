<?php


namespace WeixinPay\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class HttpCli
{
    public static function summon(Signer $signer): Client
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(
            Middleware::mapRequest(function (RequestInterface $request) use ($signer) {
                $timestamp = time();
                $nonce = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 32);

                $body = '';
                $bodyStream = $request->getBody();
                // non-seekable stream need to be handled by the caller
                if ($bodyStream->isSeekable()) {
                    $body = (string)$bodyStream;
                    $bodyStream->rewind();
                }

                $str = $request->getMethod() . "\n"
                    . $request->getRequestTarget() . "\n"
                    . $timestamp . "\n"
                    . $nonce . "\n"
                    . $body . "\n";

                $token = $signer->sign($str, $timestamp, $nonce);
                return $request->withHeader('Authorization', $token);
            })
        );

        return new Client([
            'handler' => $stack,
            'base_uri' => 'https://api.mch.weixin.qq.com',
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

}
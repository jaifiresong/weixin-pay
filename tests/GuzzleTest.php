<?php


namespace Test;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * Guzzle文档
 * https://books.sangniao.com/manual/2723616501/3637867399
 *
 * PHPUnit的使用
 * https://zhuanlan.zhihu.com/p/97301928/
 */
class GuzzleTest extends TestCase
{
    public function test01()
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://www.songcj.com',
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);

        $rsp = $client->get('/html/json_parser/');
        $r = $rsp->getBody()->getContents();

        $this->assertIsString($r);
        //$this->assertEquals(1, 1);
    }

    // 使用中间件，为每个请求都添加一个标头的示例
    public function test02()
    {
        function add_header($header, $value)
        {
            return function (callable $handler) use ($header, $value) {
                return function (RequestInterface $request, array $options) use ($handler, $header, $value) {
                    $request = $request->withHeader($header, $value);
                    return $handler($request, $options);
                };
            };
        }


        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(add_header('X-Foo0000000000000', 'bar'));
        $client = new Client(['handler' => $stack, 'base_uri' => 'https://www.songcj.com', 'timeout' => 5,]);

        $rsp = $client->get('/server_info.php');
        $r = $rsp->getBody()->getContents();
        echo $r;
        $this->assertIsString($r);
    }

    // 使用中间件，为每个响应头都添加一个标头的示例
    public function test03()
    {
        function add_response_header($header, $value)
        {
            return function (callable $handler) use ($header, $value) {
                return function (
                    RequestInterface $request,
                    array            $options
                ) use ($handler, $header, $value) {
                    $promise = $handler($request, $options);
                    return $promise->then(
                        function (ResponseInterface $response) use ($header, $value) {
                            return $response->withHeader($header, $value);
                        }
                    );
                };
            };
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(add_response_header('X-Foo', 'bar'));

        $client = new Client(['handler' => $stack, 'base_uri' => 'https://www.songcj.com', 'timeout' => 5,]);

        $rsp = $client->get('/server_info.php');
        $r = json_encode($rsp->getHeaders());
        echo $r;
        $this->assertIsString($r);
    }

    //使用 GuzzleHttp\Middleware::mapRequest() 中间件可以更加简单的创建一个用于修改请求的中间件。 此中间件接受一个请求参数并返回要发送的请求的函数。
    public function test04()
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('X-Foo', 'bar');
        }));

        $client = new Client(['handler' => $stack]);
        $this->assertIsString('');
    }

    //使用 GuzzleHttp\Middleware::mapResponse() 中间件使得修改响应更加简单。
    public function test05()
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
            return $response->withHeader('X-Foo', 'bar');
        }));

        $client = new Client(['handler' => $stack]);
        $this->assertIsString('');
    }

    public function test06()
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::mapRequest(
            function (RequestInterface $request) {
//                var_dump($request->getUri()->getPath());
                var_dump($request->getBody()->getContents());
                return $request->withHeader('X-Foo', 'bar');
            }
        ));


        $client = new Client(['handler' => $stack, 'base_uri' => 'https://www.songcj.com', 'timeout' => 5,]);

        $rsp = $client->post('/html/json_parser/?id=1&t=2', ['json' => ['a' => 1, 'b' => 2]]);
        $r = json_encode($rsp->getHeaders());
        echo $r;
        $this->assertIsString($r);
    }
}
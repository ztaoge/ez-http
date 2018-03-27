<?php
namespace EzHttp\Http;

class Http
{
    public $method;
    public $data;
    public $uri;
    public $response;

    /**
     * 构造http报文
     * @param $content
     * @return string
     */
    public function httpEncode($content)
    {
        $header = '';
        $header .= "HTTP/1.1 200 OK\r\n";
        $header .= "Content-Type: text/html;charset=utf-8\r\n";
        $header .= "\r\n\r\n";

        return $header . $content;
    }

    /**
     * 解析http报文
     * @param $buffer
     */
    public function httpDecode($buffer)
    {
        list($http_header, $http_body) = explode("\r\n\r\n", $buffer, 2);
        $header_data = explode("\r\n", $http_header);
        $request_line = $header_data[0];
        unset($header_data[0]);
        $request_header = $header_data;
        list($method, $uri, $protocol) = explode(' ', $request_line, 3);
        $this->method = $method;
        $this->uri = $uri;
        $this->data = $http_body;
    }

    /**
     * 处理请求
     * @param callable $callback
     */
    public function handle(callable $callback)
    {
        //TODO: create a routine map, and search method in routine map

        $data = $callback();

        $this->response = $this->httpEncode($data);
    }
}
<?php

namespace Core\Http;

class Request
{
    /**
     * 获取HTTP方法
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 获取请求路径
     */
    public function getPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return rtrim($path, '/');
    }

    /**
     * 获取查询参数
     *
     * @param string $key 参数名
     * @param mixed $default 默认值
     */
    public function query(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * 获取POST数据
     *
     * @param string $key 参数名
     * @param mixed $default 默认值
     */
    public function post(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * 获取JSON请求体
     */
    public function json(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    /**
     * 获取请求头
     *
     * @param string $key 头名称
     * @param mixed $default 默认值
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$headerKey] ?? $default;
    }
}
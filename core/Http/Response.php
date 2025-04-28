<?php

namespace Core\Http;

class Response
{
    protected mixed $content = '';
    protected int $status = 200;
    protected array $headers = [];

    public function __construct(mixed $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function send(): void
    {
        http_response_code($this->status);

        // 添加自定义的响应头
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // 输出内容
        echo $this->formatContent();
    }

    /**
     * 创建JSON响应
     *
     * @param array $data 数据
     * @param int $status HTTP状态码
     */
    public static function json(array $data, int $status = 200): self
    {
        return new static(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * 格式化内容
     */
    protected function formatContent(): string
    {
        if (is_string($this->content)) {
            return $this->content;
        }

        if (is_array($this->content) || is_object($this->content)) {
            return json_encode($this->content);
        }

        return (string)$this->content;
    }
}

<?php

namespace Core\Exceptions;

use Throwable;
use Core\Http\Response;

class Handler
{
    /**
     * 处理框架中的异常
     *
     * @param Throwable $exception 异常对象
     * @return Response 响应对象
     */
    public function handle(Throwable $exception): Response
    {
        // 如果是数据库连接类的异常（PDOException），可以根据需求自定义
        if ($exception instanceof \PDOException) {
            return new Response('Database connection error: ' . $exception->getMessage(), 500);
        }

        // 默认情况，输出所有其他异常
        if ($exception instanceof \RuntimeException) {
            return new Response('Runtime Error: ' . $exception->getMessage(), 500);
        }

        // 如果是其他类型的异常，你也可以继续添加
        return new Response('An error occurred: ' . $exception->getMessage(), 500);
    }
}

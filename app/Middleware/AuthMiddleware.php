<?php

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;

class AuthMiddleware
{
    /**
     * 验证用户是否登录
     *
     * @param Request $request 请求对象
     * @param callable $next 下一个中间件/控制器
     * @return Response 响应对象
     */
    public function handle(Request $request, callable $next): Response
    {
        if (!$this->checkAuth($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }

    /**
     * 检查认证状态
     */
    protected function checkAuth(Request $request): bool
    {
        // 实际项目中这里会检查token或session
        return $request->header('Authorization') === 'valid-token';
    }
}
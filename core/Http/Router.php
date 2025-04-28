<?php

namespace Core\Http;

use Core\Http\Attributes\Controller;
use Core\Http\Attributes\Middleware;
use Core\Http\Attributes\Route;
use ReflectionClass;
use ReflectionMethod;

class Router
{
    protected array $routes      = [];
    protected array $middlewares = [];

    /**
     * 注册控制器路由
     *
     * @param string $controller 控制器类名
     */
    public function registerController(string $controller): void
    {
        $reflection = new ReflectionClass($controller);
        $attributes = $reflection->getAttributes(Controller::class);
        // 检查注解是否存在
        if (count($attributes) > 0) {
            // 获取 Controller 注解的实例
            $controllerAttribute = $attributes[0]->newInstance();
            // 访问前缀
            $prefix = $controllerAttribute->prefix;
        }
        // 遍历控制器的方法
        foreach ($reflection->getMethods() as $method) {
            // 获取方法上的 Route 注解
            $methodAttributes = $method->getAttributes(Route::class);
            if (count($methodAttributes) > 0) {
                $route       = $methodAttributes[0]->newInstance();
                $path        = $route->path;       // 获取路由路径
                $methodType  = $route->method;     // 获取 HTTP 方法
                $middlewares = $route->middleware; // 获取中间件

                // 也可以检查方法上的 Middleware 注解
                $methodMiddleware = $method->getAttributes(Middleware::class);
                if (count($methodMiddleware) > 0) {
                    $methodMiddleware = $methodMiddleware[0]->newInstance();
                    // 合并中间件
                    $middlewares = array_merge($middlewares, $methodMiddleware->middlewares);
                }
//                var_dump($prefix . $path);
//                var_dump($methodType, $prefix . $path, [$controller, $method->getName()]);
                // 为路由注册
                $this->addRoute(
                    $methodType,
                    $prefix . $path,
                    [$controller, $method->getName()],
                    $middlewares
                );
            }
        }
    }


    /**
     * 添加路由
     *
     * @param string $method HTTP方法
     * @param string $path 路由路径
     * @param callable $handler 处理函数
     * @param array $middlewares 中间件数组
     */
    public function addRoute(
        string   $method,
        string   $path,
        array|callable $handler,
        array    $middlewares = []
    ): void {
        $this->routes[$method][$path] = [
            'handler'     => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * 注册全局中间件
     *
     * @param string $name 中间件名称
     * @param callable $middleware 中间件函数
     */
    public function registerMiddleware(string $name, callable $middleware): void
    {
        $this->middlewares[$name] = $middleware;
    }

    /**
     * 分发请求
     *
     * @param Request $request 请求对象
     * @return Response 响应对象
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path   = $request->getPath();
        if (!isset($this->routes[$method][$path])) {
            return new Response('Not Found', 404);
        }

        $route       = $this->routes[$method][$path];
        $handler     = $route['handler'];
        $middlewares = $route['middlewares'];

        // 创建中间件调用链
        $next = function ($request) use ($handler) {
            return $this->callHandler($handler, $request);
        };

        foreach (array_reverse($middlewares) as $middlewareName) {
            if (!isset($this->middlewares[$middlewareName])) {
                throw new \RuntimeException("Middleware {$middlewareName} not registered");
            }

            $next = function ($request) use ($middlewareName, $next) {
                return call_user_func($this->middlewares[$middlewareName], $request, $next);
            };
        }

        return $next($request);
    }

    /**
     * 调用处理函数
     *
     * @param callable $handler 处理函数
     * @param Request $request 请求对象
     * @return Response 响应对象
     */
    protected function callHandler(array|callable $handler, Request $request): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = new $class();
            return $instance->$method($request);
        }

        return call_user_func($handler, $request);
    }
}

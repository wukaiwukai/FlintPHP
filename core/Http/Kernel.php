<?php

namespace Core\Http;

use Core\Exceptions\Handler;
use Core\Http\Router;
use Core\Database\DB;
use Core\Support\RouteLoader;

class Kernel
{
    protected Router $router;

    public function __construct()
    {
        $this->router = new Router();
        RouteLoader::load($this->router, BASE_PATH . '/app/Controllers');
        $this->handler = new Handler();
    }

    /**
     * 运行应用
     */
    public function run(): void
    {
        try {
        // 初始化数据库连接
        $this->initDatabase();

        // 注册路由
        $this->registerRoutes();

        // 注册中间件
        $this->registerMiddlewares();

        // 处理请求
        $request = new Request();
        $response = $this->router->dispatch($request);
        $response->send();
        } catch (\Throwable $exception) {
            // 捕获所有异常，调用异常处理器
            $response = $this->handler->handle($exception);
            $response->send(); // 输出响应
        }
    }

    /**
     * 初始化数据库连接
     */
    protected function initDatabase(): void
    {
        $config = require __DIR__.'/../../config/database.php';
        DB::connect($config);
    }

    /**
     * 注册路由
     */
    protected function registerRoutes(): void
    {
        // 注册控制器
        $this->router->registerController(\App\Controllers\HomeController::class);

        // 加载路由文件
        require __DIR__.'/../../routes/web.php';
    }

    /**
     * 注册中间件
     */
    protected function registerMiddlewares(): void
    {
        $this->router->registerMiddleware('auth', [new \App\Middleware\AuthMiddleware(), 'handle']);
    }
}
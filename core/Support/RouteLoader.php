<?php

namespace Core\Support;

use Core\Http\Router;

class RouteLoader
{
    /**
     * 递归扫描并注册所有控制器路由
     */
    public static function load(Router $router, string $controllerPath, string $namespace = 'App\\Controllers\\'): void
    {
        self::scanDirectory($router, $controllerPath, $namespace);
    }

    protected static function scanDirectory(Router $router, string $path, string $namespace): void
    {
        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $file;

            if (is_dir($fullPath)) {
                // 如果是目录，递归继续扫描
                self::scanDirectory($router, $fullPath, $namespace . $file . '\\');
            } elseif (is_file($fullPath) && str_ends_with($file, '.php')) {
                $className = $namespace . pathinfo($file, PATHINFO_FILENAME);

                if (class_exists($className)) {
                    $router->registerController($className);
                }
            }
        }
    }
}

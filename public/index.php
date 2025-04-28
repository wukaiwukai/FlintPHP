<?php
$start = microtime(true);
require __DIR__.'/../vendor/autoload.php';
define('BASE_PATH', dirname(__DIR__)); // 项目根目录
use Core\Http\Kernel;

// 创建并运行应用
$app = new Kernel();
$app->run();
$end = microtime(true);  // 记录结束时间
echo '总响应时间: ' . ($end - $start) . ' 秒';
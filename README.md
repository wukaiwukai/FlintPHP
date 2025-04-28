# FlintPHP
轻、快、打火石点燃整个应用，追求清晰、简洁、高效、现代感
一个轻量级、高性能的PHP框架，基于注解驱动和连接池技术

## 特性亮点

✨ **现代架构**  
- PHP 8.0+ 特性支持（Attributes注解等）
- PSR-4 自动加载规范
- 依赖注入容器

⚡ **极速路由**  
- 反射缓存路由解析
- 注解式路由定义
- 动态参数支持

🛢️ **高效数据库**  
- 智能连接池管理
- 预处理SQL防注入
- 简洁的查询构建器

## 快速开始

### 安装要求
- PHP 8.0
- Composer
- PDO扩展

```bash
git clone https://github.com/wukaiwukai/FlintPHP.git
cd FlintPHP
composer install
```
## 核心功能
### 路由系统
- 请求如下：127.0.0.1:8080/api/users
```bash
#[Controller('/api')]
class UserController
{
    #[Route('/users', 'GET')]
    public function listUsers(): Response { /* ... */ }

    #[Route('/index', 'post')]
    public function index(): Response { /* ... */ }
}
```
### 模型定义
```bash
<?php

namespace App\Models;

use Core\Database\Attribute\Column;
use Core\Database\Attribute\Table;
use Database\Model;
use DateTime;

#[Table(name: 'users')]
class User extends Model
{
    #[Column(name: 'id', type: 'int',length:11, primary: true)]
    public int $id;

    #[Column(name: 'username', type: 'varchar', length: 50, unique: true)]
    public string $username;

    #[Column(name: 'password', type: 'varchar', length: 255)]
    public string $password;

    #[Column(name: 'real_name', type: 'varchar', length: 50, nullable: true)]
    public ?string $real_name = null;

    #[Column(name: 'email', type: 'varchar', length: 100, nullable: true, unique: true)]
    public ?string $email = null;

    #[Column(name: 'phone', type: 'varchar', length: 20, nullable: true, unique: true)]
    public ?string $phone = null;

    #[Column(name: 'avatar', type: 'varchar', length: 255, nullable: true)]
    public ?string $avatar = null;

    #[Column(name: 'status', type: 'tinyint', default: 1)]
    public int $status = 1;

    #[Column(name: 'last_login', type: 'datetime', nullable: true)]
    public ?DateTime $last_login = null;

    #[Column(name: 'created_at', type: 'datetime')]
    public DateTime $created_at;

    #[Column(name: 'updated_at', type: 'datetime')]
    public DateTime $updated_at;

    #[Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    public ?DateTime $deleted_at = null;
}

```
### 数据库操作
```bash
use Database\Models\User;

// 查询所有用户
$users = User::query()->get();
foreach ($users as $user) {
    echo $user->getAttribute('username') . "\n";
}
######################################################################
use Database\Models\User;

// 查询单个用户 by ID
$user = User::find(1); // 查找 ID 为 1 的用户
echo $user->getAttribute('username');

######################################################################

// 查询单个用户
$user = User::find(1);

// 更新用户的 email 和 real_name
$user->setAttribute('email', 'new.email@example.com');
$user->setAttribute('real_name', 'John Updated');
$user->save();

######################################################################
use Database\Models\User;

// 查询单个用户
$user = User::find(1);

// 执行软删除（将 `deleted_at` 设为当前时间）
$user->setAttribute('deleted_at', (new DateTime())->format('Y-m-d H:i:s'));
$user->save();

######################################################################

use Database\Models\User;

// 查询 username 为 'john_doe' 的用户
$user = User::query()->where('username', 'john_doe')->first();
if ($user) {
    echo $user->getAttribute('real_name'); // 输出 'John Doe'
} else {
    echo "用户未找到";
}


######################################################################

use Database\Models\User;

// 查询 status 为 1 且 email 包含 'example.com' 的所有用户
$users = User::query()
    ->where('status', 1)
    ->where('email', 'LIKE', '%example.com%')
    ->get();

foreach ($users as $user) {
    echo $user->getAttribute('username') . "\n";
}

######################################################################

use Database\Models\User;

// 查询 'last_login' 为 NULL 的用户
$users = User::query()->whereNull('last_login')->get();
foreach ($users as $user) {
    echo $user->getAttribute('username') . "\n";
}

######################################################################

use Database\Models\User;

// 查询 'email' 不为空的用户
$users = User::query()->whereNotNull('email')->get();
foreach ($users as $user) {
    echo $user->getAttribute('username') . "\n";
}

######################################################################

use Database\Models\User;

// 查询 username 为 'john_doe' 或 email 为 'jane.smith@example.com' 的用户
$user = User::query()
    ->where('username', 'john_doe')
    ->orWhere('email', 'jane.smith@example.com')
    ->first();

if ($user) {
    echo $user->getAttribute('real_name');
} else {
    echo "没有找到匹配的用户";
}

######################################################################

use Database\Models\User;

// 查询 username 为 'john_doe' 或 'jane_smith' 的用户
$users = User::query()->whereIn('username', ['john_doe', 'jane_smith'])->get();
foreach ($users as $user) {
    echo $user->getAttribute('username') . "\n";
}

######################################################################

use Database\Models\User;

// 查询 'created_at' 在 2023-01-01 和 2023-12-31 之间的用户
$users = User::query()
    ->whereBetween('created_at', ['2023-01-01', '2023-12-31'])
    ->get();

foreach ($users as $user) {
    echo $user->getAttribute('username') . "\n";
}

######################################################################

use Database\Models\User;

// 查询是否存在用户名为 'john_doe' 的用户
$exists = User::query()->where('username', 'john_doe')->exists();

if ($exists) {
    echo "用户已存在";
} else {
    echo "用户不存在";
}

######################################################################
// 事务示例
$conn = DB::beginTransaction();
try {
    DB::execute("UPDATE accounts SET balance = ?", [$amount], $conn);
    DB::commit($conn);
} catch (Exception $e) {
    DB::rollback($conn);
}
```
### 项目结构
```bash
myframework/
├── app/          # 应用代码
│   ├── Controllers/
│   ├── Models/
│   └── Middleware/
├── config/       # 配置文件
├── core/         # 框架核心
├── public/       # 入口文件
└── tests/        # 单元测试
```

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

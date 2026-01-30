<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 设置表名
    protected $name = 'users';

    // 设置字段信息
    protected $schema = [
        'id'           => 'int',
        'username'     => 'string',
        'email'        => 'string',
        'password_hash' => 'string',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 类型转换
    protected $type = [
        'id' => 'integer',
    ];

    // 隐藏字段（序列化时不包含）
    protected $hidden = ['password_hash'];
}

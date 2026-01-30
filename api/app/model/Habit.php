<?php
namespace app\model;

use think\Model;

class Habit extends Model
{
    // 设置表名
    protected $name = 'habits';

    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'user_id'     => 'int',
        'name'        => 'string',
        'emoji'       => 'string',
        'color'       => 'string',
        'archived'    => 'int',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 类型转换
    protected $type = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'archived'    => 'integer',
    ];

    // 关联：打卡记录
    public function records()
    {
        return $this->hasMany(HabitRecord::class, 'habit_id', 'id');
    }
}

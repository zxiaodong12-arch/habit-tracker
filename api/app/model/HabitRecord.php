<?php
namespace app\model;

use think\Model;

class HabitRecord extends Model
{
    // 设置表名
    protected $name = 'habit_records';

    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'habit_id'    => 'int',
        'record_date' => 'date',
        'completed'   => 'int',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 类型转换
    protected $type = [
        'id'        => 'integer',
        'habit_id'  => 'integer',
        'completed' => 'integer',
    ];

    // 关联：习惯
    public function habit()
    {
        return $this->belongsTo(Habit::class, 'habit_id', 'id');
    }
}

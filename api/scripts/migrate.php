<?php
/**
 * 数据迁移脚本：从 JSON 文件导入到 MySQL
 * 用法: php scripts/migrate.php <json文件路径> [user_id]
 */

require __DIR__ . '/../vendor/autoload.php';

use think\facade\Db;
use app\model\Habit;
use app\model\HabitRecord;

// 初始化应用
$app = new think\App();
$app->initialize();

if ($argc < 2) {
    echo "用法: php migrate.php <json文件路径> [user_id]\n";
    echo "示例: php migrate.php ../export.json 1\n";
    exit(1);
}

$jsonFilePath = $argv[1];
$userId = $argv[2] ?? null;

if (!file_exists($jsonFilePath)) {
    echo "错误: 文件不存在 {$jsonFilePath}\n";
    exit(1);
}

$jsonData = json_decode(file_get_contents($jsonFilePath), true);
$habits = $jsonData['habits'] ?? [];

echo "📦 开始迁移 " . count($habits) . " 个习惯...\n\n";

$successCount = 0;
$errorCount = 0;

foreach ($habits as $habit) {
    try {
        // 插入习惯
        $habitModel = new Habit();
        $habitModel->user_id = $userId;
        $habitModel->name = $habit['name'];
        $habitModel->emoji = $habit['emoji'] ?? '📝';
        $habitModel->color = $habit['color'] ?? '#10b981';
        $habitModel->archived = isset($habit['archived']) && $habit['archived'] ? 1 : 0;
        if (isset($habit['createdAt'])) {
            $habitModel->created_at = date('Y-m-d H:i:s', strtotime($habit['createdAt']));
        }
        $habitModel->save();

        $habitId = $habitModel->id;
        echo "✅ 习惯 \"{$habit['name']}\" 创建成功 (ID: {$habitId})\n";

        // 插入打卡记录
        $records = $habit['records'] ?? [];
        $recordDates = array_filter(array_keys($records), function($date) use ($records) {
            return $records[$date] === true;
        });

        if (count($recordDates) > 0) {
            foreach ($recordDates as $date) {
                $record = new HabitRecord();
                $record->habit_id = $habitId;
                $record->record_date = $date;
                $record->completed = 1;
                $record->save();
            }
            echo "   📅 导入 " . count($recordDates) . " 条打卡记录\n";
        }

        $successCount++;
    } catch (\Exception $e) {
        echo "❌ 迁移习惯 \"{$habit['name']}\" 失败: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n✨ 迁移完成!\n";
echo "   成功: {$successCount} 个\n";
echo "   失败: {$errorCount} 个\n";

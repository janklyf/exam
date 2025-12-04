<?php
// 为考试表添加考试模式字段
require_once 'db.php';

// 检查考试表是否已存在考试模式字段
$columns = get_all_results("PRAGMA table_info('考试')");
$has_mode_field = false;
foreach ($columns as $column) {
    if ($column['name'] == '考试模式') {
        $has_mode_field = true;
        break;
    }
}

if (!$has_mode_field) {
    // 添加考试模式字段，默认为'练习模式'
    $result = execute_non_query(
        "ALTER TABLE 考试 ADD COLUMN 考试模式 VARCHAR(20) DEFAULT '练习模式'"
    );
    
    if ($result) {
        echo "✅ 成功为考试表添加考试模式字段\n";
    } else {
        echo "❌ 为考试表添加考试模式字段失败\n";
    }
} else {
    echo "ℹ️ 考试表已存在考试模式字段\n";
}

// 检查现有考试数据，确保所有记录都有考试模式
$exams = get_all_results("SELECT 考试ID, 考试模式 FROM 考试");
$updated_count = 0;

foreach ($exams as $exam) {
    if (empty($exam['考试模式'])) {
        $result = execute_non_query(
            "UPDATE 考试 SET 考试模式 = '练习模式' WHERE 考试ID = ?",
            [$exam['考试ID']]
        );
        if ($result) {
            $updated_count++;
        }
    }
}

echo "ℹ️ 更新了 {$updated_count} 条考试记录的考试模式\n";

echo "\n操作完成！\n";

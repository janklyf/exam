<?php
require_once 'db.php';

try {
    // 1. 检查考试表是否已经有试卷题目关联的字段或表
    $db = get_db_connection();
    
    // 2. 如果考试题目关联表不存在，则创建
    $db->exec('CREATE TABLE IF NOT EXISTS 考试题目关联 (
        考试ID INTEGER,
        题目ID INTEGER,
        FOREIGN KEY (考试ID) REFERENCES 考试(考试ID) ON DELETE CASCADE,
        FOREIGN KEY (题目ID) REFERENCES 题库(题目ID) ON DELETE CASCADE,
        PRIMARY KEY (考试ID, 题目ID)
    )');
    
    // 3. 迁移试卷数据到考试表
    echo "开始迁移试卷数据到考试表...\n";
    
    // 获取所有试卷
    $papers = get_all_results("SELECT * FROM 试卷");
    
    foreach ($papers as $paper) {
        // 检查是否已存在相同名称的考试
        $existing_exam = get_single_result(
            "SELECT * FROM 考试 WHERE 考试名称 = ?",
            [$paper['试卷名称']]
        );
        
        if (!$existing_exam) {
            // 插入新考试记录
            execute_non_query(
                "INSERT INTO 考试 (考试名称, 开始时间, 结束时间, 考试时长, 总分, 适用班级, 创建教师ID, 状态, 创建时间) 
                 VALUES (?, datetime('now'), datetime('now', '+1 day'), ?, ?, '[]', ?, ?, ?)",
                [
                    $paper['试卷名称'],
                    $paper['考试时长'],
                    $paper['总分'],
                    $paper['创建人ID'],
                    $paper['状态'],
                    $paper['创建时间']
                ]
            );
            
            $new_exam_id = $db->lastInsertRowID();
            
            // 迁移试卷题目关联到考试题目关联
            $paper_questions = get_all_results(
                "SELECT 题目ID FROM 试卷题目关联 WHERE 试卷ID = ?",
                [$paper['试卷ID']]
            );
            
            foreach ($paper_questions as $pq) {
                execute_non_query(
                    "INSERT INTO 考试题目关联 (考试ID, 题目ID) VALUES (?, ?)",
                    [$new_exam_id, $pq['题目ID']]
                );
            }
            
            echo "已迁移试卷: {$paper['试卷名称']} 到考试表\n";
        } else {
            echo "跳过试卷: {$paper['试卷名称']}，已存在相同名称的考试\n";
        }
    }
    
    // 4. 更新考试管理页面，添加题目关联功能
    echo "数据迁移完成！\n";
    echo "接下来需要更新考试管理页面，添加题目关联功能。\n";
    
} catch (Exception $e) {
    echo "合并过程中发生错误: " . $e->getMessage() . "\n";
}
?>
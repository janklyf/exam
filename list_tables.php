<?php
require_once 'db.php';

try {
    $db = get_db_connection();
    
    // 获取所有表
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
    
    echo "数据库中的表:\n";
    echo "================\n\n";
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $table_name = $row['name'];
        echo "表名: $table_name\n";
        
        // 获取表的字段
        $columns = $db->query("PRAGMA table_info($table_name);");
        echo "字段: ";
        $column_names = [];
        while ($col = $columns->fetchArray(SQLITE3_ASSOC)) {
            $column_names[] = $col['name'] . " (" . $col['type'] . ")";
        }
        echo implode(", ", $column_names) . "\n\n";
    }
    
    $db->close();
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?>
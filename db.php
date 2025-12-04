<?php
/**
 * 数据库连接文件
 * 负责创建和管理与SQLite数据库的连接
 */

require_once 'config.php';

/**
 * 获取数据库连接实例
 * @return SQLite3 数据库连接对象
 */
function get_db_connection() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new SQLite3(DB_PATH);
            $db->enableExceptions(true);
            $db->exec('PRAGMA foreign_keys = ON;'); // 启用外键约束
        } catch (Exception $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    return $db;
}

/**
 * 执行SQL查询并返回结果
 * @param string $sql SQL查询语句
 * @param array $params 参数数组
 * @return SQLite3Result 查询结果对象
 */
function execute_query($sql, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($sql);
    
    if ($stmt === false) {
        die('SQL语句准备失败: ' . $db->lastErrorMsg());
    }
    
    // 绑定参数
    if (is_array($params)) {
        if (array_keys($params) === range(0, count($params) - 1)) {
            // 索引数组，使用?占位符
            foreach ($params as $index => $value) {
                $type = SQLITE3_TEXT;
                if (is_int($value)) {
                    $type = SQLITE3_INTEGER;
                } elseif (is_float($value)) {
                    $type = SQLITE3_FLOAT;
                }
                
                $stmt->bindValue($index + 1, $value, $type);
            }
        } else {
            // 关联数组，使用:key占位符
            foreach ($params as $key => $value) {
                $type = SQLITE3_TEXT;
                if (is_int($value)) {
                    $type = SQLITE3_INTEGER;
                } elseif (is_float($value)) {
                    $type = SQLITE3_FLOAT;
                }
                
                $stmt->bindValue(':' . $key, $value, $type);
            }
        }
    }
    
    $result = $stmt->execute();
    
    if ($result === false) {
        $error_msg = 'SQL执行失败: ' . $db->lastErrorMsg();
        // 检查是否是AJAX请求或模态请求
        if (isset($_GET['modal']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
            exit;
        }
        die($error_msg);
    }
    
    return $result;
}

/**
 * 执行SQL查询并返回单行结果
 * @param string $sql SQL查询语句
 * @param array $params 参数数组
 * @return array|null 查询结果数组，失败返回null
 */
function get_single_result($sql, $params = []) {
    $result = execute_query($sql, $params);
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $result->finalize();
    return $row;
}

/**
 * 执行SQL查询并返回所有结果
 * @param string $sql SQL查询语句
 * @param array $params 参数数组
 * @return array 查询结果数组
 */
function get_all_results($sql, $params = []) {
    $result = execute_query($sql, $params);
    $rows = [];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    
    $result->finalize();
    return $rows;
}

/**
 * 执行SQL插入、更新或删除操作
 * @param string $sql SQL语句
 * @param array $params 参数数组
 * @return int 受影响的行数
 */
function execute_non_query($sql, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($sql);
    
    if ($stmt === false) {
        die('SQL语句准备失败: ' . $db->lastErrorMsg());
    }
    
    // 绑定参数
    if (is_array($params)) {
        if (array_keys($params) === range(0, count($params) - 1)) {
            // 索引数组，使用?占位符
            foreach ($params as $index => $value) {
                $type = SQLITE3_TEXT;
                if (is_int($value)) {
                    $type = SQLITE3_INTEGER;
                } elseif (is_float($value)) {
                    $type = SQLITE3_FLOAT;
                }
                
                $stmt->bindValue($index + 1, $value, $type);
            }
        } else {
            // 关联数组，使用:key占位符
            foreach ($params as $key => $value) {
                $type = SQLITE3_TEXT;
                if (is_int($value)) {
                    $type = SQLITE3_INTEGER;
                } elseif (is_float($value)) {
                    $type = SQLITE3_FLOAT;
                }
                
                $stmt->bindValue(':' . $key, $value, $type);
            }
        }
    }
    
    $result = $stmt->execute();
    
    if ($result === false) {
        $error_msg = 'SQL执行失败: ' . $db->lastErrorMsg();
        // 检查是否是AJAX请求或模态请求
        if (isset($_GET['modal']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
            exit;
        }
        die($error_msg);
    }
    
    $result->finalize();
    return $db->changes();
}

/**
 * 获取最后插入的ID
 * @return int 最后插入的ID
 */
function get_107333520() {
    $db = get_db_connection();
    return $db->lastInsertRowID();
}

/**
 * 获取最后插入的ID（固定名称函数，便于使用）
 * @return int 最后插入的ID
 */
function get_107337543() {
    $db = get_db_connection();
    return $db->lastInsertRowID();
}

/**
 * 获取最后插入的ID（固定名称函数，便于使用）
 * @return int 最后插入的ID
 */
function get_last_insert_id() {
    $db = get_db_connection();
    return $db->lastInsertRowID();
}

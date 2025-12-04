<?php
/**
 * 公共函数文件
 * 包含认证、输入验证、文件上传等公共功能
 */

// 先加载db.php，避免循环依赖
require_once 'db.php';
require_once 'config.php';

/**
 * 检查用户是否登录
 * @return bool 是否登录
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * 检查用户权限
 * @param string $required_role 要求的角色
 * @return void 如果没有权限，重定向到登录页面
 */
function check_permission($required_role) {
    if (!is_logged_in() || $_SESSION['user_role'] != $required_role) {
        header('Location: login.php');
        exit;
    }
}

/**
 * 获取当前用户信息
 * @return array|null 当前用户信息数组，未登录返回null
 */
function get_logged_in_user() {
    if (is_logged_in()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role'],
            'user_name' => $_SESSION['user_name']
        ];
    }
    return null;
}

/**
 * 获取系统设置
 * @param string $setting_key 设置项名称
 * @param mixed $default 默认值
 * @return mixed 设置值
 */
function get_system_setting($setting_key, $default = null) {
    static $settings = null;
    
    // 只加载一次设置
    if ($settings === null) {
        $settings = [];
        $result = get_all_results("SELECT * FROM 系统设置");
        foreach ($result as $row) {
            $settings[$row['设置项']] = $row['设置值'];
        }
        
        // 设置默认值
        $default_settings = [
            'system_name' => '在线考试管理系统',
            'exam_duration' => '60',
            'min_exam_duration' => '1',
            'pass_score' => '60',
            'auto_grade' => '1',
            'allow_student_registration' => '0'
        ];
        
        $settings = array_merge($default_settings, $settings);
    }
    
    // 检查是否存在该设置项
    if (isset($settings[$setting_key])) {
        return $settings[$setting_key];
    }
    
    return $default;
}

/**
 * 用户登录
 * @param string $username 用户名（使用姓名登录）
 * @param string $password 密码
 * @return array|null 登录成功返回用户信息，失败返回null
 */
function user_login($username, $password) {
    // 检查管理员
    $admin = get_single_result(
        "SELECT * FROM 管理员 WHERE 姓名 = :username",
        ['username' => $username]
    );
    
    // 管理员使用明文密码验证
    if ($admin && $password == $admin['密码']) {
        return [
            'user_id' => $admin['管理员ID'],
            'user_role' => ROLE_ADMIN,
            'user_name' => $admin['姓名']
        ];
    }
    
    // 检查教师
    $teacher = get_single_result(
        "SELECT * FROM 教师 WHERE 姓名 = :username",
        ['username' => $username]
    );
    
    // 教师使用明文密码验证
    if ($teacher && $password == $teacher['密码']) {
        return [
            'user_id' => $teacher['教师ID'],
            'user_role' => ROLE_TEACHER,
            'user_name' => $teacher['姓名']
        ];
    }
    
    // 检查学生（使用姓名作为登录账号）
    $student = get_single_result(
        "SELECT * FROM 学生 WHERE 姓名 = :username",
        ['username' => $username]
    );
    
    if ($student && $password == $student['密码']) {
        return [
            'user_id' => $student['学生ID'],
            'user_role' => ROLE_STUDENT,
            'user_name' => $student['姓名']
        ];
    }
    
    return null;
}

/**
 * 用户登出
 * @return void
 */
function user_logout() {
    // 在销毁会话之前获取用户角色
    $user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
    
    session_destroy();
    
    // 根据用户角色跳转到对应的登录页面
    if ($user_role == ROLE_ADMIN) {
        // 管理员跳转到admin目录下的登录页面
        header('Location: admin/login.php');
    } elseif ($user_role == ROLE_TEACHER) {
        // 教师跳转到teacher目录下的登录页面
        header('Location: teacher/login.php');
    } else {
        // 学生跳转到根目录下的登录页面
        header('Location: login.php');
    }
    exit;
}

/**
 * 教师登出
 * @return void
 */
function teacher_logout() {
    // 教师登出，直接跳转到教师登录页面
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * 过滤输入数据
 * @param string $input 输入数据
 * @return string 过滤后的数据
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input));
}

/**
 * 验证邮箱格式
 * @param string $email 邮箱地址
 * @return bool 是否为有效邮箱
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 生成随机字符串
 * @param int $length 字符串长度
 * @return string 随机字符串
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * 上传文件
 * @param array $file 文件信息数组（$_FILES中的项）
 * @param string $upload_dir 上传目录
 * @return array 包含上传结果的数组，成功时包含'success' => true和'file_path' => 文件路径，失败时包含'success' => false和'message' => 错误信息
 */
function upload_file($file, $upload_dir) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '文件上传失败：' . $file['error']];
    }
    
    // 检查文件类型
    $file_type = '';
    if (function_exists('finfo_file')) {
        // 使用 finfo_file 获取 MIME 类型（推荐）
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        // 兼容旧版本 PHP
        $file_type = mime_content_type($file['tmp_name']);
    } else {
        // 最后使用文件扩展名判断
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        $file_type = isset($mime_types[$file_extension]) ? $mime_types[$file_extension] : 'application/octet-stream';
    }
    
    if (!in_array($file_type, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => '不支持的文件类型：' . $file_type];
    }
    
    // 检查文件大小
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => '文件大小超过限制（最大2MB）'];
    }
    
    // 确保上传目录存在
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // 生成唯一文件名
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = generate_random_string(10) . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;
    
    // 移动文件
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'file_path' => $file_name];
    }
    
    return ['success' => false, 'message' => '文件移动失败'];
}

/**
 * 格式化日期时间
 * @param string $datetime 日期时间字符串
 * @param string $format 格式化格式
 * @return string 格式化后的日期时间
 */
function format_datetime($datetime, $format = 'Y-m-d H:i:s') {
    $date = new DateTime($datetime);
    return $date->format($format);
}

/**
 * 获取角色名称
 * @param string $role 角色代码
 * @return string 角色名称
 */
function get_role_name($role) {
    $role_names = [
        ROLE_ADMIN => '管理员',
        ROLE_TEACHER => '教师',
        ROLE_STUDENT => '学生'
    ];
    
    return isset($role_names[$role]) ? $role_names[$role] : '未知角色';
}

/**
 * 获取考试状态名称
 * @param string $status 状态代码
 * @return string 状态名称
 */
function get_exam_status_name($status) {
    $status_names = [
        EXAM_STATUS_NOT_PUBLISHED => '未发布',
        EXAM_STATUS_PUBLISHED => '已发布',
        EXAM_STATUS_FINISHED => '已结束'
    ];
    
    return isset($status_names[$status]) ? $status_names[$status] : '未知状态';
}

/**
 * 获取题目类型名称
 * @param string $type 类型代码
 * @return string 类型名称
 */
function get_question_type_name($type) {
    $type_names = [
        QUESTION_TYPE_SINGLE_CHOICE => '单选题',
        QUESTION_TYPE_JUDGMENT => '判断题',
        QUESTION_TYPE_CHINESE_TYPING => '中文打字题',
        QUESTION_TYPE_ENGLISH_TYPING => '英文打字题'
    ];
    
    return isset($type_names[$type]) ? $type_names[$type] : '未知类型';
}

/**
 * 获取难度名称
 * @param string $difficulty 难度代码
 * @return string 难度名称
 */
function get_difficulty_name($difficulty) {
    $difficulty_names = [
        DIFFICULTY_EASY => '简单',
        DIFFICULTY_MEDIUM => '中等',
        DIFFICULTY_HARD => '困难'
    ];
    
    return isset($difficulty_names[$difficulty]) ? $difficulty_names[$difficulty] : '未知难度';
}

/**
 * 显示提示信息
 * @param string $message 提示信息
 * @param string $type 信息类型（success/info/warning/danger）
 * @return void
 */
function show_message($message, $type = 'info') {
    echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>";
    echo $message;
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
}

/**
 * 重定向到指定页面
 * @param string $url 目标URL
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

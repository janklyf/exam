<?php
/**
 * 考试管理系统配置文件
 * 包含系统配置、数据库配置、资源路径配置等
 */

// 错误报告设置
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 系统基本配置
define('SYSTEM_NAME', '在线考试管理系统');
define('SYSTEM_VERSION', '1.0.0');

// 数据库配置
define('DB_PATH', __DIR__ . '/data/exam.sqlite3');

// 资源路径配置
define('BOOTSTRAP_CSS', './bootstrap/css/bootstrap.min.css');
define('BOOTSTRAP_JS', './bootstrap/js/bootstrap.bundle.min.js');
define('CHART_JS', './chartjs/chart.js');
define('JQUERY_JS', 'https://code.jquery.com/jquery-3.6.0.min.js');

// 上传配置
define('UPLOAD_PATH', 'uploads/questions/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// 会话配置
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // 生产环境改为1
ini_set('session.gc_maxlifetime', 3600); // 会话超时时间（秒）
session_start();

// 角色定义
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');

// 状态定义
define('EXAM_STATUS_NOT_PUBLISHED', '未发布');
define('EXAM_STATUS_PUBLISHED', '已发布');
define('EXAM_STATUS_FINISHED', '已结束');

define('SCORE_STATUS_NOT_COMPLETED', '未完成');
define('SCORE_STATUS_SUBMITTED', '已提交');
define('SCORE_STATUS_GRADED', '已批改');

// 题目类型定义
define('QUESTION_TYPE_SINGLE_CHOICE', '单选题');
define('QUESTION_TYPE_JUDGMENT', '判断题');
define('QUESTION_TYPE_CHINESE_TYPING', '中文打字题');
define('QUESTION_TYPE_ENGLISH_TYPING', '英文打字题');

// 难度定义
define('DIFFICULTY_EASY', '简单');
define('DIFFICULTY_MEDIUM', '中等');
define('DIFFICULTY_HARD', '困难');

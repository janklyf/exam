<?php
// 先加载db.php，避免循环依赖
require_once 'db.php';
require_once 'config.php';
require_once 'functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    $user = user_login($username, $password);
    if ($user && $user['user_role'] == ROLE_STUDENT) {
        // 登录成功，保存会话
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['user_role'];
        $_SESSION['user_name'] = $user['user_name'];
        
        // 学生跳转到学生页面
        header('Location: student/index.php');
        exit;
    } else {
        $error = '用户名或密码错误，或您不是学生用户，请重试';
    }
}

// 获取系统名称
$system_name = get_system_setting('system_name');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学生登录 - <?php echo $system_name; ?></title>
    <!-- 引入Bootstrap 5 国内CDN，降低加载延迟 -->
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 护眼渐变背景，无额外资源占用 */
        body {
            background: linear-gradient(135deg, #e8f4f8 0%, #f0f8fb 50%, #eaf6fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.8);
        }
        .login-header {
            background-color: #0d6efd;
            color: white;
            padding: 16px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-card bg-white">
        <!-- 登录头部 -->
        <div class="login-header">
            <h4 class="mb-0"><?php echo $system_name; ?></h4>
            <p class="mb-0 fs-6 opacity-80">学生登录</p>
        </div>
        <!-- 登录表单 -->
        <div class="p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="post" action="login.php" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">用户名</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名（学生使用姓名）" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">登录密码</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">登录系统</button>
                <!-- 注册入口 -->
                <div class="text-center mt-3">
                    <a href="student_registration.php" class="text-primary text-decoration-none">没有账号？去注册</a>
                </div>
            </form>
        </div>
    </div>
 
    <!-- 引入Bootstrap JS核心包 -->
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // 简单前端登录校验
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // 保留原有表单提交逻辑，不阻止默认行为
            // 原有功能由PHP后端处理
        });
    </script>
</body>
</html>
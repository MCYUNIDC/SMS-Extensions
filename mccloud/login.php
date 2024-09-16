<?php
// 启动会话
session_start();

// 包含数据库配置文件
include 'config.php';

// 检查是否已登录
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

// 处理登录请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL查询
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['password'] === $password) {
            $_SESSION['loggedin'] = true;
            header("Location: index.php");
            exit;
        } else {
            $error = "用户名或密码错误";
        }
    } else {
        $error = "用户名或密码错误";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>MC云中转 - 后台登录</title>
    <meta name="author" content="Nathan">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" type="text/css" href="https://sms.stay33.cn/static/css/style.min.css">
</head>
<body>
<script src="./ui.js"></script>
        <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <img class="mx-auto h-10 w-auto" alt="MC云短信" src="https://sms.stay33.cn/storage/upload/20240326/c3db23e748bb365ebdcc17afc130f0e2.png">
                <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">欢迎回来！</h2>
            </div>
  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" method="POST" action="">
      <div>
        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">用户名</label>
        <div class="mt-2">
          <input id="text" name="username" type="text" autocomplete="text" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
        </div>
      </div>


      <div>
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm font-medium leading-6 text-gray-900">密码</label>
          <div class="text-sm">
            <a href="https://cn.bing.com/search?pglt=161&q=%E8%87%AA%E5%B7%B1%E5%8E%BB%E6%95%B0%E6%8D%AE%E5%BA%93%E6%94%B9%E5%8E%BB&cvid=56276a5ca9994514b6babf7222337b08&gs_lcrp=EgZjaHJvbWUyBggAEEUYOdIBBzQwNGowajGoAgCwAgA&FORM=ANNTA1&PC=U531" class="font-semibold text-indigo-600 hover:text-indigo-500">忘记密码？</a>
          </div>
        </div>
        <div class="mt-2">
          <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
        </div>
      </div>

      <div>
        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">登录</button>
      </div>
    </form>
    <p class="mt-10 text-center text-sm text-gray-500">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
    </p>
    </div>
    </div>
</body>
</html>
    <script>
        function isMobile() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            return /android|iphone|ipad|ipod|opera mini|iemobile|wpdesktop/.test(userAgent.toLowerCase());
        }

        window.onload = function() {
            if (isMobile()) {
                document.body.innerHTML = '<h2>不支持手机编辑</h2>';
            }
        }
    </script>
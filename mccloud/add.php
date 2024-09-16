<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// 包含数据库配置文件
include 'config.php';

// 处理添加通道请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $system = intval($_POST['system']);
    $api_url = $_POST['api_url'];
    $api_channel = $_POST['api_channel'];
    $api_username = $_POST['api_username'];
    $api_key = $_POST['api_key'];

    $sql = "INSERT INTO channels (system, api_url, api_channel, api_username, api_key) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $system, $api_url, $api_channel, $api_username, $api_key);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>MC云中转 - 添加通道</title>
    <meta name="author" content="Nathan">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" type="text/css" href="https://sms.stay33.cn/static/css/style.min.css">
</head>
<body>
<script src="./ui.js"></script>
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
    <form class="space-y-6" method="POST" action="">
        <div>
            <label for="system" class="block text-sm font-medium leading-6 text-gray-900">选择系统:</label>
            <select id="system" name="system" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                <option value="1">企通信短信系统</option>
                <option value="2">南逸短信系统</option>
            </select>
        </div>
        <div>
          <label for="api_url" class="block text-sm font-medium leading-6 text-gray-900">API接口:</label>
          <div class="mt-2">
            <input id="api_url" name="api_url" type="text" autocomplete="api_url" required pattern="https?://.+" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
          </div>
        </div>
        <div>
          <label for="api_channel" class="block text-sm font-medium leading-6 text-gray-900">通道ID:</label>
          <div class="mt-2">
            <input id="api_channel" name="api_channel" type="text" autocomplete="api_channel" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
          </div>
        </div>
        <div>
          <label for="api_username" class="block text-sm font-medium leading-6 text-gray-900">用户名:</label>
          <div class="mt-2">
            <input id="api_username" name="api_username" type="text" autocomplete="api_username" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
          </div>
        </div>
        <div>
          <label for="api_key" class="block text-sm font-medium leading-6 text-gray-900">API密钥:</label>
          <div class="mt-2">
            <input id="api_key" name="api_key" type="text" autocomplete="api_key" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
          </div>
        </div>
      <div>
        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">保存</button>
      </div>
    </form>
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
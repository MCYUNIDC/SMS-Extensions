<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// 包含数据库配置文件
include 'config.php';

// 处理删除请求
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM channels WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}

// 获取通道列表
$sql = "SELECT * FROM channels";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>MC云中转 - 通道管理</title>
    <meta name="author" content="Nathan">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" type="text/css" href="https://sms.stay33.cn/static/css/style.min.css">
</head>
<body>
<script src="./ui.js"></script>
<body><br><br><br><br>
<div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="border-b border-gray-200 pb-5">
       <h3 class="text-base font-semibold leading-6 text-gray-900">&nbsp;通道管理</h3>
    </div><br>
<div class="px-4 sm:px-6 lg:px-8">
  <div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
      <h1 class="text-base font-semibold leading-6 text-gray-900">API信息</h1>
      <p class="mt-2 text-sm text-gray-700">
接口状态：已完成<br>
接口URL：/api.php<br>
Content-Type：multipart/form-data<br>
API密钥：管理员密码<br>
</p>
    </div>
    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
      <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" onclick="window.open('add.php', '_blank')">添加通道</button><br>
      <a href="https://wiki.mcyunidc.cn/sms"><button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">使用教程</button></a>
    </div>
  </div>
  <div class="-mx-4 mt-8 sm:-mx-0">
    <table class="min-w-full divide-y divide-gray-300">
      <thead border="1">
        <tr>
          <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">系统</th>
          <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">接口</th>
          <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">通道</th>
          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">用户名</th>
          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">密钥</th>
          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">操作</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
          <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td class="w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:w-auto sm:max-w-none sm:pl-0"><?php echo $row['system'] == 1 ? '企通信短信系统' : '南逸短信系统'; ?></td>
          <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell"><?php echo htmlspecialchars($row['api_url']); ?></td>
          <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell"><?php echo htmlspecialchars($row['api_channel']); ?></td>
          <td class="px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($row['api_username']); ?></td>
          <td class="px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($row['api_key']); ?></td>
          <td class="px-3 py-4 text-sm text-gray-500"><a class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" href="?delete=<?php echo $row['id']; ?>">删除</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
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
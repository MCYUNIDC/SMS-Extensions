<?php
// 数据库配置
$servername = "localhost";
$username = "sms"; // 数据库用户名
$password = "123456"; // 数据库密码
$dbname = "sms"; // 数据库名

// 管理员配置
$adminUsername = "admin";
$adminPassword = "123456"; // 默认管理员密码

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 初始化管理员账户
$adminQuery = "SELECT * FROM admin WHERE username = ?";
$stmt = $conn->prepare($adminQuery);
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // 如果管理员账户不存在，创建一个
    $createAdminQuery = "INSERT INTO admin (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($createAdminQuery);
    $stmt->bind_param("ss", $adminUsername, $adminPassword);
    $stmt->execute();
}

// 关闭准备语句
$stmt->close();
?>
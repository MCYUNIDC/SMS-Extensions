<?php
// 设置返回JSON格式
header('Content-Type: application/json');

// 包含数据库配置文件
include 'config.php';

// 获取请求参数
$key = $_REQUEST['key'] ?? null;
$phone = $_REQUEST['phone'] ?? null;
$content = $_REQUEST['content'] ?? null;

// 验证请求参数
if (!$key || !$phone || !$content) {
    http_response_code(400);
    echo json_encode(['code' => 400, 'msg' => '参数缺失'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^\d{11}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['code' => 400, 'msg' => '手机号格式不正确'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 验证管理员密钥（从数据库中获取管理员密码进行验证）
$adminQuery = "SELECT password FROM admin WHERE username = 'admin'";
$stmt = $conn->prepare($adminQuery);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['code' => 500, 'msg' => '数据库查询失败'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin || $admin['password'] !== $key) {
    http_response_code(403);
    echo json_encode(['code' => 403, 'msg' => '密钥无效'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
    exit;
}

// 从数据库中获取可用的通道（按某种排序规则选择一个）
$channelQuery = "SELECT * FROM channels ORDER BY id ASC LIMIT 1";
$stmt = $conn->prepare($channelQuery);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['code' => 500, 'msg' => '数据库查询失败'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->execute();
$channelResult = $stmt->get_result();

if ($channelResult->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['code' => 400, 'msg' => '没有可用的通道'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
    exit;
}

$channelInfo = $channelResult->fetch_assoc();
$apiUrl = $channelInfo['api_url']; // 从数据库中获取完整的 URL
$apiChannel = $channelInfo['api_channel'];
$apiUsername = $channelInfo['api_username'];
$apiKey = $channelInfo['api_key'];
$systemType = $channelInfo['system']; // 获取系统类型

// 确保 URL 以 / 结尾
if (substr($apiUrl, -1) !== '/') {
    $apiUrl .= '/';
}

// 根据系统类型添加路由点
if ($systemType == 1) {
    $apiUrl .= 'Ente_Send';
} elseif ($systemType == 2) {
    $apiUrl .= 'sendApi';
} else {
    http_response_code(400);
    echo json_encode(['code' => 400, 'msg' => '系统类型无效'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
    exit;
}

// 设置请求参数
$params = [
    'phone' => $phone,
    'content' => $content
];

// 根据系统类型设置不同的参数格式
if ($systemType == 1) { // 企通信短信系统
    $params['channel'] = $apiChannel;
    $params['user_name'] = $apiUsername;
    $params['user_key'] = $apiKey;
} elseif ($systemType == 2) { // 南逸短信系统
    $params['channel'] = $apiChannel;
    $params['username'] = $apiUsername;
    $params['key'] = $apiKey;
} else {
    http_response_code(400);
    echo json_encode(['code' => 400, 'msg' => '系统类型无效'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
    exit;
}

// 发送请求函数
function sendRequest($url, $params, $timeout = 5) {
    $queryString = http_build_query($params);
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
        ]
    ]);
    $response = @file_get_contents("$url?$queryString", false, $context);
    
    if ($response === FALSE) {
        // 调试信息
        error_log("Failed to get response from URL: $url?$queryString");
        return null;
    }
    
    return json_decode($response, true);
}

// 尝试发送请求到主平台
$responseData = sendRequest($apiUrl, $params);

if ($responseData && isset($responseData['code']) && $responseData['code'] == 1) {
    // 如果请求成功，返回响应
    echo json_encode(['code' => 1, 'msg' => '请求成功'], JSON_UNESCAPED_UNICODE);
} else {
    // 主平台请求失败，尝试备用平台
    $backupQuery = "SELECT * FROM channels WHERE id != ? AND system = ? ORDER BY id ASC";
    $stmt = $conn->prepare($backupQuery);
    $stmt->bind_param("ii", $channelInfo['id'], $systemType);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['code' => 500, 'msg' => '数据库查询失败'], JSON_UNESCAPED_UNICODE);
        $conn->close();
        exit;
    }

    $stmt->execute();
    $backupResult = $stmt->get_result();

    $success = false;
    $errorDetails = null;

    while ($backupChannelInfo = $backupResult->fetch_assoc()) {
        $backupApiUrl = $backupChannelInfo['api_url']; // 从数据库中获取完整的 URL

        // 确保 URL 以 / 结尾
        if (substr($backupApiUrl, -1) !== '/') {
            $backupApiUrl .= '/';
        }

        // 根据系统类型添加路由点
        if ($backupChannelInfo['system'] == 1) {
            $backupApiUrl .= 'Ente_Send';
        } elseif ($backupChannelInfo['system'] == 2) {
            $backupApiUrl .= 'sendApi';
        } else {
            continue; // 跳过无效的系统类型
        }

        $backupParams = [
            'phone' => $phone,
            'content' => $content
        ];

        // 根据备用平台系统类型设置不同的参数格式
        if ($backupChannelInfo['system'] == 1) { // 企通信短信系统
            $backupParams['channel'] = $backupChannelInfo['api_channel'];
            $backupParams['user_name'] = $backupChannelInfo['api_username'];
            $backupParams['user_key'] = $backupChannelInfo['api_key'];
        } elseif ($backupChannelInfo['system'] == 2) { // 南逸短信系统
            $backupParams['channel'] = $backupChannelInfo['api_channel'];
            $backupParams['username'] = $backupChannelInfo['api_username'];
            $backupParams['key'] = $backupChannelInfo['api_key'];
        } else {
            continue; // 跳过无效的系统类型
        }

        $backupResponseData = sendRequest($backupApiUrl, $backupParams);

        if ($backupResponseData && $backupResponseData['code'] == 1) {
            $success = true;
            break;
        } else {
            $errorDetails = $backupResponseData;
        }
    }

    if ($success) {
        echo json_encode(['code' => 1, 'msg' => '请求成功'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['code' => 500, 'msg' => '请求失败', 'details' => $errorDetails], JSON_UNESCAPED_UNICODE);
    }
}

// 关闭数据库连接
$stmt->close();
$conn->close();
?>

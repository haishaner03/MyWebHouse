<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$jsonFile = __DIR__ . '/messages.json';

if (!file_exists($jsonFile)) {
    $initialMessages = [
        ['id' => 1, 'nickname' => '管理员', 'content' => '欢迎来到留言板！在这里说点什么吧~', 'date' => '2026-09-14 10:00'],
        ['id' => 2, 'nickname' => '游客', 'content' => '这个网站好可爱，猫咪背景太治愈了！', 'date' => '2026-09-14 11:30']
    ];
    file_put_contents($jsonFile, json_encode($initialMessages, JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $messages = json_decode(file_get_contents($jsonFile), true);
    if (!is_array($messages)) $messages = [];
    echo json_encode(['success' => true, 'messages' => $messages], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!$input || empty($input['nickname']) || empty($input['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '昵称和内容不能为空'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nickname = htmlspecialchars(trim($input['nickname']), ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars(trim($input['content']), ENT_QUOTES, 'UTF-8');

    if (mb_strlen($nickname) > 20) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '昵称不能超过20字'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (mb_strlen($content) > 200) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '留言不能超过200字'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $messages = json_decode(file_get_contents($jsonFile), true);
    if (!is_array($messages)) $messages = [];

    $newId = count($messages) > 0 ? max(array_column($messages, 'id')) + 1 : 1;
    $now = date('Y-m-d H:i');

    $messages[] = [
        'id' => $newId,
        'nickname' => $nickname,
        'content' => $content,
        'date' => $now
    ];

    file_put_contents($jsonFile, json_encode($messages, JSON_UNESCAPED_UNICODE));

    echo json_encode(['success' => true, 'message' => '留言成功', 'id' => $newId], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => '不支持的请求方法'], JSON_UNESCAPED_UNICODE);
?>

<?php
require_once 'include/db.php';
header('Content-Type: application/json');

$sn = $_GET['sn'] ?? '';

if (empty($sn)) {
    echo json_encode(['code' => 400, 'msg' => '缺少订单号']);
    exit;
}

// 查询数据库
$stmt = $db->prepare("SELECT is_paid FROM orders WHERE order_sn = ?");
$stmt->execute([$sn]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if ($order) {
    echo json_encode([
        'code' => 200,
        'is_paid' => (int)$order['is_paid'] // 1 为已支付，0 为未支付
    ]);
} else {
    echo json_encode(['code' => 404, 'msg' => '订单不存在']);
}
?>
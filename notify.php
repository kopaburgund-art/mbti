<?php
// 引入数据库连接和支付配置
require_once 'include/db.php'; 
require_once 'api.php';
$config = require_once 'config.php';

$appsecret = $config['appsecret'];

// 1. 获取原始 POST 数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// // 记录日志（方便调试）
// file_put_contents('log.txt', date('Y-m-d H:i:s') . " 收到回调: " . $input . "\n", FILE_APPEND);

if (!$data) {
    echo 'fail'; exit;
}

// 2. 验证签名
$server_hash = YsmPayApi::HashSign($data, $appsecret);

if (isset($data['hash']) && $data['hash'] === $server_hash) {
    
    // 3. 签名验证通过，判断支付状态
    if ($data['state'] == 'SUCCESS') {
        $order_sn = $data['mch_orderid']; // 对应数据库中的 order_sn

        // ---------------------------------------------------------
        // 修改订单状态逻辑
        // ---------------------------------------------------------
        // 先检查订单是否已经是支付状态，避免重复处理
        $stmt = $db->prepare("SELECT is_paid FROM orders WHERE order_sn = ?");
        $stmt->execute([$order_sn]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order && $order['is_paid'] == 0) {
            // 更新为已支付状态 (is_paid = 1)
            $update = $db->prepare("UPDATE orders SET is_paid = 1 WHERE order_sn = ?");
            $update->execute([$order_sn]);
            
            // file_put_contents('log.txt', "订单 {$order_sn} 支付成功，数据库已更新。\n", FILE_APPEND);
        }
        
        echo 'success'; // 必须输出 success 告诉平台停止通知
    } else {
        echo 'state not success';
    }

} else {
    // file_put_contents('log.txt', "签名验证失败。本地计算: {$server_hash} \n", FILE_APPEND);
    echo 'sign error';
}
?>
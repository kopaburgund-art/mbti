<?php
require_once 'include/db.php'; // 必须包含数据库连接
require_once 'api.php';
$config = require_once 'config.php';

// 1. 获取外部传参 (从 process.php 跳转过来)
$order_no = $_GET['sn'] ?? ''; 
$amount   = $_GET['amount'] ?? 19.9; // 默认金额
$payType  = intval($_GET['payType'] ?? 11); // 11:支付宝, 12:微信

if (empty($order_no)) {
    die("错误：订单号缺失");
}

// 2. 准备支付参数
$total_fee = intval(round($amount * 100)); 
$data = [
    'appid'        => $config['appid'],
    'mch_orderid'  => $order_no,
    'description'  => 'MBTI性格测试报告',
    'total'        => $total_fee,
    'notify_url'   => $config['domain'] . '/notify.php',
    'nopay_url'    => $config['domain'] . '/index.php',
    'callback_url' => $config['domain'] . '/result.php?sn=' . $order_no,
    'time'         => time(),
    'nonce_str'    => md5(uniqid(microtime(true), true)),
    'payType'      => $payType
];

$data['sign'] = YsmPayApi::HashSign($data, $config['appsecret']);
$url = 'https://www.yishoumi.cn/u/payment';

// 3. 请求接口
try {
    $response = YsmPayApi::HttpPost($url, json_encode($data));
    $result = json_decode($response, true);
    var_dump($data)

    var_dump($response)

    if (!$result || !isset($result['url'])) {
        die("下单失败：" . ($result['msg'] ?? '接口异常'));
    }
    $payUrl = $result['url'];

    // 移动端：直接跳转支付
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (preg_match('/(android|iphone|ipod|mobile)/i', $ua)) {
        header("Location: " . $payUrl);
        exit;
    }
} catch (Exception $e) {
    die("支付请求出错：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>收银台 - 扫码支付</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { text-align: center; padding-top: 80px; font-family: 'Microsoft YaHei'; background: #f0f2f5; }
        .pay-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: inline-block; padding: 40px; }
        #qrcode { margin: 20px auto; padding: 10px; border: 1px solid #eee; display: inline-block; }
        .amount { color: #4F46E5; font-size: 36px; font-weight: 900; margin: 10px 0; }
        .tips { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="pay-card">
        <h3>请使用 <?php echo ($payType == 11 ? '支付宝' : '微信'); ?> 扫码</h3>
        <div class="amount">￥<?php echo number_format($amount, 2); ?></div>
        <div id="qrcode"></div>
        <p class="tips">支付成功后，系统将自动跳转至报告页</p>
    </div>

    <script>
        // 生成二维码
        new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $payUrl; ?>",
            width: 220,
            height: 220
        });

        // 轮询订单状态
        const sn = "<?php echo $order_no; ?>";
        const checkTimer = setInterval(function() {
            fetch('check_order.php?sn=' + sn)
                .then(res => res.json())
                .then(data => {
                    if (data.is_paid === 1) {
                        clearInterval(checkTimer);
                        window.location.href = 'result.php?sn=' + sn;
                    }
                });
        }, 2000); // 每2秒查询一次
    </script>
</body>
</html>
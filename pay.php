<?php
// 开启错误提示
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'api.php';
$config = require_once 'config.php';

// --- 工具函数：判断是否为移动端 ---
function isMobile() {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua);
}

// =====================================================
// 逻辑处理：创建订单并获取支付链接
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_no = $_GET['sn'] ?? ''; 
  $amount   = $_GET['amount'] ?? 19.9; // 默认金额

    $payType  = intval($_POST['payType'] ?? 11); // 必须是整数

    // 订单金额转为分
    $total_fee = intval(round($amount * 100)); 
    $order_no  = date('YmdHis') . rand(1000, 9999);

    // 构造请求参数 (严格对应文档)
    $data = [
        'appid'         => $config['appid'],
        'mch_orderid'   => $order_no,
        'description'   => $name,
        'total'         => $total_fee,
        'notify_url'    => $config['domain'] . '/mbti/notify.php',
        'nopay_url'     => $config['domain'] . '/mbti/pay.php',
        // 支付成功后，让用户带上订单号跳转回结果页
        'callback_url'  => $config['domain'] . '/mbti/result.php?order_no=' . $order_no,
        'time'          => time(),
        'nonce_str'     => md5(uniqid(microtime(true), true)),
        'payType'       => $payType
    ];

    // 生成签名
    $data['sign'] = YsmPayApi::HashSign($data, $config['appsecret']);

    $url = 'https://www.yishoumi.cn/u/payment';
    
    try {
        $response = YsmPayApi::HttpPost($url, json_encode($data));
        $result = json_decode($response, true);

        if ($result && isset($result['url'])) {
            $payUrl = $result['url'];
            
            // 移动端：直接跳转到支付链接 (H5支付)
            if (isMobile()) {
                header("Location: " . $payUrl);
                exit;
            }
            // PC端：留在本页显示二维码
        } else {
            die("下单失败：" . ($result['msg'] ?? '接口响应异常'));
        }
    } catch (Exception $e) {
        die("请求支付接口出错：" . $e->getMessage());
    }

    // --- 显示扫码页面 (仅限PC端) ---
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>扫码支付</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <style>
            body { text-align: center; padding-top: 50px; font-family: 'Microsoft YaHei', sans-serif; background-color: #f8f9fa; color: #333; }
            .pay-box { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); display: inline-block; padding: 40px; min-width: 320px; }
            #qrcode { margin: 25px auto; padding: 15px; background: #fff; border: 1px solid #eee; display: inline-block; }
            .amount { color: #d9534f; font-size: 28px; font-weight: bold; }
            .tips { color: #666; margin-bottom: 20px; }
            .loading-text { font-size: 14px; color: #007bff; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class="pay-box">
            <h3>请使用<?php echo ($payType == 11 ? '支付宝' : '微信'); ?>扫码</h3>
            <p class="tips">支付金额</p>
            <p class="amount">￥<?php echo number_format($amount, 2); ?></p>
            
            <div id="qrcode"></div>
            
            <p>支付完成后，系统将自动为您跳转</p>
            <p class="loading-text">等待支付中...</p>
        </div>

        <script>
            // 生成二维码
            new QRCode(document.getElementById("qrcode"), {
                text: "<?php echo $payUrl; ?>",
                width: 210,
                height: 210
            });

            /**
             * 关键补充：
             * 因为没有查询接口，PC端用户扫码后，浏览器无法实时知道支付成功。
             * 1. 理想情况：支付平台的支付页（payUrl）如果是全屏的，它会自动重定向到 callback_url。
             * 2. 当前情况：我们在自己页面生成的二维码，用户在手机支付。
             * * 建议方案：前端定时轮询你【自己的数据库】或者【Session】，
             * 看看 notify.php 是否已经把该订单标记为已支付。
             */
             setInterval(function() {
                // 这里可以写一个请求到你自己的 check_local_order.php
                // 如果后端返回支付成功，执行 window.location.href = 'result.php?order_no=...';
             }, 3000);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// =====================================================
// 模式：显示初始表单 (默认进入页面)
// =====================================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>收银台</title>
    <style>
        body { font-family: sans-serif; padding: 50px; text-align: center; background: #f4f7f6; }
        .form-container { display: inline-block; text-align: left; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="form-container">
        <form method="post">
            <h3 style="margin-top:0">MBTI 性格测试报告支付</h3>
            <div class="input-group">
                <label>支付金额 (元)</label>
                <input type="text" name="amount" value="0.01" readonly>
            </div>
            <div class="input-group">
                <label>支付方式</label>
                <select name="payType">
                    <option value="11">支付宝 (H5/扫码)</option>
                    <option value="12">微信支付 (扫码)</option>
                </select>
            </div>
            <button type="submit">立即支付</button>
        </form>
    </div>
</body>
</html>
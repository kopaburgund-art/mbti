<?php $sn = $_GET['sn']; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>分析已完成 - 请解锁报告</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#020617] text-white flex flex-col items-center justify-center p-6 min-h-screen">
    <div class="max-w-md w-full glass p-10 rounded-[3rem] text-center border-t-2 border-indigo-500">
        <div class="mb-6 inline-block p-4 bg-indigo-500/20 rounded-full">
            <svg class="w-10 h-10 text-indigo-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">您的性格图谱生成成功！</h2>
        <p class="text-gray-400 text-sm mb-8">根据您的 93 项核心数据，系统检测到您属于极其罕见的性格类型...</p>
        
        <div class="bg-white/5 rounded-2xl p-4 mb-8 filter blur-[2px] opacity-40 select-none">
            <div class="h-4 w-3/4 bg-white/20 mb-2"></div>
            <div class="h-4 w-full bg-white/20 mb-2"></div>
            <div class="h-20 w-20 mx-auto rounded-full border-4 border-white/20 mt-4"></div>
        </div>

        <div class="bg-indigo-600/20 p-6 rounded-2xl border border-indigo-500/30 mb-8 text-left">
            <div class="flex justify-between items-center">
                <span class="text-sm text-indigo-300 italic">限时优惠支付</span>
                <span class="text-3xl font-black">￥19.9</span>
            </div>
            <ul class="mt-4 text-[10px] text-gray-500 space-y-1">
                <li>• 包含：85% 匹配的职业清单</li>
                <li>• 包含：未来 3 年职业风险预警</li>
                <li>• 包含：社交、恋爱深度兼容分析</li>
            </ul>
        </div>

        <a href="result.php?sn=<?=$sn?>" class="block w-full bg-indigo-500 hover:bg-indigo-400 py-5 rounded-2xl font-bold text-lg shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
            立即解锁深度报告
        </a>
        <p class="mt-4 text-[10px] text-gray-700">支付即代表同意《隐私保护协议》</p>
    </div>
</body>
</html>
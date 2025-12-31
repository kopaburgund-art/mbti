

<?php 
session_start();
$sn = $_GET['sn'] ??    date('YmdHis') . rand(1000, 9999);
if (isset($_GET['reset'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分析已就绪 - 立即解锁您的深度性格报告</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        
        /* 延续结果页的斜切 Banner */
        .banner-clip { clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        
        /* 支付按钮的闪烁微光动画，确保它一眼就能被看到 */
        @keyframes shine {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }
        .btn-pay::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-25deg);
            animation: shine 4s infinite ease-in-out;
        }

        /* 模拟报告的模糊预览层 */
        .blur-bg {
            filter: blur(12px);
            opacity: 0.25;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen antialiased">
 <?php
 include 'include/nav.php';
 
 renderNav();?>
    <div class="absolute top-0 left-0 w-full h-72 bg-[#1e1b4b] banner-clip z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-lg mx-auto px-6 pt-12 pb-20">
        
        <div class="bg-white rounded-[2.5rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] overflow-hidden">
            
            <div class="pt-10 pb-6 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-50 rounded-3xl mb-4 rotate-3">
                    <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">您的性格图谱已生成</h1>
                <p class="text-slate-400 text-sm mt-2 font-medium">报告编号: <span class="text-slate-600 font-bold"><?= $sn ?></span></p>
            </div>

            <div class="relative mx-8 p-6 bg-slate-50 rounded-[2rem] border border-slate-100 overflow-hidden">
                <div class="blur-bg space-y-4">
                    <div class="h-4 w-2/3 bg-indigo-200 rounded-full"></div>
                    <div class="flex gap-2">
                        <div class="h-20 w-1/3 bg-indigo-100 rounded-2xl"></div>
                        <div class="h-20 w-2/3 bg-white rounded-2xl border border-slate-100"></div>
                    </div>
                    <div class="h-24 w-full bg-white rounded-2xl border border-slate-100"></div>
                </div>
                
                <div class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
                    <span class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase mb-3">VIP 解锁内容</span>
                    <h3 class="text-indigo-900 font-bold text-lg leading-tight">解锁 15 页深度解析报告</h3>
                    <p class="text-indigo-800/60 text-xs mt-2">包含：职业匹配、情感分析、成长建议</p>
                </div>
            </div>

            <div class="p-10">
                <div class="flex items-center justify-between mb-8 px-2">
                    <div>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">应付总额</p>
                        <div class="flex items-baseline">
                            <span class="text-indigo-600 text-xl font-bold">¥</span>
                            <span class="text-indigo-600 text-5xl font-black ml-1">19.9</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-slate-300 text-sm line-through font-bold">原价 ¥99.0</div>
                        <div class="text-rose-500 text-xs font-black mt-1">限时 2 折优惠</div>
                    </div>
                </div>

                <!-- <a href="result.php?sn=<?=$sn?>" class="btn-pay relative overflow-hidden block w-full bg-indigo-600 hover:bg-indigo-700 hover:shadow-2xl hover:shadow-indigo-200 text-white text-center py-6 rounded-2xl font-black text-xl transition-all active:scale-95 duration-300">
                    立即解锁完整报告
                </a> -->


                <form action="pay.php" method="GET">
                <input type="hidden" name="sn" value="<?= $sn ?>">
                <input type="hidden" name="amount" value="19.9">

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payType" value="11" checked class="peer sr-only">
                        <div class="p-4 border-2 rounded-2xl text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                            <span class="block text-sm font-bold text-slate-700">支付宝</span>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payType" value="12" class="peer sr-only">
                        <div class="p-4 border-2 rounded-2xl text-center peer-checked:border-green-600 peer-checked:bg-green-50 transition-all">
                            <span class="block text-sm font-bold text-slate-700">微信支付</span>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-pay relative overflow-hidden block w-full bg-indigo-600 hover:bg-indigo-700 hover:shadow-2xl hover:shadow-indigo-200 text-white text-center py-6 rounded-2xl font-black text-xl transition-all active:scale-95 duration-300">
                    立即支付解锁报告
                </button>
            </form>
 <a href="?reset=1"
               class="text-xs text-gray-300 hover:text-red-400 transition">
                重置测试
            </a>
                <div class="mt-8 flex flex-col items-center space-y-4">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <svg class="w-3 h-3 mr-1 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 1.55l7.834 3.35a1 1 0 01.666.936v6.13a1 1 0 01-.19.591L11 18.25V11a1 1 0 00-1-1H3.19a1 1 0 01-.591-.19L1 12.01V5.836a1 1 0 01.666-.936z" clip-rule="evenodd"></path></svg>
                            加密传输
                        </div>
                        <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <svg class="w-3 h-3 mr-1 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                            专业测评
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-300">支付即代表您同意《用户服务与隐私协议》</p>
                </div>
            </div>
        </div>
        
        <p class="text-center mt-12 text-slate-400 text-[10px] font-bold uppercase tracking-[0.4em] opacity-40">
            Securely Processed by MBTI Engine
        </p>
    </div>

</body>
</html>
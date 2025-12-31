<?php
 
function renderNav() {
    echo '
    <!-- ===================== Nav（原样保留） ===================== -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="font-black text-xl tracking-tight text-indigo-600">
                MBTI PRO
            </div>
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-500">
                <a href="index.php" class="text-indigo-600 font-bold">性格测试</a>
                <a href="#" class="hover:text-indigo-600 transition">MBTI 介绍</a>
                <a href="#" class="hover:text-indigo-600 transition">类型说明</a>
                <a href="#" class="hover:text-indigo-600 transition">专业版</a>
            </nav>
        </div>
    </header>
    ';
}

?>
<?php
// setup.php
$db = new PDO('sqlite:db/mbti_pro.sqlite');
$db->exec("CREATE TABLE IF NOT EXISTS questions (id INTEGER PRIMARY KEY AUTOINCREMENT, question_text TEXT, dimension TEXT, option_a TEXT, option_b TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY AUTOINCREMENT, order_sn TEXT UNIQUE, mbti_result TEXT, is_paid INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$db->exec("CREATE TABLE IF NOT EXISTS mbti_types (code TEXT PRIMARY KEY, title TEXT, tagline TEXT, description TEXT, careers TEXT, radar_data TEXT)");

// 预置题目（建议实际增加到 93 题）
$db->exec("DELETE FROM questions");
$qs = [
    ['在社交聚会中，你通常是：', 'EI', '充满活力地与多人交流 (E)', '安静地观察或与熟人聊天 (I)'],
    ['你更倾向于通过哪种方式理解世界：', 'SN', '关注直觉和未来的各种可能性 (N)', '关注当下的事实和具体的细节 (S)'],
    ['面对冲突时，你最先考虑的是：', 'TF', '逻辑对错与公正原则 (T)', '他人的感受与人际和谐 (F)'],
    ['你更喜欢的旅行方式是：', 'JP', '提前做好详细的行程计划 (J)', '随心所欲，走到哪算哪 (P)']
];
$stmt = $db->prepare("INSERT INTO questions (question_text, dimension, option_a, option_b) VALUES (?,?,?,?)");
foreach($qs as $q) $stmt->execute($q);

// 预置一个人格样本（以 INTJ 为例）
$careers = json_encode(["系统架构师", "战略顾问", "科研专家", "独立开发者"]);
$radar = json_encode([95, 40, 85, 90, 75, 80]); // 对应雷达图 6 个维度
$db->exec("INSERT OR REPLACE INTO mbti_types VALUES ('INTJ', '建筑师', '独行在智慧荒原的战略家', '你天生拥有一种好奇心，但通常不会浪费精力在无意义的事情上...', '$careers', '$radar')");

echo "✅ 商业数据库环境初始化完成！";
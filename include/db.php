<?php


// 使用 MySQL 数据库连接
$host = 'mbti123q.mysql.cnhk.rds.aliyuncs.com'; // 数据库主机
$dbname = 'mbti'; // 数据库名称
$username = 'mbti_user'; // 数据库用户名
$password = '8954036abc!@#'; // 数据库密码
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// 创建 PDO 实例
try {
    $db = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

?>
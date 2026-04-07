<?php
$host = getenv("MYSQLHOST") ?: getenv("MYSQL_HOST");
$user = getenv("MYSQLUSER") ?: getenv("MYSQL_USER");
$pass = getenv("MYSQLPASSWORD") ?: getenv("MYSQL_PASSWORD");
$db   = getenv("MYSQLDATABASE") ?: getenv("MYSQL_DATABASE");
$port = (int)(getenv("MYSQLPORT") ?: getenv("MYSQL_PORT") ?: 3306);

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

echo "Kết nối MySQL thành công!";
?>

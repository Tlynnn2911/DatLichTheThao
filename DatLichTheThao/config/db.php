<?php
// Lấy giá trị và kiểm tra, nếu không có thì thử cả 2 định dạng (có và không có gạch dưới)
$host = getenv("MYSQLHOST") ?: getenv("MYSQL_HOST");
$user = getenv("MYSQLUSER") ?: getenv("MYSQL_USER");
$pass = getenv("MYSQLPASSWORD") ?: getenv("MYSQL_PASSWORD");
$db   = getenv("MYSQLDATABASE") ?: getenv("MYSQL_DATABASE");
$port = getenv("MYSQLPORT") ?: getenv("MYSQL_PORT") ?: 3306;

// Kiểm tra xem có biến nào bị trống không
if (!$host || !$user || !$db) {
    die("Lỗi: Thiếu cấu hình biến môi trường trên Railway (Host, User hoặc Database bị trống).");
}

// Bật chế độ báo lỗi chi tiết
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db, (int)$port);
    $conn->set_charset("utf8mb4");
    // echo "Kết nối thành công!"; // Mở ra để test, xong thì đóng lại
} catch (mysqli_sql_exception $e) {
    // In ra lỗi chi tiết để biết chính xác nó đang sai ở đâu
    die("Lỗi kết nối thực tế: " . $e->getMessage());
}
?>
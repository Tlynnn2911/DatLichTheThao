<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    if (!isset($_SESSION['user_id_chu']) && !isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $id = intval($_POST['id']);
    $check = $conn->query("SELECT id FROM dat_lich WHERE id = $id AND trang_thai_don = 'cho_xac_nhan'");

    if ($check->num_rows > 0) {
        $sql = "DELETE FROM dat_lich WHERE id = $id";

        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Đơn này không thể hủy hoặc không tồn tại.']);
    }
}

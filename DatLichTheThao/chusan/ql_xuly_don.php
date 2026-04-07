<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id_chu'])) {
    header("Location: ../auth/dangnhapchu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $current_tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'choxn';

    if ($booking_id > 0 && !empty($action)) {
        $new_status = '';
        $msg_success = '';

        switch ($action) {
            case 'confirm': // Xác nhận đơn
                $new_status = 'da_xac_nhan';
                $msg_success = 'Đã xác nhận đơn đặt sân!';
                break;
            case 'reject':  // Từ chối đơn
                $new_status = 'da_huy';
                $msg_success = 'Đã từ chối đơn đặt sân!';
                break;
            case 'cancel':  // Hủy đơn
                $new_status = 'da_huy';
                $msg_success = 'Đã hủy đơn đặt sân!';
                break;
        }

        if (!empty($new_status)) {
            $sql = "UPDATE dat_lich SET trang_thai_don = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_status, $booking_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = $msg_success;
            } else {
                $_SESSION['error'] = 'Lỗi hệ thống: ' . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = 'Dữ liệu không hợp lệ!';
    }
}

header("Location: ../chusan/quanly.php?tab=" . $current_tab);
exit();

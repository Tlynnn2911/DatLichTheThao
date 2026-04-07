<?php
session_start();
require_once '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id_chu'])) {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $danh_gia_id = isset($_POST['danh_gia_id']) ? intval($_POST['danh_gia_id']) : 0;
    $phan_hoi = isset($_POST['phan_hoi']) ? trim($_POST['phan_hoi']) : '';

    if ($danh_gia_id > 0) {
        // Cập nhật phản hồi vào bảng danh_gia
        $stmt = $conn->prepare("UPDATE danh_gia SET phan_hoi_chu_san = ?, ngay_phan_hoi = NOW() WHERE id = ?");
        $stmt->bind_param("si", $phan_hoi, $danh_gia_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Đã cập nhật phản hồi thành công!";
        } else {
            $_SESSION['error'] = "Lỗi hệ thống, vui lòng thử lại.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ.";
    }
}

// Quay lại trang quản lý tab đánh giá
header("Location: quanly.php?tab=danhgia");
exit();
?>
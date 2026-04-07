<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_reset'])) {

    $account = trim($_POST['account']);

    // Kiểm tra đầu vào
    if (empty($account)) {
        echo "<script>alert('Vui lòng nhập thông tin!'); window.history.back();</script>";
        exit;
    }

    // Tìm tài khoản 
    $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ? OR sdt = ?");
    $stmt->bind_param("ss", $account, $account);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $user_id = $user['id'];

        //Tạo mật khẩu ngẫu nhiên
        $pass_raw = substr(str_shuffle("0123456789"), 0, 8);

        // MÃ HÓA MẬT KHẨU 
        $hashed_pass = password_hash($pass_raw, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu vào database
        $update_stmt = $conn->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_pass, $user_id);

        if ($update_stmt->execute()) {
            // Thông báo và hiển thị mật khẩu cho khách biết
            echo "<script>
                alert('Thành công! Mật khẩu mới của bạn là: $pass_raw \\nHãy đăng nhập và đổi mật khẩu ngay.');
                window.location.href = 'dangnhap.php'; 
            </script>";
        } else {
            echo "<script>alert('Lỗi hệ thống!'); window.history.back();</script>";
        }
        $update_stmt->close();
    } else {
        echo "<script>alert('Không tìm thấy tài khoản!'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}

<?php
session_start();
require_once '../config/db.php';

//Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Vui lòng đăng nhập lại!'); window.location.href='../dangnhap.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    //Lấy dữ liệu từ form
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Kiểm tra khớp mật khẩu mới
    if ($new_pass !== $confirm_pass) {
        echo "<script>alert('Mật khẩu nhập lại không khớp!'); window.history.back();</script>";
        exit;
    }

    // Truy vấn lấy mật khẩu cũ từ db
    $sql = "SELECT mat_khau FROM nguoi_dung WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        //KIỂM TRA MẬT KHẨU HIỆN TẠI
        if (!password_verify($current_pass, $user['mat_khau'])) {
            echo "<script>alert('Mật khẩu hiện tại không chính xác!'); window.history.back();</script>";
            exit;
        }

        // MÃ HÓA MẬT KHẨU MỚI
        $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);

        // 6. Cập nhật mật khẩu vào database
        $update_sql = "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);

        // bind biến $hashed_new_pass
        $update_stmt->bind_param("si", $hashed_new_pass, $user_id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Đổi mật khẩu thành công!'); window.location.href='../taikhoan.php?tab=password';</script>";
        } else {
            echo "<script>alert('Lỗi: Không thể cập nhật mật khẩu.'); window.history.back();</script>";
        }
        $update_stmt->close();
    } else {
        echo "<script>alert('Tài khoản không tồn tại!'); window.history.back();</script>";
    }
    $stmt->close();
    $conn->close();
}

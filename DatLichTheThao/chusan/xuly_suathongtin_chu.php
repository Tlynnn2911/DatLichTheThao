<?php
session_start();
require_once '../config/db.php';

// 1. Kiểm tra đăng nhập (Dùng đúng biến session của Chủ sân)
if (!isset($_SESSION['user_id_chu'])) {
    $_SESSION['error'] = 'Vui lòng đăng nhập!';
    header("Location: ../dangnhapchu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id_chu'];
    
    // Lấy dữ liệu và làm sạch
    $ho_ten = trim($_POST['ho_ten']);
    $email  = trim($_POST['email']);

    // --- BƯỚC 1: KIỂM TRA DỮ LIỆU ĐẦU VÀO ---
    if (empty($ho_ten) || empty($email)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ họ tên và email!';
        header("Location: quanly.php?tab=profile_user");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Định dạng email không hợp lệ!';
        header("Location: quanly.php?tab=profile_user");
        exit();
    }

    // --- BƯỚC 2: KIỂM TRA TRÙNG EMAIL ---
    // Tìm xem email này có ai dùng chưa (trừ chính mình)
    $check_sql = "SELECT id FROM nguoi_dung WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['error'] = 'Email này đã được sử dụng bởi tài khoản khác!';
        header("Location: quanly.php?tab=profile_user");
        exit();
    }
    $stmt_check->close();

    // --- BƯỚC 3: CẬP NHẬT DỮ LIỆU ---
    $sql = "UPDATE nguoi_dung SET ho_ten = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $ho_ten, $email, $user_id);

    if ($stmt->execute()) {
        // Cập nhật lại tên trong Session để hiển thị ngay trên Menu
        $_SESSION['user_name_chu'] = $ho_ten;
        
        // Tạo thông báo thành công
        $_SESSION['success'] = 'Cập nhật thông tin thành công!';
        header("Location: quanly.php?tab=profile_user");
        exit();
    } else {
        // Tạo thông báo thất bại
        $_SESSION['error'] = 'Lỗi hệ thống, không thể cập nhật!';
        header("Location: quanly.php?tab=profile_user");
        exit();
    }
    $stmt->close();
} else {
    // Nếu truy cập trực tiếp file này mà không POST
    header("Location: quanly.php?tab=profile_user");
    exit();
}

$conn->close();
?>
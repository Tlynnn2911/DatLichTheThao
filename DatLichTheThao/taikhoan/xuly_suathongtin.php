<?php
session_start();
require_once '../config/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Vui lòng đăng nhập!';
    header("Location: ../dangnhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    // Lấy dữ liệu và làm sạch cơ bản
    $ho_ten  = trim($_POST['ho_ten']);
    $email   = trim($_POST['email']);

    // --- BƯỚC VALIDATE (KIỂM TRA DỮ LIỆU) ---
    
    // 1. Kiểm tra rỗng
    if (empty($ho_ten) || empty($email)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ họ tên và email!';
        header("Location: ../taikhoan.php?tab=profile");
        exit();
    }

    // 2. Kiểm tra định dạng Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Định dạng email không hợp lệ!';
        header("Location: ../taikhoan.php?tab=profile");
        exit();
    }

    // --- BƯỚC KIỂM TRA TRÙNG EMAIL ---
    // Tìm xem có ai ĐANG DÙNG email này mà KHÔNG PHẢI là tôi
    $check_sql = "SELECT id FROM nguoi_dung WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['error'] = 'Email này đã được sử dụng bởi tài khoản khác!';
        // SỬA LỖI: Quay về trang tài khoản, không phải trang đăng ký
        header("Location: ../taikhoan.php?tab=profile"); 
        exit();
    }
    $stmt_check->close();

    // --- BƯỚC CẬP NHẬT ---
    $sql = "UPDATE nguoi_dung SET ho_ten = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $ho_ten, $email, $user_id);

    if ($stmt->execute()) {
        // Cập nhật lại session để hiển thị tên mới ngay lập tức
        $_SESSION['user_name'] = $ho_ten; 
        
        // Nếu người dùng đổi email, có thể bạn muốn cập nhật luôn session email nếu có lưu
        if (isset($_SESSION['user_email'])) {
            $_SESSION['user_email'] = $email;
        }

        $_SESSION['success'] = 'Cập nhật thông tin thành công!';
        header("Location: ../taikhoan.php?tab=profile");
        exit();
    } else {
        // Ghi log lỗi server để debug nếu cần (không hiện cho user)
        // error_log("Update error: " . $conn->error);
        
        $_SESSION['error'] = 'Đã có lỗi xảy ra, vui lòng thử lại sau!';
        header("Location: ../taikhoan.php?tab=profile");
        exit();
    }
    $stmt->close();
} else {
    // Nếu ai đó cố truy cập file này trực tiếp mà không post dữ liệu
    header("Location: ../taikhoan.php");
    exit();
}

$conn->close();
?>
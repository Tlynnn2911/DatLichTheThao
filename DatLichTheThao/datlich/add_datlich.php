<?php
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để đặt sân.";
        header("Location: ../taikhoan/dangnhap.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $co_so_id = intval($_POST['co_so_id']);
    $san_id = intval($_POST['san_id']);
    $date = $_POST['date'];
    $slots = explode(',', $_POST['slots']);
    $total_price = floatval($_POST['total_price']);


    // Chuẩn bị dữ liệu giờ
    sort($slots);
    $start_time = $slots[0];
    // Tính giờ kết thúc 
    $end_time_ts = strtotime("$date " . end($slots)) + 1800;
    $end_time = date('H:i', $end_time_ts);
    $ma_don = strtoupper(uniqid('BOOK'));

    // Insert vào bảng dat_lich
    $sql_book = "INSERT INTO dat_lich (ma_dat_lich, nguoi_dung_id, co_so_id, san_id, ngay_dat, gio_bat_dau, gio_ket_thuc, tong_tien, trang_thai_don) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan')";

    $stmt = $conn->prepare($sql_book);
    $stmt->bind_param("siiisssd", $ma_don, $user_id, $co_so_id, $san_id, $date, $start_time, $end_time, $total_price);

    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;

        // Gán session và chuyển hướng
        $_SESSION['success'] = "Đặt lịch thành công! Đơn hàng đang chờ xác nhận.";
        header("Location: ../taikhoan.php?tab=history");
        exit();
    } else {
        // Lỗi SQL
        $_SESSION['danger'] = "Lỗi hệ thống: " . $conn->error;
        header("Location: ../taikhoan.php?tab=history");
        exit();
    }
}

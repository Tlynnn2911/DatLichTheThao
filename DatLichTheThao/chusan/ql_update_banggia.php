<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id_chu'])) {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['co_so_id'])) {
    $co_so_id = intval($_POST['co_so_id']);

    // Lấy dữ liệu từ form
    $gio_bat_dau_toi = $_POST['gio_bat_dau_toi'];
    $gia_sang = intval(str_replace([',', '.'], '', $_POST['gia_sang']));
    $gia_toi = intval(str_replace([',', '.'], '', $_POST['gia_toi']));
    $gia_cuoi_tuan = intval(str_replace([',', '.'], '', $_POST['gia_cuoi_tuan']));

    // Cập nhật vào Database
    $stmt = $conn->prepare("UPDATE co_so SET gio_bat_dau_toi = ?, gia_sang = ?, gia_toi = ?, gia_cuoi_tuan = ? WHERE id = ?");
    $stmt->bind_param("siiii", $gio_bat_dau_toi, $gia_sang, $gia_toi, $gia_cuoi_tuan, $co_so_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Cập nhật bảng giá thành công!";
    } else {
        $_SESSION['error'] = "Lỗi cập nhật: " . $conn->error;
    }

    header("Location: quanly.php?tab=price");
    exit();
}

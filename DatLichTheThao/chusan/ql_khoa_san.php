<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id_chu'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action == 'lock_slots') {
        $date = $_POST['date'];
        $slots = json_decode($_POST['slots'], true); // Mảng các object {san_id, time}

        if (empty($slots)) {
            echo json_encode(['status' => 'error', 'message' => 'Không có ô nào được chọn']);
            exit();
        }

        $success_count = 0;
        $stmt = $conn->prepare("INSERT INTO khoa_san_theo_gio (san_id, ngay_khoa, gio_bat_dau, gio_ket_thuc) VALUES (?, ?, ?, ?)");

        foreach ($slots as $slot) {
            $san_id = $slot['san_id'];
            $start_time = $slot['time'];
            $end_timestamp = strtotime("$date $start_time") + 1800; 
            $end_time = date('H:i:s', $end_timestamp);         
            $formatted_start = date('H:i:s', strtotime("$date $start_time"));
            $stmt->bind_param("isss", $san_id, $date, $formatted_start, $end_time);
            if ($stmt->execute()) {
                $success_count++;
            }
        }

        if ($success_count > 0) {
            $_SESSION['success'] = "Đã khóa thành công $success_count khung giờ.";
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi lưu dữ liệu']);
        }
    }
    // mở
    elseif ($action == 'unlock_slots') {
        $date = $_POST['date'];
        $slots = json_decode($_POST['slots'], true);

        if (empty($slots)) {
            echo json_encode(['status' => 'error', 'message' => 'Không có dữ liệu']);
            exit();
        }

        $count = 0;
        $stmt = $conn->prepare("DELETE FROM khoa_san_theo_gio WHERE san_id = ? AND ngay_khoa = ? AND gio_bat_dau = ?");

        foreach ($slots as $slot) {
            $san_id = $slot['san_id'];
            $start_fmt = date('H:i:s', strtotime("$date " . $slot['time']));

            $stmt->bind_param("iss", $san_id, $date, $start_fmt);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $count++;
                }
            }
        }
        echo json_encode(['status' => 'success', 'count' => $count]);
    }
}
?>
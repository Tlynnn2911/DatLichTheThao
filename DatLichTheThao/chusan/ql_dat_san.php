<?php
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra quyền chủ sân
if (!isset($_SESSION['user_id_chu'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// --- XỬ LÝ: PREVIEW (Tính tiền & Hiển thị thông tin) ---
if ($action == 'preview') {
    $san_id = intval($_POST['san_id']);
    $date = $_POST['date'];
    $slots = json_decode($_POST['slots']); // Mảng các giờ ["17:00", "17:30"]

    if (empty($slots)) {
        echo json_encode(['status' => 'error', 'message' => 'Chưa chọn giờ']);
        exit();
    }

    // Lấy thông tin sân và giá
    $sql = "SELECT s.ten_san, s.he_so_gia, 
                   c.gia_sang, c.gia_toi, c.gia_cuoi_tuan, c.gio_bat_dau_toi
            FROM san s 
            JOIN co_so c ON s.co_so_id = c.id 
            WHERE s.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $san_id);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();

    if (!$info) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy sân']);
        exit();
    }

    // Logic tính tiền (Giống file chitiet.php)
    $is_weekend = (date('N', strtotime($date)) >= 6); // T7, CN
    $evening_start = strtotime("$date " . ($info['gio_bat_dau_toi'] ?: '17:00:00'));
    $total_price = 0;

    foreach ($slots as $slot) {
        $slot_ts = strtotime("$date $slot");
        $base_price = 0;
        
        if ($is_weekend) {
            $base_price = $info['gia_cuoi_tuan'];
        } else {
            $base_price = ($slot_ts >= $evening_start) ? $info['gia_toi'] : $info['gia_sang'];
        }
        
        // Mỗi slot là 30p, nên giá chia đôi * hệ số
        $total_price += ($base_price * $info['he_so_gia']) / 2;
    }

    // Xác định khung giờ bắt đầu - kết thúc
    sort($slots);
    $start_time = $slots[0];
    $end_time_ts = strtotime("$date " . end($slots)) + 1800; // +30 phút
    $end_time = date('H:i', $end_time_ts);

    echo json_encode([
        'status' => 'success',
        'san_name' => $info['ten_san'],
        'formatted_date' => date('d/m/Y', strtotime($date)),
        'time_range' => "$start_time - $end_time",
        'total_price' => $total_price,
        'total_price_vnd' => number_format($total_price) . ' VNĐ'
    ]);
    exit();
}

// --- XỬ LÝ: BOOK (Lưu vào Database) ---
if ($action == 'book') {
    $san_id = intval($_POST['san_id']);
    $date = $_POST['date'];
    $slots = json_decode($_POST['slots']);
    $sdt_vanglai = trim($_POST['sdt']);
    $co_so_id = $_SESSION['current_co_so_id']; // Lấy từ Session quản lý

    if (empty($slots) || empty($sdt_vanglai)) {
        echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu']);
        exit();
    }

    // 1. Tính toán lại Tổng tiền (Tránh gian lận từ client)
    // (Copy lại logic tính tiền ở trên hoặc gom thành hàm chung)
    $sql_info = "SELECT s.he_so_gia, c.gia_sang, c.gia_toi, c.gia_cuoi_tuan, c.gio_bat_dau_toi
                 FROM san s JOIN co_so c ON s.co_so_id = c.id WHERE s.id = ?";
    $stmt = $conn->prepare($sql_info);
    $stmt->bind_param("i", $san_id);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();

    $is_weekend = (date('N', strtotime($date)) >= 6);
    $evening_start = strtotime("$date " . ($info['gio_bat_dau_toi'] ?: '17:00:00'));
    $total_price = 0;

    foreach ($slots as $slot) {
        $slot_ts = strtotime("$date $slot");
        $base_price = ($is_weekend) ? $info['gia_cuoi_tuan'] : (($slot_ts >= $evening_start) ? $info['gia_toi'] : $info['gia_sang']);
        $total_price += ($base_price * $info['he_so_gia']) / 2;
    }

    // 2. Xác định giờ bắt đầu và kết thúc tổng thể
    sort($slots);
    $gio_bat_dau = $slots[0] . ":00"; // Định dạng HH:MM:SS
    $gio_ket_thuc = date('H:i:s', strtotime("$date " . end($slots)) + 1800);

    // 3. Tạo mã đặt lịch
    $ma_dat_lich = "BOOK" . strtoupper(uniqid());

    // 4. Insert vào DB
    // Lưu ý: nguoi_dung_id để NULL vì là khách vãng lai
    // trang_thai_don = 'hoan_thanh' vì chủ sân tự đặt
    $sql_insert = "INSERT INTO dat_lich (ma_dat_lich, nguoi_dung_id, co_so_id, san_id, ngay_dat, gio_bat_dau, gio_ket_thuc, tong_tien, trang_thai_don, sdt_vanglai) 
                   VALUES (?, NULL, ?, ?, ?, ?, ?, ?, 'da_xac_nhan', ?)";
    
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("siisssds", $ma_dat_lich, $co_so_id, $san_id, $date, $gio_bat_dau, $gio_ket_thuc, $total_price, $sdt_vanglai);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $conn->error]);
    }
    exit();
}
?>
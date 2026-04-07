<?php
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!isset($_SESSION['user_id_chu'])) {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT dl.*, s.ten_san, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.tinh_thanh, c.hotline,
               u.ho_ten as ten_kh_tv, u.sdt as sdt_kh_tv
        FROM dat_lich dl 
        JOIN san s ON dl.san_id = s.id 
        JOIN co_so c ON dl.co_so_id = c.id 
        LEFT JOIN nguoi_dung u ON dl.nguoi_dung_id = u.id
        WHERE dl.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Không tìm thấy đơn này!'); window.history.back();</script>";
    exit;
}

$row = $result->fetch_assoc();

$ten_hien_thi = "";
$sdt_hien_thi = "";

if (!empty($row['nguoi_dung_id'])) {
    //là thành viên có tài khoản
    $ten_hien_thi = $row['ten_kh_tv'];
    $sdt_hien_thi = $row['sdt_kh_tv'];
} else {
    //chủ sân tự đặt
    $ten_hien_thi = "Khách";
    $sdt_hien_thi = $row['sdt_vanglai'];
}

$ma_don = isset($row['ma_dat_lich']) ? $row['ma_dat_lich'] : 'BOOK-' . $row['id'];
$ngay_dat = date('d/m/Y', strtotime($row['ngay_dat']));
$gio_choi = date('H:i', strtotime($row['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($row['gio_ket_thuc']));
$hours = round(abs(strtotime($row['gio_ket_thuc']) - strtotime($row['gio_bat_dau'])) / 3600, 1);

// Xử lý trạng thái và nút bấm
$status_class = "text-yellow";
$status_text = "Chờ xác nhận";
$show_actions_confirm = false; 
$show_action_cancel = false;   
$back_tab = 'choxn';

switch ($row['trang_thai_don']) {
    case 'cho_xac_nhan':
        $status_text = "Chờ xác nhận";
        $status_class = "text-yellow";
        $show_actions_confirm = true;
        $back_tab = 'choxn';
        break;
    case 'da_xac_nhan':
        $status_text = "Đã xác nhận";
        $status_class = "text-green";
        $show_action_cancel = true;
        $back_tab = 'daxn';
        break;
    case 'hoan_thanh':
        $status_text = "Hoàn thành";
        $status_class = "text-blue";
        $back_tab = 'daht';
        break;
    case 'da_huy':
        $status_text = "Đã hủy";
        $status_class = "text-red";
        break;
}

$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($ten_hien_thi) . "&background=random&color=fff";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOOKSAN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="detail-page-body">
    <div class="detail-header-bar">
        <a href="quanly.php?tab=<?php echo $back_tab; ?>" class="detail-back-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="detail-page-title">Chi tiết đặt lịch</div>
    </div>

    <div class="detail-container">
        <div class="detail-user-card">
            <img src="<?php echo $avatar_url; ?>" class="detail-avatar">
            <div class="detail-user-info">
                <div>Khách hàng: <b><?php echo $ten_hien_thi; ?></b></div>
                <div>SĐT: <b><a href="tel:<?php echo $sdt_hien_thi; ?>"><?php echo $sdt_hien_thi; ?></a></b></div>
            </div>
        </div>

        <div class="detail-info-section">
            <div class="detail-section-title"><i class="fas fa-clipboard-list"></i> Thông tin</div>

            <div class="detail-row">Mã lịch đặt: <span class="text-yellow">#<?php echo $ma_don; ?></span></div>
            <div class="detail-row">Trạng thái: <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
            <div class="detail-row">Tên CLB: <b><?php echo $row['ten_co_so']; ?></b></div>
            <div class="detail-row">Địa chỉ: <b><?php echo $row['dia_chi_cu_the'] . ', ' . $row['quan_huyen'] . ', ' . $row['tinh_thanh']; ?></b></div>
            <div class="detail-row">Số điện thoại sân: <b><?php echo $row['hotline']; ?></a></b></div>
            <div class="detail-row" style="margin-top: 15px;">Ngày: <b><?php echo $ngay_dat; ?></b></div>
            <div class="detail-row">- <?php echo $row['ten_san']; ?>: <b><?php echo $gio_choi; ?></b></div>
            <div class="detail-row">Tổng giờ: <b><?php echo $hours; ?>h</b></div>
            <hr class="detail-divider">
            <div class="detail-row price">
                <span>Tổng tiền:</span>
                <span class="text-bold" style="font-size: 16px;"><?php echo number_format($row['tong_tien']); ?> VNĐ</span>
            </div>

    <?php if ($show_actions_confirm): ?>
        <div class="action-bar">
            <form action="ql_xuly_don.php" method="POST" onsubmit="return confirm('Bạn muốn từ chối đơn này?');">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="current_tab" value="<?php echo $back_tab; ?>">
                <button type="submit" class="btn-large btn-reject"><i class="fas fa-times"></i> Từ chối</button>
            </form>

            <form action="ql_xuly_don.php" method="POST">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="action" value="confirm">
                <input type="hidden" name="current_tab" value="<?php echo $back_tab; ?>">
                <button type="submit" class="btn-large btn-confirm"><i class="fas fa-check"></i> Xác nhận</button>
            </form>
        </div>

    <?php elseif ($show_action_cancel): ?>
        <div class="action-bar">
            <form action="ql_xuly_don.php" method="POST" style="width:100%; display:flex; justify-content:center;" onsubmit="return confirm('CẢNH BÁO: Hủy đơn đã xác nhận?');">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="current_tab" value="<?php echo $back_tab; ?>">
                <button type="submit" class="btn-large btn-cancel-lg"><i class="fas fa-ban"></i> Hủy đơn này</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
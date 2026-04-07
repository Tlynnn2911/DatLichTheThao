<?php
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: taikhoan/dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy dữ liệu
$sql = "SELECT dl.*, s.ten_san, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.tinh_thanh, c.hotline,
               u.ho_ten as ten_kh, u.sdt as sdt_kh 
        FROM dat_lich dl 
        JOIN san s ON dl.san_id = s.id 
        JOIN co_so c ON dl.co_so_id = c.id 
        JOIN nguoi_dung u ON dl.nguoi_dung_id = u.id
        WHERE dl.id = ? AND dl.nguoi_dung_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>showNotification('Không tìm thấy lịch đặt này!'); window.location.href='taikhoan.php?tab=history';</script>";
    exit;
}

$row = $result->fetch_assoc();

// Xử lý hiển thị
$ma_don = isset($row['ma_dat_lich']) ? $row['ma_dat_lich'] : 'BOOK-' . $row['id'];
$ngay_dat = date('d/m/Y', strtotime($row['ngay_dat']));
$gio_choi = date('H:i', strtotime($row['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($row['gio_ket_thuc']));

// Tính tổng giờ
$hours = round(abs(strtotime($row['gio_ket_thuc']) - strtotime($row['gio_bat_dau'])) / 3600, 1);

// Xử lý trạng thái
$status_class = "text-yellow";
$status_text = "Chờ xác nhận";
$show_cancel_btn = false;
$show_contact_btn = false;

switch ($row['trang_thai_don']) {
    case 'cho_xac_nhan':
        $status_text = "Chờ xác nhận";
        $status_class = "text-yellow";
        $show_cancel_btn = true;
        break;
    case 'da_xac_nhan':
        $status_text = "Đã xác nhận";
        $status_class = "text-green";
        $show_contact_btn = true;
        break;
    case 'hoan_thanh':
        $status_text = "Hoàn thành";
        $status_class = "text-blue";
        break;
    case 'da_huy':
        $status_text = "Đã hủy";
        $status_class = "text-red";
        break;
}

$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['ten_kh']) . "&background=random&color=fff";
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
    <div id="notification-area">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>  
    
    <div class="detail-header-bar">
        <a href="../taikhoan.php?tab=history" class="detail-back-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="detail-page-title">Chi tiết đặt lịch</div>
    </div>

    <div class="detail-container">
        <div class="detail-user-card">
            <img src="<?php echo $avatar_url; ?>" class="detail-avatar">
            <div class="detail-user-info">
                <div>KH: <b><?php echo $row['ten_kh']; ?></b></div>
                <div>Đối tượng: <b><?php echo $row['ten_co_so']; ?></b></div>
                <div>Số điện thoại: <b><?php echo $row['sdt_kh']; ?></b></div>
            </div>
        </div>

        <div class="detail-info-section">
            <div class="detail-section-title"><i class="fas fa-clipboard-list"></i> Thông tin</div>

            <div class="detail-row">Mã lịch đặt: <span class="text-yellow">#<?php echo $ma_don; ?></span></div>
            <div class="detail-row">Trạng thái: <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
            <div class="detail-row">Tên CLB: <b><?php echo $row['ten_co_so']; ?></b></div>
            <div class="detail-row">Địa chỉ: <b><?php echo $row['dia_chi_cu_the'] . ', ' . $row['quan_huyen'] . ', ' . $row['tinh_thanh']; ?></b></div>
            <div class="detail-row">
                Số điện thoại sân:
                <b><?php echo $row['hotline']; ?></a></b>
                <i class="fas fa-phone-alt" style="font-size: 12px; margin-left:5px;"></i>
            </div>

            <div class="detail-row" style="margin-top: 15px;">Ngày: <b><?php echo $ngay_dat; ?></b></div>
            <div class="detail-row">- <?php echo $row['ten_san']; ?>: <b><?php echo $gio_choi; ?></b></div>
            <div class="detail-row">Tổng giờ: <b><?php echo $hours; ?>h</b></div>

            <hr class="detail-divider">

            <div class="detail-row price">
                <span>Tổng tiền:</span>
                <span class="text-bold" style="font-size: 16px;"><?php echo number_format($row['tong_tien']); ?> VNĐ</span>
            </div>
        </div>
    </div>

    <?php if ($show_cancel_btn): ?>
        <button class="detail-footer-btn" onclick="cancelBooking()">HỦY ĐẶT LỊCH</button>
    <?php elseif ($show_contact_btn): ?>
        <button class="detail-footer-btn" onclick="alertContact()">HỦY ĐẶT LỊCH</button>
    <?php endif; ?>

    <script>
        function cancelBooking() {
            if (!confirm('Bạn có chắc chắn muốn hủy lịch đặt này không? Hành động này không thể hoàn tác.')) return;

            const formData = new FormData();
            formData.append('id', <?php echo $booking_id; ?>);

            fetch('xoa_lich.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showNotification('Đã hủy đặt lịch thành công!');
                        window.location.href = '../taikhoan.php?tab=history';
                    } else {
                        showNotification(data.message || 'Lỗi khi hủy');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Lỗi kết nối server');
                });
        }

        function alertContact() {
            showNotification("Đơn hàng đã được xác nhận.\nVui lòng liên hệ hotline chủ sân (<?php echo $row['hotline']; ?>) để được hỗ trợ hủy!");
        }
        function showNotification(message, type = 'danger') {
            const area = document.getElementById('notification-area');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerText = message;
            area.appendChild(alertDiv);
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
</body>

</html>
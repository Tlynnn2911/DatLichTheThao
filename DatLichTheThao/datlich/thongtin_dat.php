<?php
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra đăng nhập ,dữ liệu đầu vào
if (!isset($_SESSION['user_id'])) {
    header("Location: ../taikhoan/dangnhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['san_id'])) {
    header("Location: ../index.php"); // Không có dữ liệu thì về trang chủ
    exit();
}

$user_id = $_SESSION['user_id'];
$san_id = intval($_POST['san_id']);
$date = $_POST['date'];
$slots_str = $_POST['slots']; // Chuỗi giờ: "17:00,17:30"
$slots = explode(',', $slots_str);

// Lấy thông tin 
$sql = "SELECT s.ten_san, s.he_so_gia, 
               c.id as co_so_id, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.hotline,
               c.gia_sang, c.gia_toi, c.gia_cuoi_tuan, c.gio_bat_dau_toi, c.tinh_thanh
        FROM san s 
        JOIN co_so c ON s.co_so_id = c.id 
        WHERE s.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $san_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();

// Tính toán lại tổng tiền
$is_weekend = (date('N', strtotime($date)) >= 6);
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
    $total_price += ($base_price * $info['he_so_gia']) / 2;
}

// Chuẩn bị thông tin hiển thị
$ngay_dat_fmt = date('d/m/Y', strtotime($date));
$total_hours = count($slots) / 2;
$start_time = $slots[0];
// Tính giờ kết thúc của slot cuối cùng (+30p)
$last_slot_ts = strtotime("$date " . end($slots));
$end_time = date('H:i', $last_slot_ts + 1800);
$khung_gio = "$start_time - $end_time";

// Lấy thông tin khách hàng từ Session 
$user_name = $_SESSION['user_name'];
// Hoặc query lại:
$u_res = $conn->query("SELECT ho_ten, sdt FROM nguoi_dung WHERE id = $user_id");
$u_info = $u_res->fetch_assoc();
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($u_info['ho_ten']) . "&background=random&color=fff";
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
        <a href="javascript:history.back()" class="detail-back-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="detail-page-title">Xác nhận & Thanh toán</div>
    </div>

    <div class="detail-container">
        <div class="detail-user-card">
            <img src="<?php echo $avatar_url; ?>" class="detail-avatar">
            <div class="detail-user-info">
                <div>KH: <b><?php echo $u_info['ho_ten']; ?></b></div>
                <div>Số điện thoại: <b><?php echo $u_info['sdt']; ?></b></div>
            </div>
        </div>
        <div class="detail-info-section">
            <div class="detail-section-title"><i class="fas fa-clipboard-list"></i> Thông tin đặt sân</div>
            <div class="detail-row">Cơ sở: <b><?php echo $info['ten_co_so']; ?></b></div>
            <div class="detail-row">Địa chỉ: <b><?php echo $info['dia_chi_cu_the'] . ', ' . $info['quan_huyen'] . ', ' . $info['tinh_thanh']; ?></b></div>
            <div class="detail-row">Ngày: <b><?php echo $ngay_dat_fmt; ?></b></div>
            <div class="detail-row">- <?php echo $info['ten_san']; ?>: <b><?php echo $khung_gio; ?></b></div>
            <div class="detail-row">Tổng giờ: <b><?php echo $total_hours; ?>h</b></div>
            <hr class="detail-divider">

            <div class="price-row">
                <span>Tổng tiền :</span>
                <span class="total-price"><?php echo number_format($total_price); ?> VNĐ</span>
            </div>

            <form id="confirmForm" action="add_datlich.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="co_so_id" value="<?php echo $info['co_so_id']; ?>">
                <input type="hidden" name="san_id" value="<?php echo $san_id; ?>">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="slots" value="<?php echo $slots_str; ?>">
                <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
            </form>
        </div>
    </div>

    <button class="bottom-action" onclick="submitFinalBooking()">XÁC NHẬN ĐẶT SÂN</button>

    <script>

        // Submit form
        function submitFinalBooking() {

            if (confirm("Xác nhận thông tin và đặt sân?")) {
                document.getElementById('confirmForm').submit();
            }
        }
    </script>
</body>

</html>
<?php
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
$days_map = ['Monday' => 'Thứ Hai', 'Tuesday' => 'Thứ Ba', 'Wednesday' => 'Thứ Tư', 'Thursday' => 'Thứ Năm', 'Friday' => 'Thứ Sáu', 'Saturday' => 'Thứ Bảy', 'Sunday' => 'Chủ Nhật'];
$display_date = $days_map[date('l')] . ', ' . date('d/m/Y');
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id_chu'])) {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}

$user_id = $_SESSION['user_id_chu'];
$co_so_id = 0;
//Kiểm tra session xem trước đó đang ở cơ sở nào
if (isset($_SESSION['current_co_so_id'])) {
    $sess_id = $_SESSION['current_co_so_id'];
    // Check quyền sở hữu lại cho an toàn
    $check = $conn->query("SELECT id FROM co_so WHERE id = $sess_id AND chu_san_id = $user_id");
    if ($check->num_rows > 0) {
        $co_so_id = $sess_id;
    }
}

// Nếu session mất hoặc chưa có, lấy cái đầu tiên
if ($co_so_id == 0) {
    $sql_first = "SELECT id FROM co_so WHERE chu_san_id = $user_id LIMIT 1";
    $res_first = $conn->query($sql_first);
    if ($res_first && $res_first->num_rows > 0) {
        $co_so_id = $res_first->fetch_assoc()['id'];
        $_SESSION['current_co_so_id'] = $co_so_id;
    } else {
        $co_so_id = 0;
    }
}

$row_coso = null;
if ($co_so_id > 0) {
    $sql_coso = "SELECT * FROM co_so WHERE id = $co_so_id";
    $row_coso = $conn->query($sql_coso)->fetch_assoc();
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'choxn';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>BOOKSAN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
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


    <div class="profile-header-bar">Quản Lý</div>
    <div class="profile-container">

        <?php if (isset($_SESSION['user_id_chu'])): ?>
            <?php
            $user_id = $_SESSION['user_id_chu'];
            $sql_user = "SELECT * FROM nguoi_dung WHERE id = $user_id";
            $user = $conn->query($sql_user)->fetch_assoc();
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user['ho_ten']) . "&background=random&color=fff";
            ?>

            <div class="sidebar">
                <div class="user-info-card">
                    <img src="<?php echo $avatar_url; ?>" class="user-avatar-img">
                    <div class="user-details">
                        <h3><?php echo $user['ho_ten']; ?></h3>
                        <p><i class="fas fa-phone-alt" style="font-size:11px;"></i> <?php echo $user['sdt']; ?></p>
                        <p class="email"><i class="fas fa-envelope" style="font-size:11px;"></i> <?php echo $user['email']; ?></p>
                    </div>
                </div>

                <div class="section-title">Quản Lý</div>
                <div class="menu-list">
                    <a href="quanly.php?tab=profile_coso" class="menu-item <?php echo ($tab == 'profile_coso') ? 'active' : ''; ?>">
                        <i class="far fa-building"></i> <span>Chỉnh sửa thông tin cơ sở</span>
                    </a>
                    <a href="quanly.php?tab=price" class="menu-item <?php echo ($tab == 'price') ? 'active' : ''; ?>">
                        <i class="far fa-file-lines"></i> <span>Bảng giá</span>
                    </a>
                    <a href="quanly.php?tab=choxn" class="menu-item <?php echo ($tab == 'choxn') ? 'active' : ''; ?>">
                        <i class="far fa-calendar"></i> <span>Đơn chờ xác nhận</span>
                    </a>
                    <a href="quanly.php?tab=daxn" class="menu-item <?php echo ($tab == 'daxn') ? 'active' : ''; ?>">
                        <i class="far fa-calendar-check"></i> <span>Đơn đã xác nhận</span>
                    </a>
                    <a href="quanly.php?tab=daht" class="menu-item <?php echo ($tab == 'daht') ? 'active' : ''; ?>">
                        <i class="far fa-flag"></i> <span>Đơn đã hoàn thành</span>
                    </a>
                    <a href="quanly.php?tab=danhgia" class="menu-item <?php echo ($tab == 'danhgia') ? 'active' : ''; ?>">
                        <i class="far fa-comments"></i> <span>Bài đánh giá</span>
                    </a>
                </div>

                <div class="section-title">Tài Khoản</div>
                <div class="menu-list">
                    <a href="quanly.php?tab=profile_user" class="menu-item <?php echo ($tab == 'profile_user') ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit"></i> <span>Chỉnh sửa thông tin tài khoản</span>
                    </a>
                    <a href="quanly.php?tab=password" class="menu-item <?php echo ($tab == 'password') ? 'active' : ''; ?>">
                        <i class="fas fa-key"></i> <span>Đổi mật khẩu</span>
                    </a>
                    <a href="../taikhoan/dangxuatchu.php" class="menu-item" style="color: #c0392b;">
                        <i class="fas fa-sign-out-alt" style="color: #c0392b;"></i> <span>Đăng xuất</span>
                    </a>
                </div>
            </div>

            <div class="content-area">

                <?php if ($tab == 'choxn'): ?>
                    <?php
                    // XỬ LÝ SQL LỌC KHOẢNG NGÀY
                    $sql_booking = "SELECT dl.*, s.ten_san, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.tinh_thanh, dl.trang_thai_don, dl.tong_tien FROM dat_lich dl JOIN san s ON dl.san_id = s.id JOIN co_so c ON dl.co_so_id = c.id WHERE dl.co_so_id = $co_so_id AND dl.trang_thai_don = 'cho_xac_nhan' ";
                    $res_booking = $conn->query($sql_booking);
                    ?>

                    <div class="content-header">
                        <h2>Danh sách đơn chờ xác nhận</h2>

                        <form action="quanly.php" method="GET" class="filter-wrapper">
                            <input type="hidden" name="tab" value="choxn">
                            <span class="date-label">Ngày: <?php echo $display_date ?></span>

                        </form>
                    </div>
                    <?php if ($res_booking && $res_booking->num_rows > 0): ?>
                        <div>
                            <?php while ($row = $res_booking->fetch_assoc()):
                                $time_str = date('H:i', strtotime($row['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($row['gio_ket_thuc']));

                                $status_text = "";
                                $status_color = "";
                                $status_icon = "";
                                switch ($row['trang_thai_don']) {
                                    case 'cho_xac_nhan':
                                        $status_text = "Chờ xác nhận";
                                        $status_color = "#f39c12";
                                        $status_icon = "fa-clock";
                                        break;
                                }
                            ?>
                                <div class="history-item" onclick="window.location.href='chitiet_don.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">
                                    <div class="tag-tick"></div>
                                    <div class="status-badge" style="color: <?php echo $status_color; ?>;">
                                        <?php echo $status_text; ?> <i class="fas <?php echo $status_icon; ?>"></i>
                                    </div>
                                    <div class="stadium-name"><?php echo $row['ten_san']; ?></div>
                                    <div style="font-size:14px; color:#555; margin-bottom:3px;">
                                        Chi tiết: <b><?php echo $row['ten_san']; ?> : <?php echo $time_str; ?> | Ngày <?php echo date('d/m/Y', strtotime($row['ngay_dat'])); ?></b>
                                    </div>
                                    <div style="font-size:14px; color:#555;">Tổng tiền: <b><?php echo number_format($row['tong_tien'], 0, ',', '.') ?> VND</b></div>
                                    <div class="action-buttons-row">
                                        <form action="ql_xuly_don.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <input type="hidden" name="current_tab" value="choxn">
                                            <button type="submit" class="btn-action btn-accept">
                                                <i class="fas fa-check"></i> Xác nhận
                                            </button>
                                        </form>

                                        <form action="ql_xuly_don.php" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối đơn này?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="current_tab" value="choxn">
                                            <button type="submit" class="btn-action btn-deny">
                                                <i class="fas fa-times"></i> Từ chối
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="far fa-calendar-times" style="font-size: 40px; margin-bottom: 10px; color: #eee;"></i>
                            <p>Không tìm thấy đơn nào.</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($tab == 'daxn'): ?>
                    <?php
                    // XỬ LÝ SQL LỌC KHOẢNG NGÀY 
                    $sql_booking = "SELECT dl.*, s.ten_san, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.tinh_thanh, dl.trang_thai_don, dl.tong_tien FROM dat_lich dl JOIN san s ON dl.san_id = s.id JOIN co_so c ON dl.co_so_id = c.id WHERE dl.co_so_id = $co_so_id AND dl.trang_thai_don = 'da_xac_nhan' ";
                    $res_booking = $conn->query($sql_booking);
                    ?>

                    <div class="content-header">
                        <h2>Danh sách đơn đã xác nhận</h2>

                        <form action="quanly.php" method="GET" class="filter-wrapper">
                            <input type="hidden" name="tab" value="choxn">
                            <span class="date-label">Ngày: <?php echo $display_date ?></span>
                        </form>
                    </div>
                    <?php if ($res_booking && $res_booking->num_rows > 0): ?>
                        <div>
                            <?php while ($row = $res_booking->fetch_assoc()):
                                $time_str = date('H:i', strtotime($row['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($row['gio_ket_thuc']));

                                $status_text = "";
                                $status_color = "";
                                $status_icon = "";
                                switch ($row['trang_thai_don']) {
                                    case 'da_xac_nhan':
                                        $status_text = "Đã xác nhận";
                                        $status_color = "#27ae60";
                                        $status_icon = "fa-check-circle";
                                        break;
                                }
                            ?>
                                <div class="history-item" onclick="window.location.href='chitiet_don.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">
                                    <div class="tag-tick"></div>
                                    <div class="status-badge" style="color: <?php echo $status_color; ?>;">
                                        <?php echo $status_text; ?> <i class="fas <?php echo $status_icon; ?>"></i>
                                    </div>
                                    <div class="stadium-name"><?php echo $row['ten_san']; ?></div>
                                    <div style="font-size:14px; color:#555; margin-bottom:3px;">
                                        Chi tiết: <b><?php echo $row['ten_san']; ?> : <?php echo $time_str; ?> | Ngày <?php echo date('d/m/Y', strtotime($row['ngay_dat'])); ?></b>
                                    </div>
                                    <div style="font-size:14px; color:#555;">Tổng tiền: <b><?php echo number_format($row['tong_tien'], 0, ',', '.') ?> VND</b></div>
                                    <div class="action-buttons-row">
                                        <form action="ql_xuly_don.php" method="POST" style="display:inline;" onsubmit="return confirm('CẢNH BÁO: Hủy đơn đã xác nhận?');">

                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="current_tab" value="daxn">

                                            <button type="submit" class="btn-action btn-cancel" onclick="event.stopPropagation();">
                                                <i class="fas fa-ban"></i> Hủy đơn
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="far fa-calendar-times" style="font-size: 40px; margin-bottom: 10px; color: #eee;"></i>
                            <p>Không tìm thấy đơn nào.</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($tab == 'daht'): ?>
                    <?php
                    $sql_booking = "SELECT dl.*, s.ten_san, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.tinh_thanh, dl.trang_thai_don, dl.tong_tien FROM dat_lich dl JOIN san s ON dl.san_id = s.id JOIN co_so c ON dl.co_so_id = c.id WHERE dl.co_so_id = $co_so_id AND dl.trang_thai_don = 'hoan_thanh' ";
                    if (!empty($from_date)) {
                        $sql_booking .= " AND dl.ngay_dat >= '$from_date'";
                    }
                    if (!empty($to_date)) {
                        $sql_booking .= " AND dl.ngay_dat <= '$to_date'";
                    }

                    $sql_booking .= " ORDER BY dl.ngay_dat DESC";
                    $res_booking = $conn->query($sql_booking);
                    ?>

                    <div class="content-header">
                        <h2>Danh sách đơn đã hoàn thành</h2>

                        <form action="quanly.php" method="GET" class="filter-wrapper">
                            <input type="hidden" name="tab" value="history">

                            <span class="date-label">Từ:</span>
                            <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="range-date-picker">

                            <span class="date-label">Đến:</span>
                            <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="range-date-picker">

                            <button type="submit" class="btn-filter-icon" title="Lọc dữ liệu">
                                <i class="fas fa-filter"></i>
                            </button>
                            <?php if (!empty($from_date) || !empty($to_date)): ?>
                                <a href="quanly.php?tab=daht" class="btn-reset" title="Xóa lọc"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <?php if ($res_booking && $res_booking->num_rows > 0): ?>
                        <div>
                            <?php while ($row = $res_booking->fetch_assoc()):
                                $time_str = date('H:i', strtotime($row['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($row['gio_ket_thuc']));

                                $status_text = "";
                                $status_color = "";
                                $status_icon = "";
                                switch ($row['trang_thai_don']) {
                                    case 'hoan_thanh':
                                        $status_text = "Hoàn thành";
                                        $status_color = "#2980b9";
                                        $status_icon = "fa-check-double";
                                        break;
                                }
                            ?>
                                <div class="history-item" onclick="window.location.href='chitiet_don.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">
                                    <div class="tag-tick"></div>
                                    <div class="status-badge" style="color: <?php echo $status_color; ?>;">
                                        <?php echo $status_text; ?> <i class="fas <?php echo $status_icon; ?>"></i>
                                    </div>
                                    <div class="stadium-name"><?php echo $row['ten_san']; ?></div>
                                    <div style="font-size:14px; color:#555; margin-bottom:3px;">
                                        Chi tiết: <b><?php echo $row['ten_san']; ?> : <?php echo $time_str; ?> | Ngày <?php echo date('d/m/Y', strtotime($row['ngay_dat'])); ?></b>
                                    </div>
                                    <div style="font-size:14px; color:#555;">Tổng tiền: <b><?php echo number_format($row['tong_tien'], 0, ',', '.') ?> VND</b></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="far fa-calendar-times" style="font-size: 40px; margin-bottom: 10px; color: #eee;"></i>
                            <p>Không tìm thấy lịch đặt nào.</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($tab == 'danhgia'): ?>
                    <?php
                    $sql_dg = "SELECT dg.*, nd.ho_ten 
                               FROM danh_gia dg 
                               JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id 
                               WHERE dg.co_so_id = $co_so_id 
                               ORDER BY dg.ngay_tao DESC";
                    $res_dg = $conn->query($sql_dg);
                    ?>

                    <div class="content-header">
                        <h2>Quản lý đánh giá & Phản hồi</h2>
                    </div>

                    <div style="padding-bottom: 50px;">
                        <?php if ($res_dg && $res_dg->num_rows > 0): ?>
                            <?php while ($row = $res_dg->fetch_assoc()): ?>
                                <?php
                                $avatar_user = "https://ui-avatars.com/api/?name=" . urlencode($row['ho_ten']) . "&background=random&color=fff&size=50";
                                $so_sao = intval($row['so_sao']);
                                ?>
                                <div class="history-item" style="display: block; cursor: default; padding: 20px;">
                                    <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                                        <img src="<?php echo $avatar_user; ?>" style="width: 40px; height: 40px; border-radius: 50%;">
                                        <div>
                                            <div style="font-weight: bold; color: #333;"><?php echo $row['ho_ten']; ?></div>
                                            <div style="font-size: 12px; color: #777;">
                                                <?php echo date('H:i d/m/Y', strtotime($row['ngay_tao'])); ?>
                                            </div>
                                        </div>
                                        <div style="margin-left: auto;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star" style="color: <?php echo ($i <= $so_sao) ? '#f1c40f' : '#ddd'; ?>; font-size: 14px;"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; color: #555; margin-bottom: 15px;">
                                        <?php echo nl2br(htmlspecialchars($row['noi_dung'])); ?>
                                    </div>

                                    <div style="border-top: 1px dashed #ddd; padding-top: 15px;">
                                        <form action="ql_xuly_danhgia.php" method="POST">
                                            <input type="hidden" name="danh_gia_id" value="<?php echo $row['id']; ?>">
                                            <label style="font-size: 13px; font-weight: bold; color: #2980b9; display: block; margin-bottom: 5px;">
                                                <i class="fas fa-reply"></i> Phản hồi của bạn:
                                            </label>

                                            <textarea name="phan_hoi" rows="2" class="form-control-row"
                                                placeholder="Nhập nội dung phản hồi khách hàng..."
                                                style="width: 100%; resize: vertical; padding: 8px; border: 1px solid #ccc;"><?php echo $row['phan_hoi_chu_san']; ?></textarea>

                                            <div style="text-align: right; margin-top: 10px;">
                                                <button type="submit" class="btn-action btn-accept" style="padding: 6px 15px; font-size: 13px;">
                                                    <?php echo empty($row['phan_hoi_chu_san']) ? 'Gửi phản hồi' : 'Cập nhật phản hồi'; ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="far fa-comment-dots" style="font-size: 40px; margin-bottom: 10px; color: #eee;"></i>
                                <p>Chưa có đánh giá nào từ khách hàng.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($tab == 'profile_user'): ?>
                    <div class="content-header">
                        <h2>Chỉnh sửa thông tin tài khoản</h2>
                    </div>
                    <div style="padding: 30px;">
                        <form action="xuly_suathongtin_chu.php" method="POST" class="form-box">
                            <div class="form-group-row"><label>Họ và tên</label><input type="text" name="ho_ten" class="form-control-row" value="<?php echo $user['ho_ten']; ?>" required></div>
                            <div class="form-group-row"><label>Số điện thoại</label><input type="text" value="<?php echo $user['sdt']; ?>" class="form-control-row read-only" readonly></div>
                            <div class="form-group-row"><label>Email</label><input type="email" name="email" class="form-control-row" value="<?php echo $user['email']; ?>"></div>
                            <div style="text-align: center; margin-top: 30px;"><button type="submit" class="btn-save">LƯU THAY ĐỔI</button></div>
                        </form>
                    </div>

                <?php elseif ($tab == 'profile_coso'): ?>
                    <?php
                    $sql_img = "SELECT * FROM hinh_anh_san WHERE co_so_id = $co_so_id";
                    $res_img = $conn->query($sql_img);

                    $avatar_img = null;
                    $banner_img = null;
                    $gallery_imgs = [];
                
                    if ($res_img && $res_img->num_rows > 0) {
                        while ($img = $res_img->fetch_assoc()) {
                            $img['display_src'] = '../' . $img['url_hinh'];

                            if ($img['avata'] == 1) {
                                $avatar_img = $img;
                            } elseif ($img['la_anh_dai_dien'] == 1) {
                                $banner_img = $img;
                            } elseif ($img['avata'] == 0 && $img['la_anh_dai_dien'] == 0) {
                                $gallery_imgs[] = $img;
                            }
                        }
                    }

                    $default_avatar = "https://ui-avatars.com/api/?name=" . urlencode($row_coso['ten_co_so']) . "&size=150";
                    $default_banner = "https://via.placeholder.com/800x200?text=Chua+co+anh+bia";
                    ?>

                    <div class="content-header">
                        <h2>Chỉnh sửa thông tin cơ sở</h2>
                    </div>
                    <div style="padding: 30px;">
                        <form action="../chusan/ql_update_coso.php" method="POST" class="form-box" enctype="multipart/form-data">
                            <input type="hidden" name="co_so_id" value="<?php echo $co_so_id; ?>">

                            <div style="display: flex; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">

                                <div style="flex: 1; text-align: center;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 10px;">Ảnh đại diện</label>
                                    <div style="position: relative; width: 120px; height: 120px; margin: 0 auto;">
                                        <img src="<?php echo $avatar_img ? $avatar_img['display_src'] : $default_avatar; ?>"
                                            style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #ddd;">
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <input type="file" name="avatar_file" accept="image/*" style="font-size: 11px; width: 100%;">
                                    </div>
                                </div>

                                <div style="flex: 2;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 10px;">Ảnh bìa</label>
                                    <div style="width: 100%; height: 120px; background: #f0f0f0; border-radius: 8px; overflow: hidden;">
                                        <img src="<?php echo $banner_img ? $banner_img['display_src'] : $default_banner; ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <input type="file" name="banner_file" accept="image/*" style="font-size: 11px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group-row" style="display: block;">
                                <label style="font-weight: bold; font-size: 16px;">Album (<?php echo count($gallery_imgs); ?> ảnh)</label>
                                <div class="form-group-row" style="margin-top: 10px;">
                                    <label style="form-group-row">Thêm ảnh vào Album</label>
                                    <input type="file" name="gallery_files[]" multiple accept="image/*" class="form-control-row">
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                                    <?php if (count($gallery_imgs) > 0): ?>
                                        <?php foreach ($gallery_imgs as $g_img): ?>
                                            <div style="width: 120px; text-align: center; background: #fff; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                                <a href="<?php echo $g_img['display_src']; ?>" target="_blank">
                                                    <img src="<?php echo $g_img['display_src']; ?>" style="width: 100%; height: 80px; object-fit: cover; border-radius: 3px;">
                                                </a>
                                                <div style="margin-top: 5px; text-align: left; padding-left: 5px;">
                                                    <label style="cursor: pointer; color: #c0392b; font-size: 13px;">
                                                        <input type="checkbox" name="delete_ids[]" value="<?php echo $g_img['id']; ?>"> Xóa ảnh
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="font-style: italic; color: #999;">Chưa có ảnh nào trong album.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
                            <label style="font-weight: bold; font-size: 16px;">Thông tin</label>
                            <div class="form-group-row"><label>Tên cơ sở</label>
                                <input type="text" name="ten_cs" class="form-control-row" value="<?php echo $row_coso['ten_co_so']; ?>" required>
                            </div>
                            <div class="form-group-row"><label>Địa chỉ cụ thể</label>
                                <input type="text" name="dc_cu_the" value="<?php echo $row_coso['dia_chi_cu_the']; ?>" class="form-control-row" required>
                            </div>
                            <div class="form-group-row"><label>Quận huyện</label>
                                <input type="text" name="qhuyen" value="<?php echo $row_coso['quan_huyen']; ?>" class="form-control-row" required>
                            </div>
                            <div class="form-group-row"><label>Tỉnh thành</label>
                                <input type="text" name="tinh" value="<?php echo $row_coso['tinh_thanh']; ?>" class="form-control-row" required>
                            </div>
                            <div class="form-group-row"><label>Hotline</label>
                                <input type="text" name="hotline" value="<?php echo $row_coso['hotline']; ?>" class="form-control-row" required>
                            </div>
                            <div class="row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                                <div class="form-group" style="flex: 1;">
                                    <label class="form-label" style="font-weight: bold; display: block; margin-bottom: 5px;">Giờ mở cửa:</label>
                                    <input type="time" name="gio_mo_cua" class="form-input"
                                        value="<?php echo isset($row_coso['gio_mo_cua']) ? $row_coso['gio_mo_cua'] : '05:00'; ?>"
                                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label class="form-label" style="font-weight: bold; display: block; margin-bottom: 5px;">Giờ đóng cửa:</label>
                                    <input type="time" name="gio_dong_cua" class="form-input"
                                        value="<?php echo isset($row_coso['gio_dong_cua']) ? $row_coso['gio_dong_cua'] : '23:00'; ?>"
                                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                            </div>

                            <div style="text-align: center; margin-top: 30px;"><button type="submit" class="btn-save">LƯU THAY ĐỔI</button></div>
                        </form>
                    </div>

                <?php elseif ($tab == 'price'): ?>
                    <div class="content-header">
                        <h2>Bảng giá</h2>
                    </div>

                    <div style="padding: 10px;">
                        <form action="../chusan/ql_update_banggia.php" method="POST" class="form-box">
                            <input type="hidden" name="co_so_id" value="<?php echo $co_so_id; ?>">

                            <h3 style="font-size: 16px; padding-bottom: 10px; margin-bottom: 20px;" align="center">Khung Giờ</h3>

                            <div class="row" style="display: flex; gap: 20px; align-items: flex-end; margin-bottom: 30px;">
                                <div style="flex: 1; background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center;">
                                    <label style="display:block; font-weight:bold; margin-bottom:10px; color:#555;">Mở Cửa</label>
                                    <div style="font-size: 20px; font-weight: bold; color: #333;">
                                        <?php echo date('H:i', strtotime($row_coso['gio_mo_cua'])); ?>
                                    </div>
                                </div>

                                <div style="flex: 0 0 50px; text-align: center; padding-bottom: 25px;">
                                    <i class="fas fa-arrow-right" style="color: #999;"></i>
                                </div>

                                <div class="form-group" style="flex: 3;">
                                    <label class="form-label" style="font-weight: bold; color: #c0392b;">Giờ bắt đầu tính giá tối</label>
                                    <input type="time" name="gio_bat_dau_toi" class="form-input"
                                        value="<?php echo $row_coso['gio_bat_dau_toi'] ? $row_coso['gio_bat_dau_toi'] : '17:00'; ?>"
                                        style="width: 100%; padding: 12px; border: 2px solid #c0392b; border-radius: 6px; font-weight: bold; font-size: 16px;">
                                </div>

                                <div style="flex: 0 0 50px; text-align: center; padding-bottom: 25px;">
                                    <i class="fas fa-arrow-right" style="color: #999;"></i>
                                </div>

                                <div style="flex: 1; background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center;">
                                    <label style="display:block; font-weight:bold; margin-bottom:10px; color:#555;">Đóng Cửa</label>
                                    <div style="font-size: 20px; font-weight: bold; color: #333;">
                                        <?php echo date('H:i', strtotime($row_coso['gio_dong_cua'])); ?>
                                    </div>
                                </div>
                            </div>

                            <h3 style="font-size: 16px; border-bottom: 2px solid #2980b9; padding-bottom: 10px; margin-bottom: 20px;">Bảng Giá Chi Tiết (VNĐ/giờ)</h3>

                            <div class="row" style="display: flex; gap: 30px;">
                                <div style="flex: 1; border-right: 1px solid #eee; padding-right: 30px;">
                                    <div style="margin-bottom: 15px; font-weight: bold; text-transform: uppercase;">
                                        <i class="far fa-calendar-alt"></i> Thứ 2 - Thứ 6
                                    </div>

                                    <div class="form-group-row">
                                        <label>Giá Sáng (Đồng/h)</label>
                                        <div class="input-wrapper">
                                            <input type="number" name="gia_sang" class="form-control" placeholder="0" value="<?php echo $row_coso['gia_sang']; ?>" required min="0" step="1000">
                                        </div>
                                    </div>

                                    <div class="form-group-row">
                                        <label>Giá Tối (Đồng/h)</label>
                                        <div class="input-wrapper">
                                            <input type="number" name="gia_toi" class="form-control" placeholder="0" value="<?php echo $row_coso['gia_toi']; ?>" required min="0" step="1000">
                                        </div>
                                    </div>
                                </div>

                                <div style="flex: 1;">
                                    <div style="margin-bottom: 15px; font-weight: bold; color: #555; text-transform: uppercase;">
                                        <i class="fas fa-glass-cheers"></i> Cuối tuần (T7 - CN)
                                    </div>

                                    <div class="form-group-row" style="background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba;">
                                        <label style="color: #856404;">Giá cả ngày (Đồng/h)</label>
                                        <div class="input-wrapper" style="margin-top: 10px;">
                                            <input type="number" name="gia_cuoi_tuan" class="form-control" placeholder="0" value="<?php echo $row_coso['gia_cuoi_tuan']; ?>" required min="0" step="1000">
                                        </div>
                                        <small style="display: block; margin-top: 5px; color: #856404; font-style: italic;">
                                            Áp dụng từ <?php echo date('H:i', strtotime($row_coso['gio_mo_cua'])); ?>
                                            đến <?php echo date('H:i', strtotime($row_coso['gio_dong_cua'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div style="text-align: center; margin-top: 40px;">
                                <button type="submit" class="btn-save" style="padding: 12px 40px; font-size: 16px;">LƯU BẢNG GIÁ</button>
                            </div>
                        </form>
                    </div>

                <?php elseif ($tab == 'password'): ?>
                    <div class="content-header">
                        <h2>Đổi mật khẩu</h2>
                    </div>
                    <div style="padding: 30px;">
                        <form action="xuly_doimatkhau_chu.php" method="POST" class="form-box">
                            <div class="form-group-row"><label>Mật khẩu hiện tại</label>
                                <div class="input-wrapper"><input type="password" name="current_pass" id="current_pass" class="form-control" placeholder="Nhập mật khẩu cũ" required><i class="fas fa-eye-slash input-icon-right" onclick="togglePass('current_pass', this)"></i></div>
                            </div>
                            <div class="form-group-row"><label>Mật khẩu mới</label>
                                <div class="input-wrapper"><input type="password" name="new_pass" id="new_pass" class="form-control" placeholder="Nhập mật khẩu mới" required><i class="fas fa-eye-slash input-icon-right" onclick="togglePass('new_pass', this)"></i></div>
                            </div>
                            <div class="form-group-row"><label>Nhập lại mật khẩu mới</label>
                                <div class="input-wrapper"><input type="password" name="confirm_pass" id="confirm_pass" class="form-control" placeholder="Nhập lại mật khẩu mới" required><i class="fas fa-eye-slash input-icon-right" onclick="togglePass('confirm_pass', this)"></i></div>
                            </div>
                            <div style="text-align: center; margin-top: 30px;"><button type="submit" class="btn-save">CẬP NHẬT MẬT KHẨU</button></div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bottom-nav">
        <a href="trangchu.php" class="nav-item">
            <i class="fas fa-home"></i> <span>Trang Chủ</span>
        </a>
        <div class="nav-item active">
            <i class="fas fa-user-shield"></i> <span>Quản Lý</span>
        </div>
    </div>

    <script>
        // Hàm xử lý ẩn/hiện mật khẩu
        function togglePass(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        function toggleFilterMenu() {
            var menu = document.getElementById("filterMenu");
            if (menu.style.display === "block") {
                menu.style.display = "none";
            } else {
                menu.style.display = "block";
            }
        }

        // Đóng menu khi click ra ngoài
        window.onclick = function(event) {
            if (!event.target.matches('.btn-filter-toggle') && !event.target.matches('.btn-filter-toggle *')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
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
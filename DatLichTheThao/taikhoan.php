<?php
session_start();
require_once 'config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'history';

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>BookSan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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

    <div class="profile-header-bar">Tài Khoản</div>
    <div class="profile-container">

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="sidebar">
                <div class="user-info-card" style="display: block; text-align: center;">
                    <div class="guest-avatar" style="margin: 0 auto 10px auto;"><i class="fas fa-user-circle" style="font-size: 40px;"></i></div>
                    <div class="user-details">
                        <h3>Khách</h3>
                        <p class="highlight">Vui lòng đăng nhập</p>
                    </div>
                    <div class="guest-btn-group">
                        <a href="taikhoan/dangnhap.php" class="btn-small btn-green">Đăng nhập</a>
                        <a href="taikhoan/dangky.php" class="btn-small btn-outline">Đăng ký</a>
                    </div>
                </div>
                <div class="section-title">Cá nhân</div>
                <div class="menu-list">
                    <a href="taikhoan/dangnhap.php" class="menu-item">
                        <i class="far fa-calendar-check"></i> <span>Lịch đã đặt</span> <i class="fas fa-chevron-right menu-arrow"></i>
                    </a>
                </div>
            </div>
            <div class="content-area">
                <div class="content-header">
                    <h2>Lịch sử đặt sân</h2>
                </div>
                <div class="empty-state">
                    <i class="fas fa-lock" style="font-size: 40px; margin-bottom: 10px; color: #ccc;"></i>
                    <p>Vui lòng đăng nhập để xem lịch đã đặt</p>
                </div>
            </div>

        <?php else: ?>
            <?php
            $user_id = $_SESSION['user_id'];
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
                <div class="section-title">Tài khoản</div>
                <div class="menu-list">
                    <a href="taikhoan.php?tab=history" class="menu-item <?php echo ($tab == 'history') ? 'active' : ''; ?>">
                        <i class="far fa-calendar-check"></i> <span>Lịch đã đặt</span>
                    </a>
                    <a href="taikhoan.php?tab=profile" class="menu-item <?php echo ($tab == 'profile') ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit"></i> <span>Chỉnh sửa thông tin</span>
                    </a>
                    <a href="taikhoan.php?tab=password" class="menu-item <?php echo ($tab == 'password') ? 'active' : ''; ?>">
                        <i class="fas fa-key"></i> <span>Đổi mật khẩu</span>
                    </a>
                    <a href="#" class="menu-item" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản?');">
                        <i class="fas fa-user-times"></i> <span>Xóa tài khoản</span>
                    </a>
                    <a href="taikhoan/dangxuat.php" class="menu-item" style="color: #c0392b;">
                        <i class="fas fa-sign-out-alt" style="color: #c0392b;"></i> <span>Đăng xuất</span>
                    </a>
                </div>
            </div>

            <div class="content-area">

                <?php if ($tab == 'history'): ?>
                    <?php
                    $sql_booking = "SELECT dl.*, s.ten_san, c.ten_co_so, c.dia_chi_cu_the, c.quan_huyen, c.tinh_thanh, dl.trang_thai_don FROM dat_lich dl JOIN san s ON dl.san_id = s.id JOIN co_so c ON dl.co_so_id = c.id WHERE dl.nguoi_dung_id = $user_id";

                    // Nếu có ngày bắt đầu
                    if (!empty($from_date)) {
                        $sql_booking .= " AND dl.ngay_dat >= '$from_date'";
                    }
                    // Nếu có ngày kết thúc
                    if (!empty($to_date)) {
                        $sql_booking .= " AND dl.ngay_dat <= '$to_date'";
                    }

                    $sql_booking .= " ORDER BY dl.ngay_dat DESC";
                    $res_booking = $conn->query($sql_booking);
                    ?>

                    <div class="content-header">
                        <h2>Danh sách đặt lịch</h2>

                        <form action="taikhoan.php" method="GET" class="filter-wrapper">
                            <input type="hidden" name="tab" value="history">

                            <span class="date-label">Từ:</span>
                            <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="range-date-picker">

                            <span class="date-label">Đến:</span>
                            <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="range-date-picker">

                            <button type="submit" class="btn-filter-icon" title="Lọc dữ liệu">
                                <i class="fas fa-filter"></i>
                            </button>

                            <?php if (!empty($from_date) || !empty($to_date)): ?>
                                <a href="taikhoan.php?tab=history" class="btn-reset" title="Xóa lọc"><i class="fas fa-times"></i></a>
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
                                    case 'cho_xac_nhan':
                                        $status_text = "Chờ xác nhận";
                                        $status_color = "#7f8c8d";
                                        $status_icon = "fa-question-circle";
                                        break;
                                    case 'da_xac_nhan':
                                        $status_text = "Đã xác nhận";
                                        $status_color = "#27ae60";
                                        $status_icon = "fa-check-circle";
                                        break;
                                    case 'da_huy':
                                        $status_text = "Đã hủy";
                                        $status_color = "#c0392b";
                                        $status_icon = "fa-times-circle";
                                        break;
                                    case 'hoan_thanh':
                                        $status_text = "Hoàn thành";
                                        $status_color = "#2980b9";
                                        $status_icon = "fa-check-double";
                                        break;
                                    default:
                                        $status_text = "Chờ xác nhận";
                                        $status_color = "#7f8c8d";
                                        $status_icon = "fa-question-circle";
                                }
                            ?>
                                <div class="history-item" onclick="window.location.href='datlich/chitiet_datlich.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">

                                    <div class="tag-tick"></div>
                                    <div class="status-badge" style="color: <?php echo $status_color; ?>;">
                                        <?php echo $status_text; ?> <i class="fas <?php echo $status_icon; ?>"></i>
                                    </div>
                                    <div class="stadium-name"><?php echo $row['ten_co_so']; ?></div>
                                    <div style="font-size:14px; color:#555; margin-bottom:3px;">
                                        Chi tiết: <b><?php echo $row['ten_san']; ?> : <?php echo $time_str; ?> | Ngày <?php echo date('d/m/Y', strtotime($row['ngay_dat'])); ?></b>
                                    </div>
                                    <div style="font-size:14px; color:#555;">Địa chỉ: <b> <?php echo $row['dia_chi_cu_the'] . ', ' . $row['quan_huyen'] . ', ' . $row['tinh_thanh']; ?></b></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="far fa-calendar-times" style="font-size: 40px; margin-bottom: 10px; color: #eee;"></i>
                            <p>Không tìm thấy lịch đặt nào.</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($tab == 'profile'): ?>
                    <div class="content-header">
                        <h2>Chỉnh sửa thông tin</h2>
                    </div>
                    <div style="padding: 30px;">
                        <form action="taikhoan/xuly_suathongtin.php" method="POST" class="form-box">
                            <div class="form-group-row"><label>Họ và tên</label><input type="text" name="ho_ten" class="form-control-row" value="<?php echo $user['ho_ten']; ?>" required></div>
                            <div class="form-group-row"><label>Số điện thoại</label><input type="text" value="<?php echo $user['sdt']; ?>" class="form-control-row read-only" readonly></div>
                            <div class="form-group-row"><label>Email</label><input type="email" name="email" class="form-control-row" value="<?php echo $user['email']; ?>"></div>
                            <div style="text-align: center; margin-top: 30px;"><button type="submit" class="btn-save">LƯU THAY ĐỔI</button></div>
                        </form>
                    </div>

                <?php elseif ($tab == 'password'): ?>
                    <div class="content-header">
                        <h2>Đổi mật khẩu</h2>
                    </div>
                    <div style="padding: 30px;">
                        <form action="taikhoan/xuly_doimatkhau.php" method="POST" class="form-box">
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
        <a href="index.php" class="nav-item">
            <i class="fas fa-home"></i> <span>Trang Chủ</span>
        </a>
        <div class="nav-item active">
            <i class="fas fa-user"></i> <span>Tài Khoản</span>
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

        window.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(el => el.style.display = 'none');
                }, 3000);
            }
        });
    </script>

</body>

</html>
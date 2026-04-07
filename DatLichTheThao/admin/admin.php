<?php
session_start();
require_once '../config/db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role_chu']) || $_SESSION['user_role_chu'] !== 'admin') {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}
    
// Hàm hỗ trợ đặt thông báo
function set_msg($content) {
    $_SESSION['msg_admin'] = $content;
}
// Xử lý thêm chủ sân
if (isset($_POST['add_chu_san'])) {
    $ho_ten = $conn->real_escape_string($_POST['ho_ten']);
    $email = $conn->real_escape_string($_POST['email']);
    $sdt = $conn->real_escape_string($_POST['sdt']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Kiểm tra trùng email hoặc SĐT trong TOÀN BỘ hệ thống
    $check_email = $conn->query("SELECT id, vai_tro FROM nguoi_dung WHERE email='$email'");
    $check_sdt = $conn->query("SELECT id, vai_tro FROM nguoi_dung WHERE sdt='$sdt'");
    $check_ho_ten= $conn->query("SELECT id FROM nguoi_dung WHERE ho_ten='$ho_ten'");

    if ($check_email->num_rows > 0) {
        $existing = $check_email->fetch_assoc();
        $role = ($existing['vai_tro'] == 'chu_san') ? 'chủ sân' : 'khách hàng';
        set_msg("Email đã được sử dụng bởi một $role khác!");
    } elseif ($check_sdt->num_rows > 0) {
        $existing = $check_sdt->fetch_assoc();
        $role = ($existing['vai_tro'] == 'chu_san') ? 'chủ sân' : 'khách hàng';
        set_msg("SĐT đã được sử dụng bởi một $role khác!");
    } elseif($check_ho_ten->num_rows > 0) {
        set_msg("Họ tên đã tồn tại!");
    }else {
        if($conn->query("INSERT INTO nguoi_dung (ho_ten, email, sdt, mat_khau, vai_tro, trang_thai) VALUES ('$ho_ten', '$email', '$sdt', '$password', 'chu_san', 1)")) {
            set_msg("Thêm chủ sân thành công!");
        } else {
            set_msg("Lỗi: " . $conn->error);
        }
    }
    // if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     set_msg("Email không hợp lệ!");
    // }
    // if(!preg_match('/^0[0-9]{9}$/', $sdt)) {
    //     set_msg("Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0!");
    // }
    // if(strlen($_POST['password']) < 6) {
    //     set_msg("Mật khẩu phải có ít nhất 6 ký tự!");
    // }

    header("Location: admin.php");
    exit();
}

// Xử lý thêm cơ sở mới
if (isset($_POST['add_coso'])) {
    $chu_san_id = (int)$_POST['chu_san_id'];
    $mon_the_thao_id = (int)$_POST['mon_the_thao_id'];
    $ten_co_so = $conn->real_escape_string($_POST['ten_co_so']);
    $dia_chi = $conn->real_escape_string($_POST['dia_chi']);
    $gio_mo_cua = $conn->real_escape_string($_POST['gio_mo_cua']);
    $gio_dong_cua = $conn->real_escape_string($_POST['gio_dong_cua']);
    $hotline = $conn->real_escape_string($_POST['hotline']);
    
    $sql = "INSERT INTO co_so (ten_co_so, dia_chi_cu_the, gio_mo_cua, gio_dong_cua, hotline, chu_san_id, mon_the_thao_id) 
            VALUES ('$ten_co_so', '$dia_chi', '$gio_mo_cua', '$gio_dong_cua', '$hotline', $chu_san_id, $mon_the_thao_id)";
    
    $check_ten_co_so = $conn->query("SELECT id FROM co_so WHERE ten_co_so='$ten_co_so' AND chu_san_id=$chu_san_id");

    if($check_ten_co_so->num_rows > 0) {
        set_msg("Cơ sở với tên '$ten_co_so' đã tồn tại cho chủ sân này!");
        header("Location: admin.php");
        exit();
    }

    if(!preg_match('/^0[0-9]{9}$/', $hotline)) {
        set_msg("Số hotline phải gồm 10 chữ số và bắt đầu bằng số 0!");
        header("Location: admin.php");
        exit();
    }

    if($conn->query($sql)) {
        set_msg("Thêm cơ sở thành công!");
    } else {
        set_msg("Lỗi: " . $conn->error);
    }
    header("Location: admin.php");
    exit();
}

// Xử lý xóa cơ sở
if (isset($_GET['del'])) {
    $co_so_id = (int)$_GET['del'];
    
    if($conn->query("DELETE FROM co_so WHERE id = $co_so_id")) {
        set_msg("Xóa cơ sở thành công!");
    } else {
        set_msg("Lỗi khi xóa: " . $conn->error);
    }
    
    header("Location: admin.php");
    exit();
}

// Xử lý xóa chủ sân
if (isset($_POST['delete_chu_san'])) {
    $chu_san_id = (int)$_POST['delete_chu_san'];

    $check = $conn->query("SELECT COUNT(*) AS total FROM co_so WHERE chu_san_id = $chu_san_id");
    $row = $check->fetch_assoc();
    if ($row['total'] > 0) {
        $_SESSION['confirm_delete'] = [
            'chu_san_id' => $chu_san_id,
            'total' => $row['total']
        ];
        header("Location: admin.php");
        exit();
    }
    // Không có cơ sở → xóa luôn
    if ($conn->query("DELETE FROM nguoi_dung WHERE id = $chu_san_id")) {
        set_msg("Xóa chủ sân thành công!");
    } else {
        set_msg("Lỗi: " . $conn->error);
    }
    header("Location: admin.php");
    exit();
}

if (isset($_POST['confirm_delete_chu_san'])) {
    $chu_san_id = (int)$_POST['chu_san_id'];

    if ($conn->query("DELETE FROM nguoi_dung WHERE id = $chu_san_id")) {
        set_msg("Đã xóa chủ sân và toàn bộ cơ sở liên quan!");
    } else {
        set_msg("Lỗi khi xóa!");
    }

    unset($_SESSION['confirm_delete']);
    header("Location: admin.php");
    exit();
}

// Hủy xác nhận xóa
if (isset($_GET['cancel_delete'])) {
    unset($_SESSION['confirm_delete']);
    header("Location: admin.php");
    exit();
}

// Xử lý cập nhật chủ sân
if (isset($_POST['update_chu_san'])) {
    $id = (int)$_POST['chu_san_id'];
    $ho_ten = $conn->real_escape_string($_POST['ho_ten']);
    $email = $conn->real_escape_string($_POST['email']);
    $sdt = $conn->real_escape_string($_POST['sdt']);

    // Kiểm tra trùng
    $check_email = $conn->query("SELECT id FROM nguoi_dung WHERE email='$email' AND id != $id");
    if ($check_email->num_rows > 0) {
        set_msg("Email đã tồn tại!");
        header("Location: admin.php?edit_id=$id");
        exit();
    }

    $check_sdt = $conn->query("SELECT id FROM nguoi_dung WHERE sdt='$sdt' AND id != $id");
    if ($check_sdt->num_rows > 0) {
        set_msg("SĐT đã tồn tại!");
        header("Location: admin.php?edit_id=$id");
        exit();
    }

    // Cập nhật với/không có mật khẩu
    $sql = "UPDATE nguoi_dung SET ho_ten='$ho_ten', email='$email', sdt='$sdt'";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql .= ", mat_khau='$password'";
    }
    $sql .= " WHERE id=$id";

    if($conn->query($sql)) {
        set_msg("Cập nhật thành công!");
    } else {
        set_msg("Lỗi: " . $conn->error);
    }

    header("Location: admin.php");
    exit();
}

// Lấy thông báo từ session
$msg = "";
$confirm_delete_data = null;
if (isset($_SESSION['msg_admin'])) {
    $msg = $_SESSION['msg_admin'];  
    unset($_SESSION['msg_admin']);
}

// Xử lý tìm kiếm - tìm theo SĐT người dùng
$search_sdt = isset($_GET['search_sdt']) ? $conn->real_escape_string($_GET['search_sdt']) : '';
$where_clause = "WHERE vai_tro='chu_san'";
if(!empty($search_sdt)) {
    $where_clause .= " AND sdt LIKE '$search_sdt%'";
}

// LẤY DANH SÁCH
$chu_sans = $conn->query("SELECT * FROM nguoi_dung $where_clause ORDER BY id DESC");

// Lấy danh sách môn thể thao
$mon_the_thao = $conn->query("SELECT * FROM mon_the_thao ORDER BY id");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - Quản lý</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
    <div class="headerad">
        <h1>Quản trị hệ thống - <?php echo $_SESSION['user_name_chu']; ?></h1>
        <a href="../taikhoan/dangxuat.php" class="logout-btnad">Đăng xuất</a>
    </div>

    <!-- Modal xác nhận xóa -->
    <?php if (isset($_SESSION['confirm_delete'])): ?>
    <div class="confirm-modal show">
        <div class="confirm-content">
            <p>Chủ sân hiện có <strong><?php echo $_SESSION['confirm_delete']['total']; ?></strong> cơ sở.</p>
            <p>Bạn có chắc chắn muốn <strong>xóa toàn bộ dữ liệu</strong> không?</p>
            <div class="confirm-buttons">
                <form method="post" style="display:inline">
                    <input type="hidden" name="chu_san_id"
                        value="<?php echo $_SESSION['confirm_delete']['chu_san_id']; ?>">
                    <button type="submit" name="confirm_delete_chu_san" class="btn-deletead">Xóa tất cả</button>
                </form>
            <a href="admin.php?cancel_delete=1" class="btn-cancel-delete">Hủy</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($msg): ?>
        <div class="msgad" id="status-msg"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="containerad">
        <div class="left-panelad">
            <?php
            $editData = null;
            if (isset($_GET['edit_id'])) {
                $id = (int)$_GET['edit_id'];
                $res = $conn->query("SELECT * FROM nguoi_dung WHERE id = $id");
                $editData = $res->fetch_assoc();
            }
            ?>

            <div class="form-boxad">
                <h2><?php echo isset($editData) ? 'Sửa' : 'Thêm'; ?> chủ sân</h2>

                <form method="post">
                    <?php if(isset($editData)): ?>
                        <input type="hidden" name="chu_san_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>

                    <input type="text" name="ho_ten" value="<?php echo $editData['ho_ten'] ?? ''; ?>" placeholder="Họ tên" required>
                    <input type="email" name="email" value="<?php echo $editData['email'] ?? ''; ?>" placeholder="Email" required>
                    <input type="text" name="sdt" value="<?php echo $editData['sdt'] ?? ''; ?>" placeholder="SĐT" required>
                    <input type="password" name="password" placeholder="<?php echo isset($editData) ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu'; ?>" <?php echo isset($editData) ? '' : 'required'; ?>>

                    <button class="btn-addad" name="<?php echo isset($editData) ? 'update_chu_san' : 'add_chu_san'; ?>">
                        <?php echo isset($editData) ? 'Cập nhật' : 'Thêm chủ sân'; ?>
                    </button>

                    <?php if(isset($editData)): ?>
                        <a href="admin.php" class="btn-addad" style="display: block; text-align: center; margin-top: 10px; background: #757575; text-decoration: none;">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <script>
            window.onload = function() {
                const msgBox = document.getElementById('status-msg');
                if (msgBox) {
                    setTimeout(function() {
                        msgBox.style.opacity = '0';
                        setTimeout(function() {
                            msgBox.style.display = 'none';
                        }, 500);
                    }, 3000); 
                }
            };

            // Tìm kiếm không reload trang
            let searchTimeout;
            function handleSearchInput(input) {
                clearTimeout(searchTimeout);
                const searchValue = input.value;
                
                searchTimeout = setTimeout(function() {
                    const url = new URL(window.location);
                    if(searchValue.length > 0) {
                        url.searchParams.set('search_sdt', searchValue);
                    } else {
                        url.searchParams.delete('search_sdt');
                    }
                    
                    if(url.toString() !== window.location.toString()) {
                        window.location.href = url.toString();
                    }
                }, 800);
            }

            // Giữ focus vào ô input sau khi trang load
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.querySelector('input[name="search_sdt"]');
                if(searchInput && searchInput.value) {
                    searchInput.focus();
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                }
            });
        </script>

        <div class="right-panelad">
            <div class="section-headerad">
                <h2 class="section-titlead">Danh sách chủ sân (<?php echo $chu_sans->num_rows; ?>)</h2>
                <div class="search-boxad">
                    <input type="text" 
                           name="search_sdt"
                           placeholder="Tìm theo SĐT..." 
                           value="<?php echo htmlspecialchars($search_sdt); ?>"
                           oninput="handleSearchInput(this)"
                           autocomplete="off">
                    <?php if(!empty($search_sdt)): ?>
                        <a href="admin.php" class="btn-resetad">✕ Reset</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php while($cs = $chu_sans->fetch_assoc()): 
                // Đếm số cơ sở
                $count_coso = $conn->query("SELECT COUNT(*) as total FROM co_so WHERE chu_san_id = {$cs['id']}")->fetch_assoc()['total'];
            ?>
                <div class="chu-san-cardad">
                    <div class="chu-san-headerad">
                        <div class="chu-san-infoad">
                            <h3><?php echo $cs['ho_ten']; ?></h3>
                            <p>📧 <?php echo $cs['email']; ?></p>
                            <p>📞 <?php echo $cs['sdt']; ?></p>
                        </div>
                        <div class="action-buttons">
                            <button class="btn-togglead"
                                onclick="document.getElementById('cs<?php echo $cs['id']; ?>').classList.toggle('show')">
                                Xem cơ sở
                            </button>
                            
                            <a href="admin.php?edit_id=<?php echo $cs['id']; ?>"
                                class="btn-togglead btn-editad">
                                Sửa chủ sân
                            </a>

                            <form method="post" class="inline-form">
                                <input type="hidden" name="delete_chu_san" value="<?php echo $cs['id']; ?>">
                                <button type="submit" class="btn-togglead btn-deletead">
                                    Xóa chủ sân
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="coso-listad" id="cs<?php echo $cs['id']; ?>">
                        <h4>Các cơ sở đang quản lý:</h4>
                        <?php
                        $cosos = $conn->query("SELECT cs.*, mtt.ten_mon FROM co_so cs 
                                              LEFT JOIN mon_the_thao mtt ON cs.mon_the_thao_id = mtt.id 
                                              WHERE cs.chu_san_id={$cs['id']}");
                        if($cosos->num_rows > 0):
                            while($co = $cosos->fetch_assoc()):
                        ?>
                            <div class="coso-itemad">
                                <div>
                                    <strong><?php echo $co['ten_co_so']; ?></strong>
                                    <span style="background:#0d6e5d;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px">
                                        <?php echo $co['ten_mon'] ?? 'N/A'; ?>
                                    </span><br>
                                    <small>📍 <?php echo $co['dia_chi_cu_the']; ?></small><br>
                                    <small>🕐 <?php echo $co['gio_mo_cua']; ?> - <?php echo $co['gio_dong_cua']; ?></small><br>
                                    <small>📞 Hotline: <?php echo $co['hotline']; ?></small>
                                </div>
                                <button class="btn-deletead" onclick="if(confirm('Xác nhận xóa?'))location.href='?del=<?php echo $co['id']; ?>'">
                                    Xóa
                                </button>
                            </div>
                        <?php 
                            endwhile;
                        else:
                            echo "<p style='color:#999'>Chưa có cơ sở nào</p>";
                        endif;
                        ?>
                        
                        <div class="add-coso-formad">
                            <h4>➕ Thêm cơ sở mới</h4>
                            <form method="POST">
                                <input type="hidden" name="chu_san_id" value="<?php echo $cs['id']; ?>">
                                
                                <label>Tên sân:</label>
                                <input type="text" name="ten_co_so" placeholder="VD: Sân bóng ABC" required autocomplete="off">
                                
                                <label>Môn thể thao:</label>
                                <select name="mon_the_thao_id" required>
                                    <option value="">-- Chọn môn thể thao --</option>
                                    <?php 
                                    $mon_result = $conn->query("SELECT * FROM mon_the_thao ORDER BY id");
                                    while($mon = $mon_result->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $mon['id']; ?>"><?php echo $mon['ten_mon']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                                
                                <label>Địa chỉ:</label>
                                <input type="text" name="dia_chi" placeholder="VD: 123 Đường XYZ, Quận 1" required autocomplete="off">
                                
                                <label>Giờ hoạt động:</label>
                                <div class="form-rowad">
                                    <input type="time" name="gio_mo_cua" required>
                                    <input type="time" name="gio_dong_cua" required>
                                </div>
                                
                                <label>Hotline:</label>
                                <input type="text" name="hotline" placeholder="VD: 0901234567" required autocomplete="off">
                                
                                <button class="btn-addad" name="add_coso">XÁC NHẬN THÊM CƠ SỞ</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
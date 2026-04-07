<?php
// 1. Khởi động Session & Kết nối CSDL
session_start();
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id_chu']) || $_SESSION['user_role_chu'] !== 'chu_san') {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}

$user_id = $_SESSION['user_id_chu'];
$co_so_id = 0;

//Lấy từ url khi người dùng click chọn cơ sở hoặc link trực tiếp
if (isset($_GET['id'])) {
    $req_id = intval($_GET['id']);
    // Check quyền sở hữu
    $check = $conn->query("SELECT id FROM co_so WHERE id = $req_id AND chu_san_id = $user_id");
    if ($check->num_rows > 0) {
        $co_so_id = $req_id;
        // LƯU VÀO SESSION ĐỂ CÁC TRANG KHÁC BIẾT
        $_SESSION['current_co_so_id'] = $co_so_id;
    }
}
//Lấy từ Session khi người dùng chuyển tab hoặc reload
elseif (isset($_SESSION['current_co_so_id'])) {
    $sess_id = $_SESSION['current_co_so_id'];
    // Check lại quyền sở hữu
    $check = $conn->query("SELECT id FROM co_so WHERE id = $sess_id AND chu_san_id = $user_id");
    if ($check->num_rows > 0) {
        $co_so_id = $sess_id;
    }
}

// Nếu chưa có gì, lấy cơ sở đầu tiên trong DB
if ($co_so_id == 0) {
    $sql_first = "SELECT id FROM co_so WHERE chu_san_id = $user_id LIMIT 1";
    $res_first = $conn->query($sql_first);
    if ($res_first && $res_first->num_rows > 0) {
        $row = $res_first->fetch_assoc();
        $co_so_id = $row['id'];

        // Nếu có nhiều hơn 1 cơ sở mà chưa chọn thì về trang chọn
        $sql_count = "SELECT count(*) as total FROM co_so WHERE chu_san_id = $user_id";
        $total = $conn->query($sql_count)->fetch_assoc()['total'];
        if ($total > 1) {
            header("Location: chon_co_so.php");
            exit();
        }

        // Lưu session mặc định
        $_SESSION['current_co_so_id'] = $co_so_id;
    } else {
        die("Tài khoản chưa có cơ sở nào.");
    }
}

// LẤY THÔNG TIN CHI TIẾT CƠ SỞ
$sql_info_coso = "SELECT c.id, c.ten_co_so, c.gio_mo_cua, c.gio_dong_cua,
                  (SELECT url_hinh FROM hinh_anh_san WHERE co_so_id = c.id ORDER BY avata DESC LIMIT 1) as logo_url
                  FROM co_so c 
                  WHERE c.id = $co_so_id 
                  LIMIT 1";

$res_info_coso = $conn->query($sql_info_coso);
$row_coso_info = $res_info_coso->fetch_assoc();

// Gán dữ liệu hiển thị
$coso_name = $row_coso_info['ten_co_so'];
$coso_logo = !empty($row_coso_info['logo_url']) ? $row_coso_info['logo_url'] : '../assets/img/logo.png';

$open_str = $row_coso_info['gio_mo_cua'] ? $row_coso_info['gio_mo_cua'] : '05:00:00';
$close_str = $row_coso_info['gio_dong_cua'] ? $row_coso_info['gio_dong_cua'] : '23:00:00';

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$open_time = strtotime("$selected_date $open_str");
$close_time = strtotime("$selected_date $close_str");

$time_slots = [];
for ($t = $open_time; $t < $close_time; $t += 1800) {
    $time_slots[] = date('H:i', $t);
}

$ds_san = [];
$sql_san = "SELECT * FROM san WHERE co_so_id = $co_so_id";
$res_san = $conn->query($sql_san);
if ($res_san) {
    while ($row = $res_san->fetch_assoc()) {
        $ds_san[] = $row;
    }
}

$sql_booking = "SELECT id, san_id, gio_bat_dau, gio_ket_thuc, trang_thai_don 
                FROM dat_lich 
                WHERE ngay_dat = '$selected_date' 
                AND co_so_id = '$co_so_id'
                AND trang_thai_don != 'da_huy'";

$res_booking = $conn->query($sql_booking);
$bookings = [];
if ($res_booking) {
    while ($row = $res_booking->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// --- THÊM ĐOẠN NÀY: LẤY DỮ LIỆU KHÓA THEO GIỜ ---
$sql_locks = "SELECT id, san_id, gio_bat_dau, gio_ket_thuc 
              FROM khoa_san_theo_gio 
              WHERE ngay_khoa = '$selected_date' 
              AND san_id IN (SELECT id FROM san WHERE co_so_id = $co_so_id)";
$res_locks = $conn->query($sql_locks);
$locked_hours = []; // Mảng chứa thông tin khóa
if ($res_locks) {
    while ($row = $res_locks->fetch_assoc()) {
        $locked_hours[] = $row;
    }
}
// ------------------------------------------------

$booked_slots = []; // Mảng chứa trạng thái từng ô
foreach ($time_slots as $slot) {
    $current_slot_timestamp = strtotime("$selected_date $slot");

    // 1. DUYỆT BOOKING (Cũ)
    foreach ($bookings as $booking) {
        $booking_start = strtotime("$selected_date " . $booking['gio_bat_dau']);
        $booking_end   = strtotime("$selected_date " . $booking['gio_ket_thuc']);

        if ($current_slot_timestamp >= $booking_start && $current_slot_timestamp < $booking_end) {
            $booked_slots[$booking['san_id']][$slot] = [
                'type' => 'booking', // Đánh dấu là booking
                'status' => $booking['trang_thai_don'],
                'id' => $booking['id']
            ];
        }
    }

    // 2. DUYỆT KHÓA THEO GIỜ (Mới)
    // Nếu ô này đã bị khóa theo giờ, ghi đè hoặc thêm vào booked_slots
    foreach ($locked_hours as $lock) {
        $lock_start = strtotime("$selected_date " . $lock['gio_bat_dau']);
        $lock_end   = strtotime("$selected_date " . $lock['gio_ket_thuc']);

        if ($current_slot_timestamp >= $lock_start && $current_slot_timestamp < $lock_end) {
            // Lưu trạng thái là 'locked_hour'
            $booked_slots[$lock['san_id']][$slot] = [
                'type' => 'lock',
                'status' => 'khoa',
                'id' => $lock['id']
            ];
        }
    }
}
$current_timestamp = time();
$is_today = ($selected_date == date('Y-m-d'));
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


    <header class="banner">
        <div class="logo">
            <img src="../<?php echo $coso_logo; ?>" /> <span class="brand-name-coso"><?php echo $coso_name; ?></span>
        </div>

        <div class="buttons">
            <?php if (isset($_SESSION['user_id_chu'])): ?>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="quanly.php" style="font-size:13px; color: white;">
                        Xin chào, <b><?php echo $_SESSION['user_name_chu']; ?></b>
                    </a>
                    <i class="fas fa-user-circle" style="font-size: 20px; color: white;"></i>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <div class="toolbar">
        <div class="legend">
            <div class="legend-item">
                <div class="box box-white"></div> Trống
            </div>
            <div class="legend-item">
                <div class="box box-red"></div> Đã Đặt
            </div>
            <div class="legend-item">
                <div class="box box-yellow"></div> Chờ xác nhận
            </div>
            <div class="legend-item">
                <div class="box box-orange"></div> Khóa
            </div>
        </div>
        <div class="toolbar-actions">
            <button class="btn-tool btn-add" onclick="openModalAdd()"><i class="fas fa-plus"></i> Thêm</button>
            <button class="btn-tool btn-edit" onclick="openModalList('edit')"><i class="fas fa-edit"></i> Sửa</button>
            <button class="btn-tool btn-del" onclick="openModalList('delete')"><i class="fas fa-trash"></i> Xóa</button>
        </div>
        <form id="dateForm" method="GET" class="date-picker">
            <input type="hidden" name="id" value="<?php echo $co_so_id; ?>">
            <span style="font-weight:bold;"><?php echo date('d/m/Y', strtotime($selected_date)); ?></span>
            <i class="far fa-calendar-alt"></i>
            <input type="date" name="date" value="<?php echo $selected_date; ?>" onchange="document.getElementById('dateForm').submit()">
        </form>
    </div>

    <div class="schedule-container">
        <table>
            <thead>
                <tr>
                    <th class="col-court-name" style="background-color:#b2dfdb;">Sân</th>

                    <?php foreach ($time_slots as $time): ?>
                        <th>
                            <span class="time-label"><?php echo $time; ?></span>
                        </th>
                    <?php endforeach; ?>

                    <th style="min-width: 0px; padding: 0; border: none; width: 0;">
                        <span class="time-label"><?php echo date('G:i', $close_time); ?></span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ds_san as $san): ?>
                    <tr>
                        <td class="col-court-name"><?php echo $san['ten_san']; ?></td>

                        <?php foreach ($time_slots as $time):
                            $status_class = 'available';
                            $onclick = 'onclick="selectCell(this)"';
                            $slot_timestamp = strtotime("$selected_date $time");
                            $cell_content = '';

                            // 1. Kiểm tra trạng thái TOÀN BỘ SÂN (Ưu tiên cao nhất)
                            if (isset($san['trang_thai']) && $san['trang_thai'] == 'khoa') {
                                $status_class = 'past';
                                $onclick = '';
                            }
                            // 2. Kiểm tra quá khứ
                            elseif (strtotime($selected_date) < strtotime(date('Y-m-d')) || ($is_today && $slot_timestamp < $current_timestamp)) {
                                $status_class = 'past';
                                $onclick = '';
                            }
                            // 3. Kiểm tra trạng thái ĐẶT hoặc KHÓA THEO GIỜ
                            elseif (isset($booked_slots[$san['id']][$time])) {
                                $info = $booked_slots[$san['id']][$time];

                                if ($info['type'] == 'lock') { // Đã khớp với bước 2
                                    $status_class = 'lock';
                                    $onclick = 'onclick="selectCell(this)"';
                                } elseif ($info['type'] == 'booking') {
                                    if ($info['status'] == 'cho_xac_nhan') {
                                        $status_class = 'pending';
                                    } else {
                                        $status_class = 'booked';
                                    }
                                    $onclick = 'onclick="selectCell(this)"';
                                }
                            }
                        ?>
                            <td class="ocell <?php echo $status_class; ?>"
                                data-san="<?php echo $san['id']; ?>"
                                data-time="<?php echo $time; ?>"
                                data-status="<?php echo $status_class; ?>"
                                <?php echo $onclick; ?>>
                                <?php echo $cell_content; ?>
                            </td>
                        <?php endforeach; ?>

                        <td style="border: none; padding: 0; width: 0;"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal-overlay" id="modalAdd">
        <div class="modal-form">
            <div class="modal-header">
                <span>Thêm Sân Mới</span>
                <i class="fas fa-times" onclick="closeModal('modalAdd')" style="cursor:pointer"></i>
            </div>
            <div class="form-group">
                <label class="form-label">Tên sân </label>
                <input type="text" id="add_ten_san" class="form-input" placeholder="Nhập tên sân">
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-close" onclick="closeModal('modalAdd')">Hủy</button>
                <button class="btn-modal btn-save" onclick="submitAddSan()">Lưu</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalList">
        <div class="modal-form">
            <div class="modal-header">
                <span id="listTitle">Danh sách sân</span>
                <i class="fas fa-times" onclick="closeModal('modalList')" style="cursor:pointer"></i>
            </div>
            <div id="listSanContent" style="max-height: 300px; overflow-y: auto;">
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalEdit">
        <div class="modal-form">
            <div class="modal-header">
                <span>Cập nhật thông tin</span>
                <i class="fas fa-times" onclick="closeModal('modalEdit')" style="cursor:pointer"></i>
            </div>
            <input type="hidden" id="edit_san_id">

            <div class="form-group">
                <label class="form-label">Tên sân</label>
                <input type="text" id="edit_ten_san" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Hệ số giá </label>
                <input type="number" step="0.1" id="edit_he_so_gia" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select id="edit_trang_thai" class="form-input">
                    <option value="mo">Mở</option>
                    <option value="khoa">Khóa</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-close" onclick="closeModal('modalEdit')">Hủy</button>
                <button class="btn-modal btn-save" onclick="submitEditSan()">Cập nhật</button>
            </div>
        </div>
    </div>

    <div class="switch-bar">
        <button class="btn-tool btn-placed" onclick="handleBookingClick()"><i class="fas fa-check-circle"></i> Đặt</button>
        <button class="btn-tool btn-lock" onclick="handleLockSlots()"><i class="fas fa-lock"></i>Khóa</button>
        <button class="btn-tool btn-unlock" onclick="handleUnlockSlots()"><i class="fas fa-lock-open"></i>Mở Khóa</button>
    </div>

    <div class="bottom-nav">
        <div class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </div>
        <a href="quanly.php" class="nav-item">
            <i class="fas fa-user-shield"></i>
            <span>Quản Lý</span>
        </a>
    </div>

    <div class="modal-overlay" id="modalBooking" style="display: none;">
        <div class="modal-form">
            <div class="modal-header">
                <span>Xác nhận đặt sân</span>
                <i class="fas fa-times" onclick="closeModal('modalBooking')" style="cursor:pointer"></i>
            </div>

            <div class="modal-body" style="padding: 15px;">
                <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                    <p><i class="fas fa-calendar-day"></i> Ngày: <b id="book_date">...</b></p>
                    <p><i class="fas fa-table-tennis"></i> Sân: <b id="book_san_name">...</b></p>
                    <p><i class="far fa-clock"></i> Khung giờ: <b id="book_time_range">...</b></p>
                    <p><i class="fas fa-money-bill-wave"></i> Tạm tính: <b id="book_price" style="color: #d35400;">0 VNĐ</b></p>
                </div>

                <div class="form-group">
                    <label class="form-label">SĐT Khách vãng lai <span style="color:red">*</span></label>
                    <input type="text" id="book_phone" class="form-input" placeholder="Nhập số điện thoại khách">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-close" onclick="closeModal('modalBooking')">Hủy</button>
                <button class="btn-modal btn-save" onclick="submitBookingGuest()">Xác nhận Đặt</button>
            </div>
        </div>
    </div>

    <script>
        let bookingData = {
            san_id: null,
            slots: [],
            date: ''
        };

        function handleBookingClick() {
            // 1. Lấy các ô đã chọn
            const selectedCells = document.querySelectorAll('.ocell.selected');
            if (selectedCells.length === 0) {
                showNotification('Vui lòng chọn khung giờ cần đặt!');
                return;
            }

            // 2. Kiểm tra tính hợp lệ (Cùng 1 sân)
            let firstSanId = selectedCells[0].dataset.san;
            let slots = [];

            // Sắp xếp các ô theo thời gian để hiển thị cho đẹp
            let sortedCells = Array.from(selectedCells).sort((a, b) => {
                return a.dataset.time.localeCompare(b.dataset.time);
            });

            for (let cell of sortedCells) {
                if (cell.dataset.san !== firstSanId) {
                    showNotification('Vui lòng chỉ chọn các ô trong cùng 1 sân!');
                    return;
                }
                // Kiểm tra nếu ô đã bị đặt hoặc khóa
                if (cell.dataset.status !== 'available' && cell.dataset.status !== 'past') {
                    // Tùy logic, thường admin có thể đặt đè, nhưng ở đây ta chặn cho an toàn
                    if (cell.dataset.status === 'booked' || cell.dataset.status === 'lock') {
                        showNotification('Không thể đặt vào ô đã có người đặt hoặc bị khóa!');
                        return;
                    }
                }
                slots.push(cell.dataset.time);
            }

            // 3. Chuẩn bị dữ liệu gửi đi tính toán
            bookingData.san_id = firstSanId;
            bookingData.slots = slots;
            bookingData.date = "<?php echo $selected_date; ?>"; // Lấy từ PHP

            // 4. Gọi Ajax để lấy thông tin chi tiết (Giá tiền, Tên sân...)
            fetch('ql_dat_san.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=preview&san_id=${bookingData.san_id}&date=${bookingData.date}&slots=${JSON.stringify(bookingData.slots)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 5. Hiển thị lên Modal
                        document.getElementById('book_date').innerText = data.formatted_date;
                        document.getElementById('book_san_name').innerText = data.san_name;
                        document.getElementById('book_time_range').innerText = data.time_range;
                        document.getElementById('book_price').innerText = data.total_price_vnd;

                        // Reset form input
                        document.getElementById('book_phone').value = '';

                        // Hiện Modal
                        document.getElementById('modalBooking').style.display = 'flex';
                    } else {
                        showNotification('Lỗi: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Lỗi kết nối server khi tính tiền!');
                });
        }

        function submitBookingGuest() {
            const sdt = document.getElementById('book_phone').value;

            if (!sdt) {
                showNotification('Vui lòng nhập số điện thoại khách hàng!');
                return;
            }

            if (!confirm('Xác nhận đặt sân cho khách này?')) return;

            // Gửi yêu cầu đặt chính thức
            fetch('ql_dat_san.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=book&san_id=${bookingData.san_id}&date=${bookingData.date}&slots=${JSON.stringify(bookingData.slots)}&sdt=${sdt}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showNotification('Đặt sân thành công!');
                        location.reload();
                    } else {
                        showNotification('Lỗi: ' + data.message);
                    }
                })
                .catch(err => console.error(err));
        }

        function selectCell(element) {
            const currentStatus = element.dataset.status;
            const selectedCells = document.querySelectorAll('.ocell.selected');

            // Nếu đã có ô được chọn thì kiểm tra trạng thái
            if (selectedCells.length > 0) {
                const firstStatus = selectedCells[0].dataset.status;

                if (currentStatus !== firstStatus) {
                    showNotification('Chỉ được chọn các ô có cùng trạng thái!');
                    return;
                }
            }

            // Toggle chọn / bỏ chọn
            element.classList.toggle('selected');
        }

        const CO_SO_ID = <?php echo $co_so_id; ?>;

        // XỬ LÝ MODAL
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        //THÊM SÂN
        function openModalAdd() {
            document.getElementById('modalAdd').style.display = 'flex';
        }

        function submitAddSan() {
            const tenSan = document.getElementById('add_ten_san').value;
            if (!tenSan) {
                showNotification('Vui lòng nhập tên sân', 'danger');
                return;
            }

            fetch('ql_update_san.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=add&co_so_id=${CO_SO_ID}&ten_san=${tenSan}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Reload trang, PHP session sẽ hiển thị alert xanh
                        location.reload();
                    } else {
                        //Giữ nguyên popup, hiển thị alert đỏ
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(err => showNotification('Lỗi kết nối server', 'danger'));
        }

        // MỞ DANH SÁCH ĐỂ SỬA / XÓA
        function openModalList(mode) {
            document.getElementById('modalList').style.display = 'flex';
            document.getElementById('listTitle').innerText = (mode === 'edit') ? "Chọn sân để sửa" : "Chọn sân để xóa";

            const listContent = document.getElementById('listSanContent');
            listContent.innerHTML = '<p>Đang tải...</p>';

            // Thêm he_so_gia và trang_thai vào object
            const sanList = [
                <?php foreach ($ds_san as $s) {
                    // Nếu giá trị null thì gán mặc định
                    $hs = isset($s['he_so_gia']) ? $s['he_so_gia'] : 1;
                    $tt = isset($s['trang_thai']) ? $s['trang_thai'] : 'mo';

                    echo "{id: {$s['id']}, name: '{$s['ten_san']}', heso: '$hs', status: '$tt'},";
                } ?>
            ];

            let html = '';
            sanList.forEach(s => {
                let btn = '';
                if (mode === 'edit') {
                    // Truyền thêm s.heso và s.status vào hàm mở form
                    btn = `<button class="btn-icon-action" style="background:#f39c12; color:white;" 
                           onclick="openEditForm(${s.id}, '${s.name}', '${s.heso}', '${s.status}')">
                           <i class="fas fa-pen"></i></button>`;
                } else {
                    btn = `<button class="btn-icon-action" style="background:#c0392b; color:white;" 
                           onclick="submitDeleteSan(${s.id})"><i class="fas fa-trash"></i></button>`;
                }

                html += `<div class="list-san-item">
                            <span>${s.name}</span>
                            ${btn}
                         </div>`;
            });
            listContent.innerHTML = html;
        }

        // SỬA SÂN
        function openEditForm(id, name, heso, status) {
            closeModal('modalList'); // Đóng list
            document.getElementById('modalEdit').style.display = 'flex'; // Mở form

            // Điền dữ liệu cũ vào form
            document.getElementById('edit_san_id').value = id;
            document.getElementById('edit_ten_san').value = name;

            // Điền hệ số giá và trạng thái
            document.getElementById('edit_he_so_gia').value = heso;
            document.getElementById('edit_trang_thai').value = status;
        }

        // GỬI DỮ LIỆU CẬP NHẬT
        function submitEditSan() {
            const id = document.getElementById('edit_san_id').value;
            const ten = document.getElementById('edit_ten_san').value;
            const heso = document.getElementById('edit_he_so_gia').value;
            const trangthai = document.getElementById('edit_trang_thai').value;

            fetch('ql_update_san.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=edit&san_id=${id}&ten_san=${ten}&he_so_gia=${heso}&trang_thai=${trangthai}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(err => showNotification('Lỗi kết nối server', 'danger'));
        }

        // XÓA SÂN
        function submitDeleteSan(id) {
            if (confirm('Bạn có chắc chắn muốn xóa sân này? Hành động không thể hoàn tác!')) {
                fetch('ql_update_san.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=delete&san_id=${id}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            showNotification(data.message, 'danger');
                        }
                    })
                    .catch(err => showNotification('Lỗi kết nối server', 'danger'));
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

        function showNotification(message, type = 'danger') {
            const area = document.getElementById('notification-area');

            // Tạo phần tử thông báo
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerText = message;

            // Thêm vào đầu khu vực thông báo
            area.appendChild(alertDiv);

            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        function handleLockSlots() {
            // 1. Lấy tất cả các ô đang được chọn (class .selected)
            const selectedCells = document.querySelectorAll('.ocell.selected');

            if (selectedCells.length === 0) {
                showNotification('Vui lòng chọn ít nhất một ô để khóa!');
                return;
            }

            // 2. Kiểm tra xem có ô nào đã bị đặt hoặc đã khóa rồi không
            // (Tùy logic, nếu muốn cho phép khóa đè thì bỏ qua bước này)
            for (let cell of selectedCells) {
                if (cell.dataset.status === 'booked' || cell.dataset.status === 'pending') {
                    showNotification('Không thể khóa ô đã có người đặt!');
                    return;
                }
                if (cell.dataset.status === 'lock' || cell.dataset.status === 'lock') {
                    showNotification('Ô này đã bị khóa rồi!');
                    return;
                }
            }

            // 3. Gom dữ liệu để gửi
            const slots = [];
            const date = "<?php echo $selected_date; ?>"; // Lấy ngày hiện tại từ PHP

            selectedCells.forEach(cell => {
                slots.push({
                    san_id: cell.dataset.san,
                    time: cell.dataset.time
                });
            });

            if (!confirm(`Bạn muốn khóa ${slots.length} khung giờ đã chọn?`)) return;

            // 4. Gửi Ajax
            const formData = new FormData();
            formData.append('action', 'lock_slots');
            formData.append('date', date);
            formData.append('slots', JSON.stringify(slots));

            fetch('ql_khoa_san.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload(); // Reload để cập nhật giao diện
                    } else {
                        showNotification(data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Có lỗi xảy ra khi kết nối server');
                });
        }

        function handleUnlockSlots() {
            // 1. Lấy các ô đang chọn
            const selectedCells = document.querySelectorAll('.ocell.selected');

            if (selectedCells.length === 0) {
                showNotification('Vui lòng chọn ô cần mở khóa!');
                return;
            }

            // 2. Kiểm tra hợp lệ: Chỉ được chọn các ô ĐANG BỊ KHÓA
            // (Dựa vào class hoặc data-status mình đã gán ở PHP)
            for (let cell of selectedCells) {
                if (!cell.classList.contains('locked-hour')) {
                    showNotification('Bạn đang chọn ô chưa bị khóa hoặc ô đã đặt. Chỉ chọn ô có ổ khóa để mở!');
                    return;
                }
            }

            // 3. Gom dữ liệu
            const slots = [];
            const date = "<?php echo $selected_date; ?>";

            selectedCells.forEach(cell => {
                slots.push({
                    san_id: cell.dataset.san,
                    time: cell.dataset.time
                });
            });

            if (!confirm(`Bạn có chắc muốn mở khóa ${slots.length} khung giờ này?`)) return;

            // 4. Gửi Ajax
            const formData = new FormData();
            formData.append('action', 'unlock_slots');
            formData.append('date', date);
            formData.append('slots', JSON.stringify(slots));

            fetch('ql_khoa_san.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // alert(`Đã mở khóa ${data.count} ô thành công!`);
                        location.reload();
                    } else {
                        showNotification(data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Lỗi kết nối!');
                });
        }
    </script>
</body>

</html>
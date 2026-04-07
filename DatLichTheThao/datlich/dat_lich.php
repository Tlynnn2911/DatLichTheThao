<?php
require_once '../config/db.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// lay tham so
$co_so_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$sql_coso = "SELECT ten_co_so, gio_mo_cua, gio_dong_cua, gio_bat_dau_toi, gia_sang, gia_toi, gia_cuoi_tuan 
             FROM co_so WHERE id = $co_so_id";
$res_coso = $conn->query($sql_coso);
$coso = $res_coso->fetch_assoc();

// xac dinh ngay cuoi tuan thu 7 = 6 cn = 7
$day_of_week = date('N', strtotime($selected_date));
$is_weekend = ($day_of_week >= 6);

// gio mo cua
$open_str = $coso ? $coso['gio_mo_cua'] : '05:00:00';
$close_str = $coso ? $coso['gio_dong_cua'] : '23:00:00';
$evening_start_str = ($coso && isset($coso['gio_bat_dau_toi'])) ? $coso['gio_bat_dau_toi'] : '17:00:00';

$open_time = strtotime("$selected_date $open_str");
$close_time = strtotime("$selected_date $close_str");
$evening_start_time = strtotime("$selected_date $evening_start_str");

//mang time slot
$time_slots = [];
for ($t = $open_time; $t < $close_time; $t += 1800) {
    $time_slots[] = date('H:i', $t);
}

$ds_san = [];
$san_info = [];
$sql_san = "SELECT * FROM san WHERE co_so_id = $co_so_id AND trang_thai = 'mo'";
$res_san = $conn->query($sql_san);
while ($row = $res_san->fetch_assoc()) {
    $ds_san[] = $row;
    $san_info[$row['id']] = $row['ten_san'];
}

$sql_booking = "SELECT san_id, gio_bat_dau, gio_ket_thuc 
                FROM dat_lich 
                WHERE ngay_dat = '$selected_date' 
                AND co_so_id = $co_so_id 
                AND trang_thai_don != 'da_huy'";

$res_booking = $conn->query($sql_booking);
$booked_slots = [];
if ($res_booking) {
    while ($row = $res_booking->fetch_assoc()) {
        $booking_start = strtotime("$selected_date " . $row['gio_bat_dau']);
        $booking_end   = strtotime("$selected_date " . $row['gio_ket_thuc']);

        foreach ($time_slots as $slot) {
            $current_slot_timestamp = strtotime("$selected_date $slot");
            if ($current_slot_timestamp >= $booking_start && $current_slot_timestamp < $booking_end) {
                $booked_slots[$row['san_id']][$slot] = true;
            }
        }
    }
}

$sql_locks = "SELECT san_id, gio_bat_dau, gio_ket_thuc 
              FROM khoa_san_theo_gio 
              WHERE ngay_khoa = '$selected_date' 
              AND san_id IN (SELECT id FROM san WHERE co_so_id = $co_so_id)";

$res_locks = $conn->query($sql_locks);
$locked_slots = []; // mang slot bi khoa

if ($res_locks) {
    while ($row = $res_locks->fetch_assoc()) {
        $lock_start = strtotime("$selected_date " . $row['gio_bat_dau']);
        $lock_end   = strtotime("$selected_date " . $row['gio_ket_thuc']);

        foreach ($time_slots as $slot) {
            $current_slot_timestamp = strtotime("$selected_date $slot");
            // la true neu slot bi khoa
            if ($current_slot_timestamp >= $lock_start && $current_slot_timestamp < $lock_end) {
                $locked_slots[$row['san_id']][$slot] = true;
            }
        }
    }
}

$current_timestamp = time();
$is_today = ($selected_date == date('Y-m-d'));
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
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

    <div class="header">
        <a href="../index.php" class="back-btn" style="color:white; font-size:20px;"><i class="fas fa-arrow-left"></i></a>
        <h2 style="flex:1; text-align:center; margin:0; font-size:18px;">Đặt Lịch</h2>
    </div>

    <div class="toolbar">
        <div class="legend">
            <div class="legend-item">
                <div class="box box-white"></div> Trống
            </div>
            <div class="legend-item">
                <div class="box box-red"></div> Đã Đặt
            </div>
            <div class="legend-item">
                <div class="box box-gray"></div> Khóa
            </div>
            <div class="legend-item">
                <div class="box" style="background:#00b894"></div> Đang chọn
            </div>
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
                        <th><span class="time-label"><?php echo $time; ?></span></th>
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
                        <?php
                        foreach ($time_slots as $time):
                            $status_class = 'available';
                            $onclick = '';
                            $slot_timestamp = strtotime("$selected_date $time");
                            // neu time da troi qua hoac bi khoa
                            if (
                                strtotime($selected_date) < strtotime(date('Y-m-d')) ||
                                ($is_today && $slot_timestamp < $current_timestamp) ||
                                isset($locked_slots[$san['id']][$time])
                            ) {

                                $status_class = 'past';
                            }
                            // kiem tra da dat
                            elseif (isset($booked_slots[$san['id']][$time])) {
                                $status_class = 'booked';
                            }
                            // con trong
                            else {
                                $base_price = 0;
                                if ($is_weekend) {
                                    $base_price = $coso['gia_cuoi_tuan'];
                                } else {
                                    if ($slot_timestamp >= $evening_start_time) {
                                        $base_price = $coso['gia_toi'];
                                    } else {
                                        $base_price = $coso['gia_sang'];
                                    }
                                }

                                $slot_price = ($base_price * $san['he_so_gia']) / 2;
                                $onclick = "onclick=\"selectCell(this, '{$san['ten_san']}', $slot_price)\"";
                            }
                        ?>
                            <td class="cell <?php echo $status_class; ?>"
                                data-san="<?php echo $san['id']; ?>"
                                data-time="<?php echo $time; ?>"
                                <?php echo $onclick; ?>>
                            </td>
                        <?php endforeach; ?>
                        <td style="border: none; padding: 0; width: 0;"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="booking-summary-bar" id="bookingSummary">
        <div class="bar-row-top" id="summaryTitle">
            Sân ...
        </div>
        <div class="bar-row-middle">
            <span id="summaryTime">Tổng giờ: 0h00</span>
            <span class="price-text" id="summaryPrice">Tổng tiền: 0 VNĐ</span>
        </div>
        <div class="bar-row-bottom">
            <button class="btn-confirm-booking" onclick="submitBooking()">TIẾP THEO</button>
        </div>
    </div>

    <script>
        // mang cac slot da chon
        // { sanId, time, price, sanName, element }
        let selectedSlots = [];

        function timeToMinutes(timeStr) {
            const [h, m] = timeStr.split(':').map(Number);
            return h * 60 + m;
        }

        function selectCell(element, sanName, price) {
            const sanId = element.dataset.san;
            const time = element.dataset.time;

            // LOGIC CHỌN 1 SÂN DUY NHẤT
            // Nếu đã chọn ô của sân khác, reset hết chọn mới
            if (selectedSlots.length > 0 && selectedSlots[0].sanId !== sanId) {
                // Xóa class selected ở các ô cũ
                selectedSlots.forEach(slot => slot.element.classList.remove('selected'));
                selectedSlots = []; // Reset mảng
            }

            // TOGGLE CHỌN/BỎ CHỌN
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                // Xóa khỏi mảng
                selectedSlots = selectedSlots.filter(item => item.time !== time);
            } else {
                element.classList.add('selected');
                // Thêm vào mảng
                selectedSlots.push({
                    sanId,
                    time,
                    price,
                    sanName,
                    element
                });
            }

            updateSummaryBar();
        }

        function updateSummaryBar() {
            const bar = document.getElementById('bookingSummary');

            if (selectedSlots.length === 0) {
                bar.classList.remove('active');
                return;
            }

            bar.classList.add('active');

            // TÍNH TOÁN
            // Tên sân
            const currentSanName = selectedSlots[0].sanName;

            // 2. Tổng tiền
            let totalPrice = 0;
            selectedSlots.forEach(slot => totalPrice += slot.price);

            // Tổng giờ (Số slot * 30 phút)
            const totalHoursDec = selectedSlots.length / 2;
            const h = Math.floor(totalHoursDec);
            const m = (totalHoursDec - h) * 60;
            const timeStr = h + "h" + (m > 0 ? (m < 10 ? "0" + m : m) : "00");

            // Khoảng thời gian
            // Sort tăng dần theo thời gian
            selectedSlots.sort((a, b) => a.time.localeCompare(b.time));

            const startStr = selectedSlots[0].time;
            const lastStr = selectedSlots[selectedSlots.length - 1].time;

            // Tính giờ kết thúc của slot cuối cùng (+30p)
            let [lh, lm] = lastStr.split(':').map(Number);
            let d = new Date(2000, 0, 1, lh, lm);
            d.setMinutes(d.getMinutes() + 30);
            let endStr = ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);

            // CẬP NHẬT GIAO DIỆN
            document.getElementById('summaryTitle').innerText = `${currentSanName}: ${startStr} - ${endStr}`;
            document.getElementById('summaryTime').innerText = `Tổng giờ: ${timeStr}`;

            const priceFormatted = new Intl.NumberFormat('vi-VN').format(totalPrice);
            document.getElementById('summaryPrice').innerText = `Tổng tiền: ${priceFormatted} VNĐ`;
        }

        function submitBooking() {
            if (selectedSlots.length === 0) {
                showNotification("Vui lòng chọn ít nhất một khung giờ!");
                return;
            }

            selectedSlots.sort((a, b) => timeToMinutes(a.time) - timeToMinutes(b.time));

            // 2. KIỂM TRA TÍNH LIÊN TỤC (Logic mới thêm vào)
            // Duyệt qua danh sách, nếu slot sau không cách slot trước đúng 30 phút -> có khoảng trống/bị chặn
            for (let i = 0; i < selectedSlots.length - 1; i++) {
                const currentTime = timeToMinutes(selectedSlots[i].time);
                const nextTime = timeToMinutes(selectedSlots[i + 1].time);

                // 30 phút = khoảng cách tiêu chuẩn giữa các slot
                if (nextTime - currentTime !== 30) {
                    showNotification("Bạn không thể đặt cách quãng! Vui lòng chọn các khung giờ liên tiếp nhau hoặc liên hệ hotline để được giúp đỡ.");
                    return; // Dừng lại, không gửi form
                }
            }

            // Gom dữ liệu cần thiết
            const sanId = selectedSlots[0].sanId;
            const date = document.querySelector('input[name="date"]').value;

            // Lấy danh sách các khung giờ đã chọn
            // Sắp xếp giờ tăng dần
            selectedSlots.sort((a, b) => a.time.localeCompare(b.time));
            const times = selectedSlots.map(s => s.time).join(',');

            //Tạo form ẩn để POST dữ liệu sang trang xác nhận
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'thongtin_dat.php'; // Chuyển sang trang xác nhận mới

            // Các input ẩn
            const inputSanId = document.createElement('input');
            inputSanId.type = 'hidden';
            inputSanId.name = 'san_id';
            inputSanId.value = sanId;
            form.appendChild(inputSanId);

            const inputDate = document.createElement('input');
            inputDate.type = 'hidden';
            inputDate.name = 'date';
            inputDate.value = date;
            form.appendChild(inputDate);

            const inputTimes = document.createElement('input');
            inputTimes.type = 'hidden';
            inputTimes.name = 'slots';
            inputTimes.value = times;
            form.appendChild(inputTimes);

            //Submit form
            document.body.appendChild(form);
            form.submit();
        }

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
    </script>
</body>

</html>
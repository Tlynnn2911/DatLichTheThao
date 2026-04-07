
<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Bạn cần đăng nhập để thực hiện thao tác này.';
    echo json_encode(['status' => 'error', 'redirect' => true]);
    exit;
}

// Lấy dữ liệu
$u_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$cs_id = isset($_POST['co_so_id']) ? intval($_POST['co_so_id']) : 0;

if ($cs_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: Không xác định được sân.']);
    exit;
}

//đánh giá
if ($action == 'delete') {
    $stmt = $conn->prepare("DELETE FROM danh_gia WHERE nguoi_dung_id = ? AND co_so_id = ?");
    $stmt->bind_param("ii", $u_id, $cs_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Đã xóa đánh giá của bạn thành công!';
        echo json_encode(['status' => 'success']);
    } else {
        $_SESSION['error'] = 'Lỗi khi xóa: ' . $conn->error;
        echo json_encode(['status' => 'error']);
    }
    $stmt->close();
    exit;
}

// thêm hoặc sửa
$stars = isset($_POST['stars']) ? intval($_POST['stars']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($stars < 1 || $stars > 5 || empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn số sao và nhập nội dung.']);
    exit;
}

if ($action == 'add') {
    // Kiểm tra xem đã đánh giá chưa 
    $check = $conn->query("SELECT id FROM danh_gia WHERE nguoi_dung_id = $u_id AND co_so_id = $cs_id");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'Bạn đã đánh giá sân này rồi. Vui lòng chọn sửa đánh giá cũ.';
        echo json_encode(['status' => 'error', 'redirect' => true]);
        exit;
    }

    // Thêm mới
    $stmt = $conn->prepare("INSERT INTO danh_gia (nguoi_dung_id, co_so_id, so_sao, noi_dung) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $u_id, $cs_id, $stars, $content);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Gửi đánh giá thành công!';
        echo json_encode(['status' => 'success']);
    } else {
        $_SESSION['error'] = 'Lỗi hệ thống: ' . $stmt->error;
        echo json_encode(['status' => 'error']);
    }
} elseif ($action == 'update') {
    // Cập nhật
    $stmt = $conn->prepare("UPDATE danh_gia SET so_sao = ?, noi_dung = ?, ngay_tao = NOW() WHERE nguoi_dung_id = ? AND co_so_id = ?");
    $stmt->bind_param("isii", $stars, $content, $u_id, $cs_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Cập nhật đánh giá thành công!';
        echo json_encode(['status' => 'success']);
    } else {
        $_SESSION['error'] = 'Lỗi cập nhật: ' . $stmt->error;
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
}

$conn->close();
?>
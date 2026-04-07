<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');


// KIỂM TRA ĐĂNG NHẬP + QUYỀN

if (!isset($_SESSION['user_id_chu']) || $_SESSION['user_role_chu'] !== 'chu_san') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Không có quyền truy cập'
    ]);
    exit;
}

$user_id_chu = intval($_SESSION['user_id_chu']);
$action = $_POST['action'] ?? '';


// THÊM SÂN

if ($action === 'add') {

    $co_so_id = intval($_POST['co_so_id'] ?? 0);
    $ten_san  = trim($_POST['ten_san'] ?? '');

    if ($co_so_id <= 0 || $ten_san === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Dữ liệu không hợp lệ'
        ]);
        exit;
    }

    // Kiểm tra cơ sở có thuộc chủ sân không
    $check = $conn->query("
        SELECT id FROM co_so 
        WHERE id = $co_so_id AND chu_san_id = $user_id_chu
    ");

    if ($check->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bạn không có quyền thêm sân vào cơ sở này'
        ]);
        exit;
    }


    // Kiểm tra trùng tên sân trong cùng cơ sở
    $ten_san_esc = $conn->real_escape_string($ten_san);

    $checkName = $conn->query("
        SELECT id FROM san 
        WHERE co_so_id = $co_so_id 
        AND ten_san = '$ten_san_esc'
    ");

    if ($checkName->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tên sân đã tồn tại trong cơ sở này'
        ]);
        exit;
    }
        // Thêm sân
    $sql = "
        INSERT INTO san (co_so_id, ten_san, trang_thai, he_so_gia)
        VALUES ($co_so_id, '$ten_san', 'mo', 1)
    ";

    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Thêm sân thành công';
        echo json_encode([
            'status' => 'success',
            'message' => 'Thêm sân thành công'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Lỗi khi thêm sân'
        ]);
    }

    exit;
}


// SỬA SÂN

    if ($action === 'edit') {

    $san_id    = intval($_POST['san_id'] ?? 0);
    $ten_san   = trim($_POST['ten_san'] ?? '');
    $he_so_gia = floatval($_POST['he_so_gia'] ?? 1);
    $trang_thai = $_POST['trang_thai'] ?? 'mo';

    if ($san_id <= 0 || $ten_san === '' || !in_array($trang_thai, ['mo', 'khoa'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Dữ liệu sửa không hợp lệ'
        ]);
        exit;
    }

    // Kiểm tra sân có thuộc chủ sân không
    $check = $conn->query("
        SELECT s.id FROM san s
        JOIN co_so c ON s.co_so_id = c.id
        WHERE s.id = $san_id AND c.chu_san_id = $user_id_chu
    ");

    if ($check->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bạn không có quyền sửa sân này'
        ]);
        exit;
    }


    $ten_san_esc = $conn->real_escape_string($ten_san);

    // Kiểm tra trùng tên
    $checkName = $conn->query("
        SELECT s.id FROM san s
        WHERE s.ten_san = '$ten_san_esc'
        AND s.id <> $san_id
        AND s.co_so_id = (
            SELECT co_so_id FROM san WHERE id = $san_id
        )
    ");

    if ($checkName->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tên sân đã tồn tại'
        ]);
        exit;
    }
    // Cập nhật sân
    $sql = "
        UPDATE san 
        SET ten_san = '$ten_san',
            he_so_gia = $he_so_gia,
            trang_thai = '$trang_thai'
        WHERE id = $san_id
    ";

    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Thêm sân thành công';
        echo json_encode([
            'status' => 'success',
            'message' => 'Cập nhật sân thành công'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Lỗi khi cập nhật sân'
        ]);
    }

    exit;
}


// XÓA SÂN

if ($action === 'delete') {

    $san_id = intval($_POST['san_id'] ?? 0);

    if ($san_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID sân không hợp lệ'
        ]);
        exit;
    }

    // Kiểm tra quyền
    $check = $conn->query("
        SELECT s.id FROM san s
        JOIN co_so c ON s.co_so_id = c.id
        WHERE s.id = $san_id AND c.chu_san_id = $user_id_chu
    ");

    if ($check->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bạn không có quyền xóa sân này'
        ]);
        exit;
    }

    // Xóa sân
    if ($conn->query("DELETE FROM san WHERE id = $san_id")) {
        $_SESSION['success'] = 'Xóa sân thành công';
        echo json_encode([
            'status' => 'success',
            'message' => 'Xóa sân thành công'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Không thể xóa sân'
        ]);
    }

    exit;
}


//ACTION KHÔNG HỢP LỆ

echo json_encode([
    'status' => 'error',
    'message' => 'Hành động không hợp lệ'
]);
exit;

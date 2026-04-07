<?php
session_start();
require_once 'config/db.php';

// header trả về json
header('Content-Type: application/json');

// Kiểm tra tham số ID đầu vào
if (isset($_GET['id'])) {
    $co_so_id = intval($_GET['id']);

    // Lấy ID người dùng đang đăng nhập
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

    $response = [
        'images' => [],      // danh sách ảnh album
        'reviews' => [],     // danh sách tất cả đánh giá
        'my_review' => null  // đánh giá riêng của user hiện tại
    ];
    
    $sql_img = "SELECT url_hinh FROM hinh_anh_san 
                WHERE co_so_id = $co_so_id 
                AND avata = 0 
                AND la_anh_dai_dien = 0";

    $res_img = $conn->query($sql_img);
    if ($res_img) {
        while ($row = $res_img->fetch_assoc()) {
            $response['images'][] = $row['url_hinh'];
        }
    }

    $sql_review = "SELECT d.id, d.so_sao, d.noi_dung, d.ngay_tao, d.nguoi_dung_id, u.ho_ten, d.phan_hoi_chu_san
                   FROM danh_gia d 
                   JOIN nguoi_dung u ON d.nguoi_dung_id = u.id 
                   WHERE d.co_so_id = $co_so_id 
                   ORDER BY d.ngay_tao DESC";

    $res_review = $conn->query($sql_review);

    if ($res_review) {
        while ($row = $res_review->fetch_assoc()) {
            $row['ngay_tao_fmt'] = date('d/m/Y', strtotime($row['ngay_tao']));
            $row['avatar_url'] = "https://ui-avatars.com/api/?name=" . urlencode($row['ho_ten']) . "&background=random&color=fff&size=64";
            if ($user_id > 0 && $row['nguoi_dung_id'] == $user_id) {
                $response['my_review'] = $row;
            }
            $response['reviews'][] = $row;
        }
    }

    // Trả về kết quả dạng JSON
    echo json_encode($response);
    $conn->close();
} else {
    echo json_encode(['error' => 'Thiếu ID cơ sở']);
}

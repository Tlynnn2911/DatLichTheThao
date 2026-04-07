<?php
session_start();
require_once '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id_chu']) || $_SESSION['user_role_chu'] !== 'chu_san') {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}

$user_id = $_SESSION['user_id_chu'];

// Lấy danh sách cơ sở của chủ sân này
$sql = "SELECT * FROM co_so WHERE chu_san_id = $user_id";
$result = $conn->query($sql);
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>BOOKSAN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bgroud">
    <div class="select-facility-container">
        <div class="welcome-text">
            <h2>Xin chào, <?php echo $_SESSION['user_name_chu']; ?>!</h2>
            <p>Vui lòng chọn cơ sở bạn muốn quản lý</p>
        </div>

        <div class="facility-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    // Lấy ảnh đại diện
                    $id = $row['id'];
                    $sql_img = "SELECT url_hinh FROM hinh_anh_san WHERE co_so_id = $id ORDER BY avata DESC LIMIT 1";
                    $res_img = $conn->query($sql_img);
                    $img = ($res_img && $r = $res_img->fetch_assoc()) ? '../' . $r['url_hinh'] : 'https://via.placeholder.com/300x150?text=No+Image';
                ?>
                    <a href="trangchu.php?id=<?php echo $row['id']; ?>" class="facility-card">
                        <img src="<?php echo $img; ?>" class="f-img">
                        <div class="f-body">
                            <div class="f-title"><?php echo $row['ten_co_so']; ?></div>
                            <div class="f-address">
                                <i class="fas fa-map-marker-alt" style="margin-top:3px;"></i>
                                <span><?php echo $row['dia_chi_cu_the'] . ', ' . $row['quan_huyen']; ?></span>
                            </div>
                            <div class="btn-select">Quản lý cơ sở này <i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; width:100%;">
                    <p>Bạn chưa có cơ sở nào.</p>
                </div>
            <?php endif; ?>
        </div>

        <a href="../taikhoan/dangxuatchu.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </div>
</body>

</html>
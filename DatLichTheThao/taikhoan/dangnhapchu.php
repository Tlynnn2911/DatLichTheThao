<?php
session_start();
require_once '../config/db.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account = $conn->real_escape_string($_POST['account']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM nguoi_dung 
            WHERE (sdt = '$account' OR email = '$account')
              AND vai_tro IN ('admin', 'chu_san') 
              AND trang_thai = 1
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $check_pass = password_verify($password, $row['mat_khau']) || ($password === $row['mat_khau']);

        if ($check_pass) {
            // Đăng nhập thành công, lưu session
            $_SESSION['user_id_chu'] = $row['id'];
            $_SESSION['user_name_chu'] = $row['ho_ten'];
            $_SESSION['user_role_chu'] = $row['vai_tro'];
            $_SESSION['user_email_chu'] = $row['email'];
            $_SESSION['user_phone_chu'] = $row['sdt'];

            if ($row['vai_tro'] === 'admin') {
                // Thoát ra khỏi auth/ rồi vào admin/admin.php
                header("Location: ../admin/admin.php");
                exit();
            }
            //KIỂM TRA SỐ LƯỢNG CƠ SỞ
            $chu_san_id = $row['id'];
            $sql_check_coso = "SELECT id FROM co_so WHERE chu_san_id = $chu_san_id";
            $res_check = $conn->query($sql_check_coso);
            $count_coso = $res_check->num_rows;

            if ($count_coso > 1) {
                // Nếu có nhiều hơn 1 cơ sở thì chuyển đến trang chọn
                header("Location: ../chusan/choncoso.php");
            } elseif ($count_coso == 1) {
                // Nếu chỉ có 1 cơ sở thì vào thẳng trang chủ với ID đó
                $row_coso = $res_check->fetch_assoc();
                header("Location: ../chusan/trangchu.php?id=" . $row_coso['id']);
            } else {
                // Chưa có cơ sở nào, thông báo lỗi
                $error_msg = "Chưa có cơ sở nào";
            }
            exit();
        } else {
            $error_msg = "Mật khẩu không chính xác!";
        }
    } else {
        $error_msg = "Tài khoản không tồn tại hoặc bạn không có quyền Quản lý!";
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Đăng Nhập - Chủ Sân</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bgroud">
    <div class="register-login-card">
        <div class="card-header">
            <h2>Đăng nhập - Chủ sân</h2>
            <p>BOOKSAN - Hệ thống quản lý sân thể thao</p>
        </div>
        <?php if ($error_msg): ?>
            <div class="alert"><i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Số điện thoại hoặc email</label>
                <div class="input-wrapper">
                    <input type="text" name="account" id="acc_input" class="form-control" placeholder="Nhập số điện thoại hoặc email" required>
                    <i class="fas fa-times-circle input-icon-right" onclick="clearInput('acc_input')"></i>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mật khẩu</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="pass_input" class="form-control" placeholder="Nhập mật khẩu" required autocomplete="off">
                    <i class="fas fa-eye-slash input-icon-right" onclick="togglePass()"></i>
                </div>
            </div>
            <div class="switch-role">
                <p align="center">Nếu bạn là KHÁCH CHƠI,</p><br>
                <p align="center">hãy bấm vào đây để <a target="_blank" href="../index.php"> Tìm kiếm và đặt lịch</a></p>
            </div>
            <button type="submit" class="btn-submit">Đăng nhập</button>
        </form>
    </div>
    <script>
        function togglePass() {
            const input = document.getElementById('pass_input');
            const icon = event.target;
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            }
        }

        function clearInput(id) {
            document.getElementById(id).value = '';
            document.getElementById(id).focus();
        }
    </script>
</body>

</html>
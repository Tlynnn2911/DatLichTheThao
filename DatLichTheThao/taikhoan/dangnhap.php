<?php
session_start();
require_once '../config/db.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input_value = "";

    // Kiểm tra đầu vào là Email hay SĐT
    if (!empty($_POST['email'])) {
        $input_value = $conn->real_escape_string($_POST['email']);
        $sql = "SELECT * FROM nguoi_dung WHERE email = '$input_value'";
    } elseif (!empty($_POST['phone'])) {
        $input_value = $conn->real_escape_string($_POST['phone']);
        $sql = "SELECT * FROM nguoi_dung WHERE sdt = '$input_value'";
    }

    $password = $_POST['password'];

    if ($input_value && $password) {
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Kiểm tra mật khẩu
            $check_pass = false;
            if (password_verify($password, $row['mat_khau'])) {
                $check_pass = true;
            } elseif ($password === $row['mat_khau']) {
                $check_pass = true;
            }

            if ($check_pass) {
                //KIỂM TRA QUYỀN KHÁCH HÀNG
                if ($row['vai_tro'] !== 'khach_hang') {
                    $error_msg = "Tài khoản này dành cho Quản lý. Vui lòng đăng nhập tại trang Chủ sân!";
                }
                //Kiểm tra trạng thái khóa
                elseif ($row['trang_thai'] == 0) {
                    $error_msg = "Tài khoản của bạn đã bị khóa!";
                } else {
                    // Đăng nhập thành công
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_name'] = $row['ho_ten'];
                    $_SESSION['user_role'] = $row['vai_tro'];
                    $_SESSION['user_email'] = $row['email'];
                    $_SESSION['user_phone'] = $row['sdt'];

                    header("Location: ../index.php");
                    exit();
                }
            } else {
                $error_msg = "Mật khẩu không chính xác!";
            }
        } else {
            $error_msg = "Tài khoản không tồn tại!";
        }
    } else {
        $error_msg = "Vui lòng nhập đầy đủ thông tin!";
    }
}
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
    <div class="page-header">
        <a href="../index.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="page-title">Đăng Nhập</div>
    </div>

    <div class="register-login-card">

        <div class="tabs">
            <div class="tab-item active" onclick="switchTab('phone')">Số Điện Thoại</div>
            <div class="tab-item" onclick="switchTab('email')">Email</div>
        </div>

        <div class="form-body">

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" action="">

                <div id="tab-phone" class="tab-content active">
                    <div class="form-group">
                        <label class="form-label">Số điện thoại của bạn?</label>
                        <div class="input-wrapper">
                            <input type="number" name="phone" id="phone_input" class="form-control" placeholder="Nhập số điện thoại">
                            <i class="fas fa-times-circle input-icon-right" onclick="clearInput('phone_input')"></i>
                        </div>
                    </div>
                </div>

                <div id="tab-email" class="tab-content">
                    <div class="form-group">
                        <label class="form-label">Email của bạn?</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" id="email_input" class="form-control" placeholder="Nhập email của bạn">
                            <i class="fas fa-times-circle input-icon-right" onclick="clearInput('email_input')"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mật khẩu (*)</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="pass_input" class="form-control" placeholder="Nhập mật khẩu" required>
                        <i class="fas fa-eye-slash input-icon-right" onclick="togglePass()"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">ĐĂNG NHẬP</button>

                <div class="forgot-pass">
                    <a href="quenmatkhau.php">Quên mật khẩu?</a>
                </div>

            </form>
        </div>
    </div>

    <div class="register-link">
        Bạn chưa có tài khoản? <a href="dangky.php">Đăng ký</a>
    </div>

    <div class="owner-banner" onclick="window.location.href='dangnhapchu.php'">
        Nếu bạn là <b>CHỦ SÂN</b><br>Bấm vào đây để đăng nhập!
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            if (type === 'phone') {
                document.querySelectorAll('.tab-item')[0].classList.add('active');
                document.getElementById('tab-phone').classList.add('active');
                document.getElementById('email_input').value = '';
            } else {
                document.querySelectorAll('.tab-item')[1].classList.add('active');
                document.getElementById('tab-email').classList.add('active');
                document.getElementById('phone_input').value = '';
            }
        }

        function togglePass() {
            const input = document.getElementById('pass_input');
            const icon = event.target;
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

        function clearInput(id) {
            document.getElementById(id).value = '';
            document.getElementById(id).focus();
        }
    </script>
</body>

</html>
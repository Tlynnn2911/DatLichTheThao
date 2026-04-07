<?php
require_once '../config/db.php';

$error_msg = "";
$success_msg = "";

// Xử lý khi bấm nút Đăng Ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu và làm sạch
    $sdt = $conn->real_escape_string(trim($_POST['sdt']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $ho_ten = $conn->real_escape_string(trim($_POST['ho_ten']));
    $pass = $_POST['password'];
    $re_pass = $_POST['re_password'];

    // Validate cơ bản
    if (empty($sdt)) {
        $error_msg = "Số điện thoại không được để trống!";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $sdt)) {
        $error_msg = "Số điện thoại phải có 10-11 chữ số!";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Email không hợp lệ!";
    } elseif ($pass != $re_pass) {
        $error_msg = "Mật khẩu nhập lại không khớp!";
    } elseif (strlen($pass) < 6) {
        $error_msg = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        // Kiểm tra SĐT đã tồn tại chưa
        $check_sql = "SELECT id FROM nguoi_dung WHERE sdt='$sdt'";

        // Nếu có nhập email thì kiểm tra thêm email
        if (!empty($email)) {
            $check_sql .= " OR email='$email'";
        }

        $check_result = $conn->query($check_sql);

        if ($check_result === false) {
            die("Lỗi SQL CHECK: " . $conn->error);
        }

        if ($check_result->num_rows > 0) {
            $error_msg = "Số điện thoại hoặc Email này đã được đăng ký!";
        } else {
            // Mã hóa mật khẩu (Bắt buộc để bảo mật)
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

            // Thêm vào database
            if (!empty($email)) {
                // Có email
                $sql = "INSERT INTO nguoi_dung (ho_ten, sdt, mat_khau, email, vai_tro, trang_thai) 
                        VALUES ('$ho_ten', '$sdt', '$hashed_pass', '$email','khach_hang', 1)";
            } else {
                // Không có email - set NULL
                $sql = "INSERT INTO nguoi_dung (ho_ten, sdt, mat_khau, email, vai_tro, trang_thai) 
                        VALUES ('$ho_ten', '$sdt', '$hashed_pass', NULL, 'khach_hang', 1)";
            }
            echo "<script>console.log('SQL: " . addslashes($sql) . "');</script>";

            if ($conn->query($sql) === TRUE) {
                $new_id = $conn->insert_id; // Lấy ID vừa insert
                $success_msg = "Đăng ký thành công!";
                // Tự động chuyển trang sau 2 giây
                echo "<script>setTimeout(function(){ window.location.href = 'dangnhap.php'; }, 2000);</script>";
            } else {
                $error_msg = "Lỗi hệ thống: " . $conn->error;
            }
        }
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
        <div class="page-title">Đăng Ký</div>
    </div>
    <div class="register-login-card">
        <div id="notification-area">
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
        </div>


        <form method="POST" action="" id="registerForm">

            <div class="form-group">
                <label class="form-label">Số điện thoại (*)</label>
                <div class="input-wrapper">
                    <input type="tel" name="sdt" id="sdt_input" class="form-control"
                        placeholder="Nhập số điện thoại (10số)"
                        pattern="^0[0-9]{9}$" required>
                    <i class="fas fa-times-circle input-icon-right" onclick="clearInput('sdt_input')"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email của bạn (Không bắt buộc)</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email_input" class="form-control"
                        placeholder="Nhập email của bạn (Tùy chọn)">
                    <i class="fas fa-times-circle input-icon-right" onclick="clearInput('email_input')"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tên đầy đủ</label>
                <div class="input-wrapper">
                    <input type="text" name="ho_ten" id="hoten_input" class="form-control"
                        placeholder="Nhập họ và tên" required>
                    <i class="fas fa-times-circle input-icon-right" onclick="clearInput('hoten_input')"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu (*)</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="pass1" class="form-control"
                        placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                        minlength="6" required>
                    <i class="fas fa-eye-slash input-icon-right" onclick="togglePass('pass1', this)"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nhập lại mật khẩu (*)</label>
                <div class="input-wrapper">
                    <input type="password" name="re_password" id="pass2" class="form-control"
                        placeholder="Nhập lại mật khẩu"
                        minlength="6" required>
                    <i class="fas fa-eye-slash input-icon-right" onclick="togglePass('pass2', this)"></i>
                </div>
            </div>
    
            <button type="submit" class="btn-submit">Đăng ký</button>

            <div class="form-footer">
                Bạn đã có tài khoản? <a href="dangnhap.php">Đăng nhập</a>
            </div>
        </form>
    </div>
    <script>
        // Hàm toggle hiển thị/ẩn mật khẩu
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

        // Hàm xóa nội dung khi bấm dấu X
        function clearInput(inputId) {
            const input = document.getElementById(inputId);
            input.value = '';
            input.focus();
        }

        // Validate form trước khi submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const sdt = document.getElementById('sdt_input').value;
            const email = document.getElementById('email_input').value;
            const password = document.getElementById('pass1').value;
            const rePassword = document.getElementById('pass2').value;

            // Kiểm tra số điện thoại
            if (!/^0[0-9]{9}$/.test(sdt)) {
                e.preventDefault();
                showNotification('Số điện thoại phải có 10 chữ số!');
                return false;
            }

            // Kiểm tra email nếu có nhập
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                showNotification('Email không hợp lệ!');
                return false;
            }

            // Kiểm tra mật khẩu
            if (password !== rePassword) {
                e.preventDefault();
                showNotification('Mật khẩu nhập lại không khớp!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                showNotification('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
        });
    </script>
</body>

</html>
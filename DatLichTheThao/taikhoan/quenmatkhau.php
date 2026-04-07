<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOOKSAN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bgroud">
    <div class="page-header">
        <a href="../dangnhap.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
    </div>
    <div class="register-login-card">
        <div class="card-header">
            <h2 align="center">Quên Mật Khẩu</h2>
            <br>
        </div>

        <form action="xuly_quenmk.php" method="POST">

            <div class="form-group">
                <label class="form-label">Số Điện Thoại hoặc Email (*)</label>
                <div class="input-wrapper">
                    <input type="text" name="account" id="acc_input" class="form-control" placeholder="Nhập số điện thoại hoặc email" required>
                    <i class="fas fa-times-circle input-icon-right" onclick="clearInput('acc_input')"></i>
                </div>
            </div>

            <button type="submit" name="btn_reset" class="btn-submit">Gửi yêu cầu</button>
        </form>
        <div class="forgot-pass">
            <a href="dangnhap.php">Quay lại đăng nhập</a>
        </div>
    </div>
    <script>
        function clearInput(id) {
            document.getElementById(id).value = '';
            document.getElementById(id).focus();
        }
    </script>
</body>

</html>
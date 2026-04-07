<?php
session_start();
require_once 'config/db.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');
$days_map = ['Monday' => 'Thứ Hai', 'Tuesday' => 'Thứ Ba', 'Wednesday' => 'Thứ Tư', 'Thursday' => 'Thứ Năm', 'Friday' => 'Thứ Sáu', 'Saturday' => 'Thứ Bảy', 'Sunday' => 'Chủ Nhật'];
$display_date = $days_map[date('l')] . ', ' . date('d/m/Y');

$sql = "SELECT 
            c.id, 
            c.ten_co_so, 
            c.dia_chi_cu_the,
            c.quan_huyen,
            c.tinh_thanh,
            c.gio_mo_cua,
            c.gio_dong_cua,
            c.hotline,
            
            COALESCE((SELECT ROUND(AVG(d.so_sao), 1) FROM danh_gia d WHERE d.co_so_id = c.id), 5.0) as diem_danh_gia,
            
            (SELECT COUNT(*) FROM danh_gia d WHERE d.co_so_id = c.id) as luot_danh_gia,

            (SELECT m.ten_mon 
             FROM mon_the_thao m 
             WHERE m.id = c.mon_the_thao_id 
             LIMIT 1) as ten_mon_the_thao,
            
            (SELECT h.url_hinh 
             FROM hinh_anh_san h 
             WHERE h.co_so_id = c.id 
             ORDER BY h.la_anh_dai_dien DESC, h.id DESC 
             LIMIT 1) as hinh_anh_dai_dien,
             
             (SELECT h.url_hinh 
             FROM hinh_anh_san h 
             WHERE h.co_so_id = c.id 
             ORDER BY h.avata DESC, h.id DESC 
             LIMIT 1) as avata

        FROM co_so c";
$result = $conn->query($sql);
if (!$result) {
    die("Lỗi SQL: " . $conn->error);
}

//Lấy danh sách các quận huyện
$sql_khuvuc = "SELECT DISTINCT quan_huyen FROM co_so ORDER BY quan_huyen ASC";
$res_khuvuc = $conn->query($sql_khuvuc);

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$khu_vuc = isset($_GET['area']) ? $_GET['area'] : '';

$where_clause = "WHERE 1=1";

if (!empty($keyword)) {
    $safe_key = $conn->real_escape_string($keyword);
    $where_clause .= " AND c.ten_co_so LIKE '%$safe_key%'";
}

if (!empty($khu_vuc)) {
    $safe_area = $conn->real_escape_string($khu_vuc);
    $where_clause .= " AND c.quan_huyen = '$safe_area'";
}

$sql = "SELECT 
            c.id, 
            c.ten_co_so, 
            c.dia_chi_cu_the,
            c.quan_huyen,
            c.tinh_thanh,
            c.gio_mo_cua,
            c.gio_dong_cua,
            c.hotline,
            
            COALESCE((SELECT ROUND(AVG(d.so_sao), 1) FROM danh_gia d WHERE d.co_so_id = c.id), 5.0) as diem_danh_gia,
            (SELECT COUNT(*) FROM danh_gia d WHERE d.co_so_id = c.id) as luot_danh_gia,
            (SELECT m.ten_mon FROM mon_the_thao m WHERE m.id = c.mon_the_thao_id LIMIT 1) as ten_mon_the_thao,
            (SELECT h.url_hinh FROM hinh_anh_san h WHERE h.co_so_id = c.id ORDER BY h.la_anh_dai_dien DESC, h.id DESC LIMIT 1) as hinh_anh_dai_dien,
            (SELECT h.url_hinh FROM hinh_anh_san h WHERE h.co_so_id = c.id ORDER BY h.avata DESC, h.id DESC LIMIT 1) as avata

        FROM co_so c
        $where_clause
        ORDER BY c.id DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Lỗi SQL: " . $conn->error);
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>BOOKSAN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
            <img src="assets/img/logo.png" /> <span class="brand-name">BookSan</span>
        </div>
        <div class="date-display"><?php echo $display_date; ?></div>

        <div class="buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>

                <a href="taikhoan/dangnhap.php" class="btn btn-login">Đăng nhập</a>
                <a href="taikhoan/dangky.php" class="btn btn-register">Đăng kí</a>

            <?php else: ?>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="taikhoan.php" style="font-size:13px; color: white;">
                        Xin chào, <b><?php echo $_SESSION['user_name']; ?></b>
                    </a>
                    <i class="fas fa-user-circle" style="font-size: 20px; color: white;"></i>
                </div>

            <?php endif; ?>
        </div>
    </header>

    <div class="filter-bar">
        <form method="GET" action="" class="search-form" style="display: flex; gap: 10px; flex: 1; align-items: center;">

            <div class="search-box" style="flex: 2;">
                <i class="fas fa-search"></i>
                <input type="text" name="keyword" placeholder="Tìm tên sân..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>

            <div class="search-box" style="flex: 1; min-width: 150px;">
                <i class="fas fa-map-marker-alt"></i>
                <select name="area" onchange="this.form.submit()" style="border: none; outline: none; width: 90%; background: transparent; cursor: pointer;">
                    <option value="">-- Tất cả khu vực --</option>
                    <?php
                    if ($res_khuvuc) {
                        // Reset con trỏ dữ liệu về đầu nếu cần
                        $res_khuvuc->data_seek(0);
                        while ($row_kv = $res_khuvuc->fetch_assoc()) {
                            $selected = ($khu_vuc == $row_kv['quan_huyen']) ? 'selected' : '';
                            echo "<option value=\"{$row_kv['quan_huyen']}\" $selected>{$row_kv['quan_huyen']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <button type="submit" style="background: #27ae60; color: white; border: none; padding: 0 20px; border-radius: 20px; cursor: pointer;">Tìm</button>
        </form>

        <div class="filter-tabs">
            <div class="filter-item"><i class="fas fa-check-circle"></i> <a href="taikhoan.php?tab=history" style="text-decoration: none; color: inherit;">Sân đã đặt</a></div>
        </div>
    </div>

    <div class="container">
        <div class="venue-grid">
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Chuẩn bị dữ liệu JSON để truyền vào Modal
                    $venue_data = json_encode([
                        'id' => $row['id'],
                        'name' => $row['ten_co_so'],
                        'address' => $row['dia_chi_cu_the'] . ', ' . $row['quan_huyen'],
                        'time' => $row['gio_mo_cua'] . ' - ' . $row['gio_dong_cua'],
                        'rating' => $row['diem_danh_gia'],
                        'count' => $row['luot_danh_gia'],
                        'img' => $row['hinh_anh_dai_dien'] ? $row['hinh_anh_dai_dien'] : 'https://via.placeholder.com/350x160',
                        'phone' => $row['hotline'] ? $row['hotline'] : ' ',
                        'sport' => $row['ten_mon_the_thao']
                    ]);
            ?>

                    <div class="card" onclick='openVenueModal(<?php echo $venue_data; ?>)'>
                        <div class="card-image">
                            <img src="<?php echo $row['hinh_anh_dai_dien'] ? $row['hinh_anh_dai_dien'] : 'https://via.placeholder.com/350x160'; ?>" alt="Ảnh sân">
                            <div class="badges">
                                <div class="badge rating"><i class="fas fa-star"></i> <?php echo $row['diem_danh_gia']; ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="venue-name"><?php echo $row['ten_co_so']; ?></div>
                            <div class="venue-info">
                                <img src="<?php echo $row['avata'] ? $row['avata'] : 'https://via.placeholder.com/350x160'; ?>" class="venue-avatar">
                                <div class="venue-details">
                                    <div class="venue-address"><?php echo $row['dia_chi_cu_the']; ?>...</div>
                                    <div class="venue-time"><i class="far fa-clock"></i> <?php echo $row['gio_mo_cua'] . '-' . $row['gio_dong_cua']; ?></div>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <div style="font-size:11px; color:#2E8B57; font-weight:600;"><?php echo $row['ten_mon_the_thao']; ?></div>
                                <a href="datlich/dat_lich.php?id=<?php echo $row['id']; ?>" class="btn-booking" style="text-decoration: none; display: inline-block; text-align: center;">ĐẶT LỊCH</a>
                            </div>
                        </div>
                    </div>

            <?php }
            } else {
                echo "<p style='text-align:center'>Chưa có sân nào.</p>";
            } ?>
        </div>
    </div>

    <div class="modal-overlay" id="venueModal">
        <div class="modal-card">

            <div class="modal-header-img">
                <img id="m_img" src="" alt="Cover">
                <div class="btn-close-modal" onclick="closeModal()"><i class="fas fa-arrow-left"></i></div>
                <div class="top-actions">
                    <div class="action-circle"><i class="fas fa-share"></i></div>
                    <div class="action-circle"><i class="far fa-heart"></i></div>
                </div>
                <button class="btn-book-big" id="btnBookNow">Đặt lịch</button>
            </div>

            <div class="modal-body">
                <div class="m-rating"><i class="fas fa-star"></i> <span id="m_rating"></span> (<span id="m_count"></span> đánh giá)</div>
                <div class="m-title" id="m_name">Tên Sân</div>
                <div class="m-info-row"><i class="fas fa-map-marker-alt"></i> <span id="m_address">Địa chỉ</span></div>
                <div class="m-info-row"><i class="far fa-clock"></i> <span id="m_time">Giờ</span></div>
                <div class="m-info-row"><i class="fas fa-phone-alt"></i> Liên hệ: <span id="m_phone"></span></div>

                <div class="modal-tabs">
                    <div class="m-tab active" onclick="switchTab('tab-images', this)">Hình ảnh</div>
                    <div class="m-tab" onclick="switchTab('tab-reviews', this)">Đánh giá</div>
                </div>

                <div class="modal-content-scroll">

                    <div id="tab-images" class="tab-content-panel active">
                        <div class="modal-gallery-grid" id="gallery-container">
                        </div>

                        <p id="no-img-msg" style="display:none; text-align:center; color:#999; font-size:13px; margin-top:20px;">
                            Chưa có ảnh album mô tả thêm.
                        </p>
                    </div>

                    <div id="tab-reviews" class="tab-content-panel">

                        <?php if (isset($_SESSION['user_id'])): ?>

                            <div id="myReviewDisplay" class="my-review-box" style="display: none;">
                                <div class="my-review-header">
                                    <span class="my-review-label"><i class="fas fa-user-check"></i> Đánh giá của bạn</span>
                                    <div class="btn-action-group">
                                        <button class="btn-act btn-edit" onclick="enableEditMode()"><i class="fas fa-edit"></i> Sửa</button>
                                        <button class="btn-act btn-delete" onclick="deleteMyReview()"><i class="fas fa-trash"></i> Xóa</button>
                                    </div>
                                </div>
                                <div id="myReviewStars" style="color:#f1c40f; font-size:12px; margin-bottom:5px;"></div>
                                <div id="myReviewContent" style="font-size:13px; color:#333; line-height: 1.5;"></div>
                            </div>

                            <div id="reviewFormBox" class="write-review-box">
                                <div style="font-weight:bold; font-size:13px; margin-bottom:5px;" id="formTitle">Viết đánh giá mới:</div>

                                <div class="star-rating-input" id="starInputGroup">
                                    <i class="fas fa-star" onclick="setRating(1)" onmouseover="hoverRating(1)" onmouseout="resetRating()"></i>
                                    <i class="fas fa-star" onclick="setRating(2)" onmouseover="hoverRating(2)" onmouseout="resetRating()"></i>
                                    <i class="fas fa-star" onclick="setRating(3)" onmouseover="hoverRating(3)" onmouseout="resetRating()"></i>
                                    <i class="fas fa-star" onclick="setRating(4)" onmouseover="hoverRating(4)" onmouseout="resetRating()"></i>
                                    <i class="fas fa-star" onclick="setRating(5)" onmouseover="hoverRating(5)" onmouseout="resetRating()"></i>
                                </div>

                                <input type="hidden" id="ratingValue" value="5">
                                <input type="hidden" id="currentCoSoId" value="">
                                <input type="hidden" id="formAction" value="add"> <textarea id="reviewContent" class="review-textarea" rows="3" placeholder="Chia sẻ trải nghiệm của bạn về sân này..."></textarea>

                                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                                    <button id="btnCancelEdit" class="btn-act" style="background:#9ca3af; color:white; display:none;" onclick="cancelEditMode()">Hủy</button>
                                    <button class="btn-submit-review" onclick="submitReview()">Gửi đánh giá</button>
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="login-prompt">
                                Vui lòng <a href="taikhoan/dangnhap.php">Đăng nhập</a> để viết đánh giá.
                            </div>
                        <?php endif; ?>

                        <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">

                        <div id="reviews-container">
                        </div>

                        <p id="no-review-msg" style="display:none; text-align:center; color:#999; font-size:13px; margin-top:10px;">
                            Chưa có đánh giá nào.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="lightbox-overlay" id="lightboxModal" onclick="closeFullImage()">
        <span class="lightbox-close" onclick="closeFullImage()">&times;</span>
        <img class="lightbox-content" id="lightboxImg" onclick="event.stopPropagation()">
    </div>

    <div class="bottom-nav">
        <div class="nav-item active"><i class="fas fa-home"></i><span>Trang chủ</span></div>
        <a href="taikhoan.php" class="nav-item"><i class="fas fa-user"></i><span>Tài Khoản</span></a>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(el => el.style.display = 'none');
                }, 3000);
            }
        });

        // XỬ LÝ SAO 
        let selectedRating = 5;

        function hoverRating(n) {
            const stars = document.querySelectorAll('#starInputGroup i');
            stars.forEach((star, index) => {
                if (index < n) star.className = 'fas fa-star active';
                else star.className = 'fas fa-star';
            });
        }

        function setRating(n) {
            selectedRating = n;
            const ratingInput = document.getElementById('ratingValue');
            if (ratingInput) ratingInput.value = n;
            hoverRating(n);
        }

        function resetRating() {
            hoverRating(selectedRating);
        }

        // LOGIC MODAL & LẤY DỮ LIỆU
        function openVenueModal(data) {
            const modal = document.getElementById('venueModal');
            modal.style.display = 'flex';
            // Cập nhật ID sân ngay lập tức
            const idInput = document.getElementById('currentCoSoId');
            if (idInput) {
                idInput.value = data.id;
                console.log("Đã chuyển sang sân ID: " + data.id);
            } else {
                console.warn("Chưa đăng nhập hoặc không tìm thấy input ID");
            }
            // thông tin giao diện
            document.getElementById('m_img').src = data.img;
            document.getElementById('m_name').innerText = data.name;
            document.getElementById('m_address').innerText = data.address;
            document.getElementById('m_time').innerText = data.time;
            document.getElementById('m_phone').innerText = data.phone;
            document.getElementById('m_rating').innerText = data.rating;
            document.getElementById('m_count').innerText = data.count;

            // Cập nhật link đặt lịch
            const btnBook = document.getElementById('btnBookNow');
            if (btnBook) btnBook.onclick = function() {
                window.location.href = `datlich/dat_lich.php?id=${data.id}`;
            };
            resetModalUI();
            loadVenueData(data.id);
        }
        
        // Hàm reset UI modal về trạng thái chờ
        function resetModalUI() {
            const galleryBox = document.getElementById('gallery-container');
            const reviewBox = document.getElementById('reviews-container');
            // Reset Tab Hình ảnh
            galleryBox.innerHTML = '<p style="text-align:center; width:100%; grid-column:span 3; color:#666; margin-top:10px;">Đang tải dữ liệu...</p>';
            document.getElementById('no-img-msg').style.display = 'none';
            // Reset Tab Đánh giá
            reviewBox.innerHTML = '';
            document.getElementById('no-review-msg').style.display = 'none';
            // Reset Form đánh giá về trạng thái thêm mới
            const myBox = document.getElementById('myReviewDisplay');
            if (myBox) {
                myBox.style.display = 'none';
                myBox.dataset.stars = "";
                myBox.dataset.content = "";
            }
            // Hiện form nhập liệu nếu user chưa đánh giá sân mới
            const formBox = document.getElementById('reviewFormBox');
            if (formBox) {
                formBox.style.display = 'block';
                document.getElementById('reviewContent').value = '';
                setRating(5);
                document.getElementById('formAction').value = 'add';
                document.getElementById('formTitle').innerText = 'Viết đánh giá mới:';

                const btnCancel = document.getElementById('btnCancelEdit');
                if (btnCancel) btnCancel.style.display = 'none';
            }
            // Mặc định quay về tab hình ảnh đầu tiên
            switchTab('tab-images', document.querySelector('.m-tab'));
        }
        // Hàm gọi API lấy dữ liệu
        function loadVenueData(id) {
            fetch(`laydulieuanh_danhgia.php?id=${id}`)
                .then(response => response.json())
                .then(details => {
                    renderGallery(details.images);
                    renderReviews(details.reviews, details.my_review);
                })
                .catch(err => console.error('Lỗi tải dữ liệu:', err));
        }
        // Render ảnh album
        function renderGallery(images) {
            const galleryBox = document.getElementById('gallery-container');
            galleryBox.innerHTML = '';

            if (images && images.length > 0) {
                images.forEach(imgUrl => {
                    galleryBox.innerHTML += `<img src="${imgUrl}" class="modal-gallery-item" onclick="viewFullImage('${imgUrl}')">`;
                });
            } else {
                document.getElementById('no-img-msg').style.display = 'block';
            }
        }

        // Render đánh giá & Xử lý trạng thái
        function renderReviews(reviews, myReview) {
            const container = document.getElementById('reviews-container');
            const myBox = document.getElementById('myReviewDisplay');
            const formBox = document.getElementById('reviewFormBox');

            container.innerHTML = '';

        //đánh giá của mình
            if (myReview) {
                if (myBox) {
                    myBox.style.display = 'block';
                    document.getElementById('myReviewContent').innerText = myReview.noi_dung;

                    let stars = '';
                    for (let i = 0; i < 5; i++) stars += (i < myReview.so_sao) ? '<i class="fas fa-star"></i> ' : '<i class="far fa-star"></i> ';
                    document.getElementById('myReviewStars').innerHTML = stars;
                    let existingReply = document.getElementById('myReviewReply');
                    if (existingReply) existingReply.remove();

                    if (myReview.phan_hoi_chu_san) {
                        const replyDiv = document.createElement('div');
                        replyDiv.id = 'myReviewReply';
                        replyDiv.style.cssText = "margin-top: 10px; background: #f0fff4; padding: 10px; border-radius: 6px; border-left: 3px solid #27ae60;";
                        replyDiv.innerHTML = `
                    <div style="font-weight: bold; font-size: 12px; color: #27ae60; margin-bottom: 4px;">
                        <i class="fas fa-reply"></i> Phản hồi từ chủ sân
                    </div>
                    <div style="font-size: 13px; color: #555;">${myReview.phan_hoi_chu_san}</div>
                `;
                        myBox.appendChild(replyDiv);
                    }

                    myBox.dataset.stars = myReview.so_sao;
                    myBox.dataset.content = myReview.noi_dung;
                }
                if (formBox) formBox.style.display = 'none';
            } else {
                if (myBox) myBox.style.display = 'none';
                if (formBox) {
                    formBox.style.display = 'block';
                    document.getElementById('formAction').value = 'add';
                    document.getElementById('formTitle').innerText = 'Viết đánh giá mới:';
                    document.getElementById('btnCancelEdit').style.display = 'none';
                }
            }

            // đánh giá khác
            if (reviews && reviews.length > 0) {
                document.getElementById('no-review-msg').style.display = 'none';
                reviews.forEach(rv => {
                    let starsHtml = '';
                    for (let i = 0; i < 5; i++) starsHtml += (i < rv.so_sao) ? '<i class="fas fa-star"></i> ' : '<i class="far fa-star"></i> ';

                    const isMe = (myReview && rv.nguoi_dung_id == myReview.nguoi_dung_id);

                    // phản hồi
                    let replyBlock = '';
                    if (rv.phan_hoi_chu_san && rv.phan_hoi_chu_san.trim() !== '') {
                        replyBlock = `
                    <div style="margin-top: 10px; background: #f9f9f9; padding: 10px; border-radius: 6px; border-left: 3px solid #27ae60; margin-left: 0;">
                        <div style="font-weight: bold; font-size: 12px; color: #27ae60; margin-bottom: 4px;">
                            <i class="fas fa-reply"></i> Phản hồi từ chủ sân
                        </div>
                        <div style="font-size: 13px; color: #555; font-style: italic;">
                            "${rv.phan_hoi_chu_san}"
                        </div>
                        <div style="font-size: 11px; color: #999; margin-top:4px; text-align:right;">
                            ${rv.ngay_phan_hoi ? 'Đã trả lời lúc: ' + rv.ngay_phan_hoi : ''}
                        </div>
                    </div>
                `;
                    }

                    container.innerHTML += `
            <div class="review-item" style="${isMe ? 'background:#f9fcf9;' : ''}">
                <img src="${rv.avatar_url}" class="review-avatar">
                <div class="review-content">
                    <div class="review-name">${rv.ho_ten} ${isMe ? '<span style="color:#27ae60; font-size:11px;">(Bạn)</span>' : ''}</div>
                    <div class="review-stars">${starsHtml}</div>
                    <div class="review-text">${rv.noi_dung ? rv.noi_dung : ''}</div>
                    
                    ${replyBlock} <div class="review-date">${rv.ngay_tao_fmt}</div>
                </div>
            </div>`;
                });
            } else {
                document.getElementById('no-review-msg').style.display = 'block';
            }
        }

        // Bật chế độ sửa
        function enableEditMode() {
            const myBox = document.getElementById('myReviewDisplay');
            const formBox = document.getElementById('reviewFormBox');

            myBox.style.display = 'none';
            formBox.style.display = 'block';
            const oldStars = myBox.dataset.stars;
            const oldContent = myBox.dataset.content;
            setRating(oldStars);
            document.getElementById('reviewContent').value = oldContent;
            document.getElementById('formAction').value = 'update';
            document.getElementById('formTitle').innerText = 'Chỉnh sửa đánh giá:';
            document.getElementById('btnCancelEdit').style.display = 'block';
        }

        // Hủy chế độ sửa
        function cancelEditMode() {
            document.getElementById('reviewFormBox').style.display = 'none';
            document.getElementById('myReviewDisplay').style.display = 'block';
        }

        // Reset form về mặc định
        function resetReviewForm() {
            if (!document.getElementById('reviewFormBox')) return;
            document.getElementById('reviewContent').value = '';
            setRating(5);
            document.getElementById('formAction').value = 'add';
        }

        // Gửi đánh giá
        function submitReview() {
            // Lấy ID trực tiếp từ input hidden để đảm bảo chính xác nhất tại thời điểm bấm nút
            const coSoId = document.getElementById('currentCoSoId') ? document.getElementById('currentCoSoId').value : 0;
            const action = document.getElementById('formAction').value;
            const stars = document.getElementById('ratingValue').value;
            const content = document.getElementById('reviewContent').value;

            if (coSoId == 0) {
                showNotification("Lỗi: Không xác định được sân. Vui lòng tải lại trang.");
                return;
            }

            if (!content.trim()) {
                showNotification('Vui lòng nhập nội dung đánh giá!');
                return;
            }

            const formData = new FormData();
            formData.append('action', action);
            formData.append('co_so_id', coSoId);
            formData.append('stars', stars);
            formData.append('content', content);
            fetch('add_up_del_danhgia.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.reload();
                    } else {
                        if (data.redirect) {
                            window.location.reload();
                        } else {
                            showNotification(data.message || 'Có lỗi xảy ra');
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Lỗi kết nối đến máy chủ');
                });
        }

        function deleteMyReview() {
            if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')) return;

            const coSoId = document.getElementById('currentCoSoId').value;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('co_so_id', coSoId);
            fetch('add_up_del_danhgia.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.reload();
                    } else {
                        showNotification(data.message || 'Lỗi khi xóa');
                    }
                })
                .catch(err => {
                    console.error('Lỗi fetch:', err);
                    showNotification('Lỗi kết nối khi xóa.');
                });
        }



        // Chuyển Tab
        function switchTab(tabId, tabElement) {
            document.querySelectorAll('.tab-content-panel').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.m-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            if (tabElement) tabElement.classList.add('active');
        }

        function closeModal() {
            document.getElementById('venueModal').style.display = 'none';
        }

        // Lightbox xem ảnh to
        function viewFullImage(src) {
            const lightbox = document.getElementById('lightboxModal');
            const img = document.getElementById('lightboxImg');
            lightbox.style.display = "flex";
            img.src = src;
        }

        function closeFullImage() {
            document.getElementById('lightboxModal').style.display = "none";
        }

        // Đóng modal khi click ra ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('venueModal')) {
                closeModal();
            }
        }

        // Phím ESC để đóng Lightbox
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeFullImage();
            }
        });

        function showNotification(message, type = 'danger') {
            const area = document.getElementById('notification-area');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerText = message;
            area.appendChild(alertDiv);
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
</body>

</html>
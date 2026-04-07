<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id_chu'])) {
    header("Location: ../taikhoan/dangnhapchu.php");
    exit();
}
$user_id = $_SESSION['user_id_chu'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['co_so_id'])) {
        $co_so_id = intval($_POST['co_so_id']);
        $check = $conn->query("SELECT id FROM co_so WHERE id = $co_so_id AND chu_san_id = $user_id");
        if ($check->num_rows == 0) {
            $_SESSION['error'] = 'Bạn không có quyền chỉnh sửa cơ sở này!';
            header("Location: ../chusan/quanly.php");
            exit();
        }
        $stmt = $conn->prepare("
            UPDATE co_so 
            SET ten_co_so=?, dia_chi_cu_the=?, quan_huyen=?, tinh_thanh=?, hotline=?, gio_mo_cua=?, gio_dong_cua=? 
            WHERE id=?
        ");

        $stmt->bind_param(
            "sssssssi",
            $_POST['ten_cs'],
            $_POST['dc_cu_the'],
            $_POST['qhuyen'],
            $_POST['tinh'],
            $_POST['hotline'],
            $_POST['gio_mo_cua'],
            $_POST['gio_dong_cua'],
            $co_so_id
        );
        $stmt->execute();

        $dir_uploads = "../assets/img/uploads/";
        if (!file_exists($dir_uploads)) {
            mkdir($dir_uploads, 0777, true);
        }

        if (!empty($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
            foreach ($_POST['delete_ids'] as $del_id) {
                $del_id = intval($del_id);
                $res = $conn->query("SELECT url_hinh FROM hinh_anh_san WHERE id=$del_id AND co_so_id=$co_so_id");
                if ($row = $res->fetch_assoc()) {
                    $file_path = "../" . $row['url_hinh'];
                    if (file_exists($file_path)) unlink($file_path);
                    $conn->query("DELETE FROM hinh_anh_san WHERE id=$del_id");
                }
            }
        }

        if (!empty($_FILES['avatar_file']['name'])) {
            $old_avt = $conn->query("SELECT id, url_hinh FROM hinh_anh_san WHERE co_so_id=$co_so_id AND avata=1");
            while ($row = $old_avt->fetch_assoc()) {
                $old_path = "../" . $row['url_hinh'];
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
                $conn->query("DELETE FROM hinh_anh_san WHERE id=" . $row['id']);
            }

            //Upload Avatar mới
            $fname = "avt_" . time() . "_" . basename($_FILES['avatar_file']['name']);
            move_uploaded_file($_FILES['avatar_file']['tmp_name'], $dir_uploads . $fname);
            $db_url = "assets/img/uploads/" . $fname;

            //Insert Avatar mới
            $conn->query("
                INSERT INTO hinh_anh_san (co_so_id, url_hinh, avata, la_anh_dai_dien)
                VALUES ($co_so_id, '$db_url', 1, 0)
            ");
        }

        if (!empty($_FILES['banner_file']['name'])) {
            // Xóa cũ
            $old_bnr = $conn->query("SELECT id, url_hinh FROM hinh_anh_san WHERE co_so_id=$co_so_id AND la_anh_dai_dien=1");
            while ($row = $old_bnr->fetch_assoc()) {
                $old_path = "../" . $row['url_hinh'];
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
                $conn->query("DELETE FROM hinh_anh_san WHERE id=" . $row['id']);
            }

            // Up mới
            $fname = "bnr_" . time() . "_" . basename($_FILES['banner_file']['name']);
            move_uploaded_file($_FILES['banner_file']['tmp_name'], $dir_uploads . $fname);
            $db_url = "assets/img/uploads/" . $fname;

            $conn->query("
                INSERT INTO hinh_anh_san (co_so_id, url_hinh, avata, la_anh_dai_dien)
                VALUES ($co_so_id, '$db_url', 0, 1)
            ");
        }

        //ab ảnh
        if (!empty($_FILES['gallery_files']['name'])) {
            foreach ($_FILES['gallery_files']['name'] as $i => $name) {
                if ($_FILES['gallery_files']['size'][$i] > 0) {
                    $fname = "gal_" . time() . "_{$i}_" . basename($name);

                    if (move_uploaded_file($_FILES['gallery_files']['tmp_name'][$i], $dir_uploads . $fname)) {
                        $db_url = "assets/img/uploads/" . $fname;
                        $conn->query("
                            INSERT INTO hinh_anh_san (co_so_id, url_hinh, avata, la_anh_dai_dien)
                            VALUES ($co_so_id, '$db_url', 0, 0)
                        ");
                    }
                }
            }
        }

        $_SESSION['success'] = 'Cập nhật thông tin cơ sở thành công!';
        header("Location: ../chusan/quanly.php?tab=profile_coso");
        exit();
    }
}

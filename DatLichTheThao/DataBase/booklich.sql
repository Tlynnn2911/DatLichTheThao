-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 17, 2026 lúc 06:25 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `booklich`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `co_so`
--

CREATE TABLE `co_so` (
  `id` int(11) NOT NULL,
  `chu_san_id` int(11) NOT NULL,
  `mon_the_thao_id` int(11) NOT NULL,
  `ten_co_so` varchar(150) NOT NULL,
  `dia_chi_cu_the` varchar(255) NOT NULL,
  `tinh_thanh` varchar(50) DEFAULT NULL,
  `quan_huyen` varchar(50) DEFAULT NULL,
  `hotline` varchar(15) DEFAULT NULL,
  `gio_mo_cua` time DEFAULT NULL,
  `gio_dong_cua` time DEFAULT NULL,
  `gio_bat_dau_toi` time DEFAULT '17:00:00',
  `gia_sang` int(11) DEFAULT 0,
  `gia_toi` int(11) DEFAULT 0,
  `gia_cuoi_tuan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `co_so`
--

INSERT INTO `co_so` (`id`, `chu_san_id`, `mon_the_thao_id`, `ten_co_so`, `dia_chi_cu_the`, `tinh_thanh`, `quan_huyen`, `hotline`, `gio_mo_cua`, `gio_dong_cua`, `gio_bat_dau_toi`, `gia_sang`, `gia_toi`, `gia_cuoi_tuan`) VALUES
(1, 2, 3, 'Sân thể thao UTT', 'Số 54 Triều Khúc', 'Hà Nội', 'Thanh Liệt', '0123456789', '06:00:00', '23:00:00', '16:30:00', 60000, 80000, 80000),
(2, 2, 2, 'BMC', '286 Nguyễn Xiển', 'Hà Nội', 'Thanh Liệt', '0123456', '05:00:00', '23:30:00', '17:00:00', 0, 0, 0),
(3, 6, 1, 'Sân Tân Triều', 'Phố Vũ Đức Úy, Triều Khúc, Tân Triều, Thanh Trì', 'Hà Nội', 'Thanh Trì', '08675464', '08:00:00', '22:30:00', '17:00:00', 200000, 250000, 300000),
(4, 7, 1, 'Sân The One Gamuda', 'Đường số 2, Gamuda Gardens, Trần Phú, Hoàng Mai', 'Hà Nội', 'Hoàng Mai', '032342', '06:00:00', '23:00:00', '17:00:00', 250000, 300000, 350000),
(5, 8, 1, 'Sân Zone 9', 'Khu đô thị Văn Quán, Hà Đông', 'Hà Nội', 'Hà Đông', '07534532', '05:00:00', '24:00:00', '19:00:00', 200000, 220000, 300000),
(6, 10, 2, 'Minh Dương BADMINTON', 'Ngách 28, Ngõ 286 Nguyễn Xiển, Xã Tân Triều', 'Hà Nội', 'Thanh Trì', '0324342', '07:00:00', '23:59:59', '17:00:00', 50000, 80000, 80000),
(7, 10, 3, 'TukTuk Pickleball', '18 Chợ Yên Xá, Tân Triều', 'Hà Nội', 'Thanh Trì', '0243242', '05:00:00', '23:00:00', '18:00:00', 120000, 150000, 180000),
(8, 16, 1, 'Sân Bóng C500 HVAN', 'Đường 19/5, P. Văn Quán, Hà Đông, Hà Nội', 'Hà Nội', 'Hà Đông', '0123456788', '05:00:00', '22:00:00', '17:00:00', 200000, 250000, 300000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `co_so_id` int(11) NOT NULL,
  `so_sao` int(11) NOT NULL CHECK (`so_sao` >= 1 and `so_sao` <= 5),
  `noi_dung` text DEFAULT NULL,
  `phan_hoi_chu_san` text DEFAULT NULL,
  `ngay_phan_hoi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_gia`
--

INSERT INTO `danh_gia` (`id`, `nguoi_dung_id`, `co_so_id`, `so_sao`, `noi_dung`, `phan_hoi_chu_san`, `ngay_phan_hoi`, `ngay_tao`) VALUES
(1, 4, 1, 4, 'Sân rất đẹp, đánh rất ok', 'Cảm ơn bạn đã ủng hộ sân!', '2025-12-23 17:25:26', '2025-12-23 17:25:26'),
(19, 5, 1, 5, 'ngon luôn', NULL, '2025-12-23 17:25:53', '2025-12-23 17:25:53'),
(23, 5, 2, 5, 'ngon', NULL, '2025-12-23 06:32:57', '2025-12-23 06:32:57'),
(24, 12, 1, 5, 'sân này đúng đỉnh', NULL, '2025-12-24 02:50:19', '2025-12-24 02:50:19'),
(25, 13, 1, 4, 'nên trải nghiệm', 'cảm ơn b', '2026-01-03 17:30:37', '2025-12-24 02:51:18'),
(26, 14, 1, 5, 'cuối tuần đi đánh cùng các bạn đúng vui luôn', NULL, '2025-12-24 02:52:36', '2025-12-24 02:52:36'),
(27, 15, 1, 5, '5 sao chất lượng', NULL, '2025-12-24 02:53:05', '2025-12-24 02:53:05'),
(28, 5, 5, 5, 'ngon', NULL, '2026-01-03 16:53:01', '2026-01-03 16:53:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dat_lich`
--

CREATE TABLE `dat_lich` (
  `id` int(11) NOT NULL,
  `ma_dat_lich` varchar(20) DEFAULT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `co_so_id` int(11) NOT NULL,
  `san_id` int(11) NOT NULL,
  `ngay_dat` date NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `tong_tien` decimal(10,2) DEFAULT NULL,
  `trang_thai_don` enum('cho_xac_nhan','da_xac_nhan','hoan_thanh','da_huy') DEFAULT 'cho_xac_nhan',
  `sdt_vanglai` varchar(15) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `dat_lich`
--

INSERT INTO `dat_lich` (`id`, `ma_dat_lich`, `nguoi_dung_id`, `co_so_id`, `san_id`, `ngay_dat`, `gio_bat_dau`, `gio_ket_thuc`, `tong_tien`, `trang_thai_don`, `sdt_vanglai`, `ngay_tao`) VALUES
(1, 'BOOK-001', 3, 1, 1, '2025-12-17', '17:00:00', '20:00:00', 100000.00, 'hoan_thanh', NULL, '2025-12-16 05:03:11'),
(2, 'BOOK-002', 4, 1, 2, '2025-12-15', '20:00:00', '22:00:00', 200000.00, 'hoan_thanh', NULL, '2025-12-16 05:03:22'),
(3, 'BOOK-003', 4, 1, 3, '2025-12-23', '17:00:00', '19:00:00', 400000.00, 'da_huy', NULL, '2025-12-21 10:55:22'),
(4, 'BOOK-004', 5, 1, 1, '2025-12-23', '19:00:00', '22:00:00', 300000.00, 'hoan_thanh', NULL, '2025-12-21 10:56:38'),
(5, 'BOOK694A96C9C3969', 5, 1, 3, '2025-12-23', '20:00:00', '21:00:00', 80000.00, 'da_huy', NULL, '2025-12-23 13:19:05'),
(6, 'BOOK694A9ACD6CB2A', 5, 1, 1, '2025-12-24', '13:00:00', '14:30:00', 90000.00, 'hoan_thanh', NULL, '2025-12-23 13:36:13'),
(7, 'BOOK694AAA54B8472', 5, 1, 3, '2025-12-23', '22:00:00', '23:00:00', 80000.00, 'hoan_thanh', NULL, '2025-12-23 14:42:28'),
(8, 'BOOK694AAB4AA4057', 5, 1, 9, '2025-12-23', '22:30:00', '23:00:00', 40000.00, 'hoan_thanh', NULL, '2025-12-23 14:46:34'),
(9, 'BOOK694AABA116C58', 5, 1, 3, '2025-12-25', '10:00:00', '12:00:00', 120000.00, 'da_huy', NULL, '2025-12-23 14:48:01'),
(13, 'BOOK6959478AE94CF', 5, 1, 3, '2026-01-04', '12:00:00', '13:00:00', 80000.00, 'da_huy', NULL, '2026-01-03 16:44:58'),
(14, 'BOOK695B42B2DCAE9', NULL, 1, 9, '2026-01-05', '14:30:00', '16:00:00', 90000.00, 'da_huy', '012345678', '2026-01-05 04:48:50'),
(15, 'BOOK695B459E270BC', NULL, 1, 16, '2026-01-05', '15:00:00', '16:30:00', 90000.00, 'da_huy', '01231245', '2026-01-05 05:01:18'),
(16, 'BOOK695B47A697682', NULL, 1, 16, '2026-01-05', '16:00:00', '18:00:00', 150000.00, 'da_huy', '01231245', '2026-01-05 05:09:58'),
(17, 'BOOK695B47E85435C', NULL, 1, 9, '2026-01-05', '15:00:00', '16:30:00', 90000.00, 'da_huy', '01231245', '2026-01-05 05:11:04'),
(18, 'BOOK696726D9482FB', 5, 1, 3, '2026-01-14', '12:30:00', '15:00:00', 150000.00, 'da_huy', NULL, '2026-01-14 05:17:13'),
(19, 'BOOK6968E184A22F4', 5, 1, 3, '2026-01-15', '20:00:00', '21:30:00', 120000.00, 'da_huy', NULL, '2026-01-15 12:45:56'),
(20, 'BOOK6968E64D3BE3E', 5, 1, 2, '2026-01-15', '21:30:00', '23:00:00', 120000.00, 'hoan_thanh', NULL, '2026-01-15 13:06:21'),
(21, 'BOOK6969A7F97D954', 5, 1, 2, '2026-01-16', '20:30:00', '22:30:00', 160000.00, 'hoan_thanh', NULL, '2026-01-16 02:52:41'),
(22, 'BOOK6969A80B252F6', 5, 1, 3, '2026-01-16', '18:00:00', '19:30:00', 120000.00, 'da_huy', NULL, '2026-01-16 02:52:59'),
(23, 'BOOK696A27E38B5F6', 5, 6, 13, '2026-01-16', '21:30:00', '23:30:00', 160000.00, 'da_huy', NULL, '2026-01-16 11:58:27'),
(24, 'BOOK696A280A9BDD5', 5, 3, 24, '2026-01-18', '20:00:00', '22:00:00', 600000.00, 'cho_xac_nhan', NULL, '2026-01-16 11:59:06'),
(25, 'BOOK696B1D1A597B5', 5, 1, 9, '2026-01-18', '18:00:00', '20:00:00', 160000.00, 'cho_xac_nhan', NULL, '2026-01-17 05:24:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinh_anh_san`
--

CREATE TABLE `hinh_anh_san` (
  `id` int(11) NOT NULL,
  `co_so_id` int(11) NOT NULL,
  `url_hinh` varchar(255) NOT NULL,
  `avata` tinyint(1) NOT NULL DEFAULT 0,
  `la_anh_dai_dien` tinyint(1) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hinh_anh_san`
--

INSERT INTO `hinh_anh_san` (`id`, `co_so_id`, `url_hinh`, `avata`, `la_anh_dai_dien`, `ngay_tao`) VALUES
(14, 2, 'assets/img/uploads/avt_1766432665_z7345131137621_f3e5a0da577280a7ac0ce2ebedb525a2.jpg', 1, 0, '2025-12-22 19:44:25'),
(15, 2, 'assets/img/uploads/bnr_1766432665_image-87.png.webp', 0, 1, '2025-12-22 19:44:25'),
(16, 2, 'assets/img/uploads/gal_1766432665_0_san-cau-long-tien-dinh-sport-phan-trong-tue-1_thumb_500.webp', 0, 0, '2025-12-22 19:44:25'),
(17, 2, 'assets/img/uploads/gal_1766432665_1_unnamed-2025-05-17T104627.750.webp', 0, 0, '2025-12-22 19:44:25'),
(18, 2, 'assets/img/uploads/gal_1766432665_2_unnamed-2025-05-17T104643.182.webp', 0, 0, '2025-12-22 19:44:25'),
(19, 3, 'assets/img/uploads/avt_1766433723_311574603_562899955836567_3073892826290017849_n.jpg', 1, 0, '2025-12-22 20:02:03'),
(20, 3, 'assets/img/uploads/bnr_1766433723_unnamed.jpg', 0, 1, '2025-12-22 20:02:03'),
(21, 3, 'assets/img/uploads/gal_1766433723_0_516793995_1333337355459486_7475889365592874391_n.jpg', 0, 0, '2025-12-22 20:02:03'),
(22, 3, 'assets/img/uploads/gal_1766433723_1_516918921_1333337378792817_8912837955568378945_n.jpg', 0, 0, '2025-12-22 20:02:03'),
(23, 3, 'assets/img/uploads/gal_1766433723_2_518311326_1333337365459485_1720094769133781238_n.jpg', 0, 0, '2025-12-22 20:02:03'),
(24, 4, 'assets/img/uploads/avt_1766433834_images.png', 1, 0, '2025-12-22 20:03:54'),
(25, 4, 'assets/img/uploads/bnr_1766433834_the-one-gamuda-5.webp', 0, 1, '2025-12-22 20:03:54'),
(26, 4, 'assets/img/uploads/gal_1766433834_0_538365305_1392271102901510_2539254876531032210_n.jpg', 0, 0, '2025-12-22 20:03:54'),
(27, 4, 'assets/img/uploads/gal_1766433834_1_the-one-gamuda.webp', 0, 0, '2025-12-22 20:03:54'),
(28, 5, 'assets/img/uploads/avt_1766433924_185334361_870687400321424_2359446354130228595_n.jpg', 1, 0, '2025-12-22 20:05:24'),
(29, 5, 'assets/img/uploads/bnr_1766433924_104685398_637695560287277_8857999970780042198_n.jpg', 0, 1, '2025-12-22 20:05:24'),
(30, 5, 'assets/img/uploads/gal_1766433924_0_104101427_635160777207422_82308714965275892_n.jpg', 0, 0, '2025-12-22 20:05:24'),
(31, 5, 'assets/img/uploads/gal_1766433924_1_104685584_636090927114407_8081653206805378338_n.jpg', 0, 0, '2025-12-22 20:05:24'),
(32, 5, 'assets/img/uploads/gal_1766433924_2_106195649_637695470287286_385369277556463921_n.jpg', 0, 0, '2025-12-22 20:05:24'),
(33, 5, 'assets/img/uploads/gal_1766433924_3_482319730_1669868237249432_6355295750683853766_n.jpg', 0, 0, '2025-12-22 20:05:24'),
(34, 6, 'assets/img/uploads/avt_1766434051_484031991_122109544394792847_2780371753593097920_n.jpg', 1, 0, '2025-12-22 20:07:31'),
(35, 6, 'assets/img/uploads/bnr_1766434051_526850617_122150701574792847_4170325988819061156_n.jpg', 0, 1, '2025-12-22 20:07:31'),
(36, 6, 'assets/img/uploads/gal_1766434051_0_492428274_122129346320792847_5033004049324334630_n.jpg', 0, 0, '2025-12-22 20:07:31'),
(37, 6, 'assets/img/uploads/gal_1766434051_1_498178394_122135366000792847_147798615145715598_n.jpg', 0, 0, '2025-12-22 20:07:31'),
(38, 6, 'assets/img/uploads/gal_1766434051_2_527793246_122151062348792847_7813302116877353978_n.jpg', 0, 0, '2025-12-22 20:07:31'),
(39, 6, 'assets/img/uploads/gal_1766434051_3_585860439_122169290270792847_8727341531705452055_n.jpg', 0, 0, '2025-12-22 20:07:31'),
(40, 6, 'assets/img/uploads/gal_1766434051_4_600200949_122173400636792847_6328360543226893093_n.jpg', 0, 0, '2025-12-22 20:07:31'),
(41, 7, 'assets/img/uploads/avt_1766434107_451444048_122152039736094647_828374827874680743_n.jpg', 1, 0, '2025-12-22 20:08:27'),
(42, 7, 'assets/img/uploads/bnr_1766434107_500129874_122189937842094647_5216987345270912329_n.jpg', 0, 1, '2025-12-22 20:08:27'),
(43, 7, 'assets/img/uploads/gal_1766434107_0_484794823_122181318224094647_123648425833740989_n.jpg', 0, 0, '2025-12-22 20:08:27'),
(44, 7, 'assets/img/uploads/gal_1766434107_1_487313471_122182799210094647_1625731967897196994_n.jpg', 0, 0, '2025-12-22 20:08:27'),
(45, 7, 'assets/img/uploads/gal_1766434107_2_496946816_122188800572094647_7301018705189465606_n.jpg', 0, 0, '2025-12-22 20:08:27'),
(46, 7, 'assets/img/uploads/gal_1766434107_3_500056613_122189937854094647_1551723572918304843_n.jpg', 0, 0, '2025-12-22 20:08:27'),
(53, 1, 'assets/img/uploads/bnr_1766515336_598705052_1306982681459312_4536171341376184664_n.jpg', 0, 1, '2025-12-23 18:42:16'),
(54, 1, 'assets/img/uploads/gal_1766515336_0_f4b8e904-77c5-4f20-a202-2401799905d6.jpg', 0, 0, '2025-12-23 18:42:16'),
(55, 1, 'assets/img/uploads/gal_1766515336_1_faaf0f6c-b9bb-4aa9-af4f-b4c1ae6c413e.jpg', 0, 0, '2025-12-23 18:42:16'),
(56, 1, 'assets/img/uploads/avt_1768371136_Logo UTT_logo-40x60-xanh.png', 1, 0, '2026-01-14 06:12:16'),
(57, 8, 'assets/img/uploads/avt_1768371199_Logo_hoc_vien_ANND.png', 1, 0, '2026-01-14 06:13:19'),
(58, 8, 'assets/img/uploads/bnr_1768371199_c5.jfif', 0, 1, '2026-01-14 06:13:19'),
(59, 8, 'assets/img/uploads/gal_1768371199_0_500.jfif', 0, 0, '2026-01-14 06:13:19'),
(60, 8, 'assets/img/uploads/gal_1768371199_1_c500.jpg', 0, 0, '2026-01-14 06:13:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoa_san_theo_gio`
--

CREATE TABLE `khoa_san_theo_gio` (
  `id` int(11) NOT NULL,
  `san_id` int(11) NOT NULL,
  `ngay_khoa` date NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khoa_san_theo_gio`
--

INSERT INTO `khoa_san_theo_gio` (`id`, `san_id`, `ngay_khoa`, `gio_bat_dau`, `gio_ket_thuc`, `ngay_tao`) VALUES
(5, 1, '2026-01-05', '12:00:00', '12:30:00', '2026-01-04 18:48:06'),
(6, 1, '2026-01-05', '12:30:00', '13:00:00', '2026-01-04 18:48:06'),
(7, 1, '2026-01-05', '13:30:00', '14:00:00', '2026-01-04 18:48:06'),
(8, 1, '2026-01-05', '14:00:00', '14:30:00', '2026-01-04 18:48:06'),
(9, 2, '2026-01-05', '11:30:00', '12:00:00', '2026-01-04 18:48:06'),
(10, 2, '2026-01-05', '12:00:00', '12:30:00', '2026-01-04 18:48:06'),
(11, 2, '2026-01-05', '12:30:00', '13:00:00', '2026-01-04 18:48:06'),
(12, 2, '2026-01-05', '13:00:00', '13:30:00', '2026-01-04 18:48:06'),
(14, 2, '2026-01-05', '14:00:00', '14:30:00', '2026-01-04 18:48:06'),
(15, 2, '2026-01-05', '14:30:00', '15:00:00', '2026-01-04 18:48:06'),
(16, 3, '2026-01-05', '12:00:00', '12:30:00', '2026-01-04 18:48:06'),
(17, 3, '2026-01-05', '12:30:00', '13:00:00', '2026-01-04 18:48:06'),
(18, 3, '2026-01-05', '13:00:00', '13:30:00', '2026-01-04 18:48:06'),
(19, 3, '2026-01-05', '13:30:00', '14:00:00', '2026-01-04 18:48:06'),
(20, 3, '2026-01-05', '14:00:00', '14:30:00', '2026-01-04 18:48:06'),
(21, 9, '2026-01-05', '12:30:00', '13:00:00', '2026-01-04 18:48:06'),
(22, 9, '2026-01-05', '13:00:00', '13:30:00', '2026-01-04 18:48:06'),
(23, 9, '2026-01-05', '13:30:00', '14:00:00', '2026-01-04 18:48:06'),
(24, 16, '2026-01-05', '13:00:00', '13:30:00', '2026-01-04 18:48:06'),
(25, 2, '2026-01-05', '13:30:00', '14:00:00', '2026-01-05 04:27:11'),
(26, 28, '2026-01-14', '14:00:00', '14:30:00', '2026-01-14 06:18:36'),
(27, 28, '2026-01-14', '14:30:00', '15:00:00', '2026-01-14 06:18:36'),
(28, 29, '2026-01-14', '14:00:00', '14:30:00', '2026-01-14 06:18:36'),
(29, 29, '2026-01-14', '14:30:00', '15:00:00', '2026-01-14 06:18:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mon_the_thao`
--

CREATE TABLE `mon_the_thao` (
  `id` int(11) NOT NULL,
  `ten_mon` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `mon_the_thao`
--

INSERT INTO `mon_the_thao` (`id`, `ten_mon`) VALUES
(1, 'Bóng đá'),
(2, 'Cầu lông'),
(3, 'Pickleball');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `sdt` varchar(15) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `vai_tro` enum('admin','chu_san','khach_hang') DEFAULT 'khach_hang',
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ho_ten`, `sdt`, `mat_khau`, `email`, `vai_tro`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Nguyễn Văn Admin', '0901111111', '$2y$10$uShkLn2HiD6y0zD6q0gmlefh9vEv.Z/06DoyI5k6LdUYOhShsPU16', 'admin@sys.com', 'admin', 1, '2025-12-16 04:59:08'),
(2, 'Trần Chủ Sân', '0902222222', '$2y$10$KyFsoM6QaDjrwwEGs1Nx7eFiJgAt7zp.ygUwr/JtOquXHatahFJbu', 'chusan@gmail.com', 'chu_san', 1, '2025-12-16 04:59:08'),
(3, 'Lê Khách Hàng A', '0903333333', '$2y$10$mb/jLMLakPaWhiHCAptQ5uWoe/dj6QmaEvJaQExcOcwkbUecKc0kW', 'khacha@gmail.com', 'khach_hang', 1, '2025-12-16 04:59:08'),
(4, 'Phạm Khách Hàng B', '0904444444', '$2y$10$6CGGNqrJ3Ui78BztlHxa6ehyXu04XCDEnPhD7UEL47jFVw65nE85e', 'khachb@gmail.com', 'khach_hang', 1, '2025-12-16 04:59:08'),
(5, 'Nguyễn Mạnh Quỳnh', '0365614536', '$2y$10$ehl/22uKXt.a04LZd0EDh.eW5255swT2tzQKttcOVskwhI0G/yqcq', 'hnyuq15032005@gmail.com', 'khach_hang', 1, '2025-12-18 12:26:59'),
(6, 'Nguyễn Văn Bách', '0905555555', '$2y$10$FWMI.RJ2ZXgwzPcHEcuh2OGpZrqZeN.UvZtkrKWX.Wd0z7ecj6Vee', 'bachnguyen@gmail.com', 'chu_san', 1, '2025-12-20 10:26:53'),
(7, 'Kiều Cao Long', '0906666666', '$2y$10$05z61ecnHsyPZoW3uUDBleVQaNA8BepByG9xizfyVazGB..HZu4L.', 'LongCao@gmail.com', 'chu_san', 1, '2025-12-20 10:27:56'),
(8, 'Ngưu Thị Lý', '0907777777', '$2y$10$26l1srFutiDLreMsTYibzO95Jggefx6FkywoMiYR7O6yqXZ99OKny', 'nguthily@gmail.com', 'chu_san', 1, '2025-12-20 10:28:50'),
(10, 'Liễu Như Yên', '0909999999', '$2y$10$p.4hLsNhjm33XJTezMNSJe0MXvW5Y5keDaiud2/qnOxi4PlgkuaV.', 'nhieucoso@gmail.com', 'chu_san', 1, '2025-12-20 10:30:11'),
(11, 'Đăng Văn Ký', '0364567890', '$2y$10$xrrK4MhqRTLmNWAuBQkWeOCQyVySg09vHwGYr6Ud2FxzqCgIFTRdC', NULL, 'khach_hang', 1, '2025-12-23 17:31:26'),
(12, 'Kiều Minh Ánh', '0392920513', '$2y$10$zS7gbcIfGk.x9c/iRDMde.FtSyo.IoSzQCwOkAayC73DbTvsyUh/K', 'anh@gmail.com', 'khach_hang', 1, '2025-12-24 02:47:27'),
(13, 'Trương Thị Thùy Linh', '0374801798', '$2y$10$AKlZKsZ4hqmXDlw/.ap/WuirwaZ6r3XYFfO4ESRz2gXSkdcr7o2rm', 'linh@gmail.com', 'khach_hang', 1, '2025-12-24 02:48:38'),
(14, 'Nguyễn Khành Tùng', '0301111111', '$2y$10$1kJvzpR2KUit9l4g0Js7fOPCdz9/TFgdHJyME/OJjzSa4CR0IhWuS', 'tung@gmail.com', 'khach_hang', 1, '2025-12-24 02:49:20'),
(15, 'Lê Phương Minh', '0302222222', '$2y$10$kmmQ8gCtW8qlCAUUvj6OTutuzV2m9BMGyN5/Kh5yBYmU2aoTp700W', 'minh@gmail.com', 'khach_hang', 1, '2025-12-24 02:49:51'),
(16, 'Nguyễn Mạnh Quỳnh', '032345678', '$2y$10$zNUHjjeqSGkP6xW54AvqZ.4TaY5ytqo6Vwb/Tcqe5EZboWNDTQdN2', 'quynhclone1503@gmail.com', 'chu_san', 1, '2026-01-14 05:58:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san`
--

CREATE TABLE `san` (
  `id` int(11) NOT NULL,
  `co_so_id` int(11) NOT NULL,
  `ten_san` varchar(50) NOT NULL,
  `trang_thai` enum('mo','khoa') DEFAULT 'mo',
  `he_so_gia` float NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san`
--

INSERT INTO `san` (`id`, `co_so_id`, `ten_san`, `trang_thai`, `he_so_gia`) VALUES
(1, 1, 'Sân Số 1', 'mo', 1),
(2, 1, 'Sân Số 2', 'mo', 1),
(3, 1, 'Sân Số 3', 'mo', 1),
(4, 2, 'Sân Số 1', 'mo', 1),
(9, 1, 'Sân Số 4', 'mo', 1),
(12, 6, 'Sân 1', 'mo', 1),
(13, 6, 'Sân 2', 'mo', 1),
(14, 6, 'Sân 3', 'mo', 1.5),
(16, 1, 'Sân Số 5', 'mo', 1),
(18, 8, 'Sân 1', 'mo', 1),
(19, 8, 'Sân 2', 'mo', 1),
(20, 8, 'Sân 3', 'mo', 1),
(21, 8, 'Sân 4', 'mo', 1),
(22, 3, 'Sân Thường 1', 'mo', 1),
(23, 3, 'Sân Thường 2', 'mo', 1),
(24, 3, 'Sân Thường 3', 'mo', 1),
(25, 3, 'Sân Vip 1', 'mo', 1.2),
(26, 3, 'Sân Vip 2', 'mo', 1.2),
(27, 4, 'Sân 1', 'mo', 1),
(28, 4, 'Sân 2', 'mo', 1),
(29, 4, 'Sân 3', 'mo', 1),
(30, 4, 'Sân 4', 'mo', 1),
(31, 4, 'Sân 5', 'khoa', 1),
(32, 5, 'Sân 1', 'mo', 1),
(33, 5, 'Sân 2', 'mo', 1),
(34, 5, 'Sân 3 (11)', 'mo', 2),
(35, 5, 'Sân 4 (11)', 'mo', 2),
(36, 7, 'Sân Số 1', 'mo', 1),
(37, 7, 'Sân Số 2', 'mo', 1),
(38, 7, 'Sân Số 3', 'mo', 1),
(39, 7, 'Sân Vip 1', 'mo', 1.5),
(40, 7, 'Sân Vip 2', 'mo', 1.5);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `co_so`
--
ALTER TABLE `co_so`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotline` (`hotline`),
  ADD KEY `co_so_ibfk_1` (`chu_san_id`),
  ADD KEY `co_so_ibfk_2` (`mon_the_thao_id`);

--
-- Chỉ mục cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_venue` (`nguoi_dung_id`,`co_so_id`),
  ADD KEY `danh_gia_ibfk_2` (`co_so_id`);

--
-- Chỉ mục cho bảng `dat_lich`
--
ALTER TABLE `dat_lich`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_dat_lich` (`ma_dat_lich`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`),
  ADD KEY `san_id` (`san_id`);

--
-- Chỉ mục cho bảng `hinh_anh_san`
--
ALTER TABLE `hinh_anh_san`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hinh_anh_san_ibfk_1` (`co_so_id`);

--
-- Chỉ mục cho bảng `khoa_san_theo_gio`
--
ALTER TABLE `khoa_san_theo_gio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `san_id` (`san_id`);

--
-- Chỉ mục cho bảng `mon_the_thao`
--
ALTER TABLE `mon_the_thao`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sdt` (`sdt`);

--
-- Chỉ mục cho bảng `san`
--
ALTER TABLE `san`
  ADD PRIMARY KEY (`id`),
  ADD KEY `san_ibfk_1` (`co_so_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `co_so`
--
ALTER TABLE `co_so`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `dat_lich`
--
ALTER TABLE `dat_lich`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `hinh_anh_san`
--
ALTER TABLE `hinh_anh_san`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `khoa_san_theo_gio`
--
ALTER TABLE `khoa_san_theo_gio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `mon_the_thao`
--
ALTER TABLE `mon_the_thao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `san`
--
ALTER TABLE `san`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `co_so`
--
ALTER TABLE `co_so`
  ADD CONSTRAINT `co_so_ibfk_1` FOREIGN KEY (`chu_san_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `co_so_ibfk_2` FOREIGN KEY (`mon_the_thao_id`) REFERENCES `mon_the_thao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD CONSTRAINT `danh_gia_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `danh_gia_ibfk_2` FOREIGN KEY (`co_so_id`) REFERENCES `co_so` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `dat_lich`
--
ALTER TABLE `dat_lich`
  ADD CONSTRAINT `dat_lich_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`),
  ADD CONSTRAINT `dat_lich_ibfk_2` FOREIGN KEY (`san_id`) REFERENCES `san` (`id`);

--
-- Các ràng buộc cho bảng `hinh_anh_san`
--
ALTER TABLE `hinh_anh_san`
  ADD CONSTRAINT `hinh_anh_san_ibfk_1` FOREIGN KEY (`co_so_id`) REFERENCES `co_so` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `khoa_san_theo_gio`
--
ALTER TABLE `khoa_san_theo_gio`
  ADD CONSTRAINT `khoa_san_ibfk_1` FOREIGN KEY (`san_id`) REFERENCES `san` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `san`
--
ALTER TABLE `san`
  ADD CONSTRAINT `san_ibfk_1` FOREIGN KEY (`co_so_id`) REFERENCES `co_so` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$
--
-- Sự kiện
--
CREATE DEFINER=`root`@`localhost` EVENT `tu_dong_cap_nhat_trang_thai_theo_gio` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-12-23 20:28:15' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

    UPDATE `dat_lich`
    SET `trang_thai_don` = 'da_huy'
    WHERE `trang_thai_don` = 'cho_xac_nhan'
    AND TIMESTAMP(ngay_dat, gio_bat_dau) < NOW();


    UPDATE `dat_lich`
    SET `trang_thai_don` = 'hoan_thanh'
    WHERE `trang_thai_don` = 'da_xac_nhan'
    AND TIMESTAMP(ngay_dat, gio_ket_thuc) < NOW();
    
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

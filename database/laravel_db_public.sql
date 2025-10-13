-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th10 13, 2025 lúc 01:36 AM
-- Phiên bản máy phục vụ: 8.0.43-0ubuntu0.24.04.2
-- Phiên bản PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `laravel_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chuong_trinhs`
--

CREATE TABLE `chuong_trinhs` (
  `id` bigint UNSIGNED NOT NULL,
  `ma_chuong_trinh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_chuong_trinh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoi_luong` decimal(8,2) NOT NULL DEFAULT '0.00',
  `muc_tieu_dao_tao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `loai_hinh_dao_tao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tinh_trang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang áp dụng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chuong_trinhs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chuong_trinh_chuyen_de`
--

CREATE TABLE `chuong_trinh_chuyen_de` (
  `id` bigint UNSIGNED NOT NULL,
  `chuong_trinh_id` bigint UNSIGNED NOT NULL,
  `chuyen_de_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chuong_trinh_chuyen_de`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chuyen_des`
--

CREATE TABLE `chuyen_des` (
  `id` bigint UNSIGNED NOT NULL,
  `ma_so` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_chuyen_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoi_luong` decimal(8,2) NOT NULL,
  `doi_tuong_dao_tao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `muc_tieu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `noi_dung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `trang_thai_tai_lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bai_giang_path` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chuyen_des`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chuyen_de_giang_vien`
--

CREATE TABLE `chuyen_de_giang_vien` (
  `id` bigint UNSIGNED NOT NULL,
  `chuyen_de_id` bigint UNSIGNED NOT NULL,
  `giang_vien_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chuyen_de_giang_vien`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dang_kies`
--

CREATE TABLE `dang_kies` (
  `id` bigint UNSIGNED NOT NULL,
  `hoc_vien_id` bigint UNSIGNED NOT NULL,
  `khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `dang_kies`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dia_diem_dao_taos`
--

CREATE TABLE `dia_diem_dao_taos` (
  `id` bigint UNSIGNED NOT NULL,
  `ma_phong` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_phong` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hv_toi_da` int UNSIGNED NOT NULL DEFAULT '0',
  `co_so_vat_chat` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `dia_diem_dao_taos`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `diem_danhs`
--

CREATE TABLE `diem_danhs` (
  `id` bigint UNSIGNED NOT NULL,
  `dang_ky_id` bigint UNSIGNED NOT NULL,
  `lich_hoc_id` bigint UNSIGNED NOT NULL,
  `trang_thai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Có mặt',
  `ly_do_vang` text COLLATE utf8mb4_unicode_ci,
  `diem_buoi_hoc` decimal(5,2) DEFAULT NULL,
  `so_gio_hoc` decimal(8,2) DEFAULT NULL,
  `danh_gia_ky_luat` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `diem_danhs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_vis`
--

CREATE TABLE `don_vis` (
  `id` bigint UNSIGNED NOT NULL,
  `ma_don_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_hien_thi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phong_bo_phan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cong_ty_ban_nvqt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thaco_tdtv` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `noi_lam_viec_chi_tiet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_vis`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_vi_phap_nhans`
--

CREATE TABLE `don_vi_phap_nhans` (
  `ma_so_thue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_don_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia_chi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ghi_chu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_vi_phap_nhans`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `email_accounts`
--

CREATE TABLE `email_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL DEFAULT '587',
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `encryption_tls` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `email_accounts`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `khoa_hoc_id` bigint UNSIGNED DEFAULT NULL,
  `recipient_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_account_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `email_logs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `ten_mau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai_thong_bao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tieu_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `noi_dung` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `email_templates`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exports`
--

CREATE TABLE `exports` (
  `id` bigint UNSIGNED NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `total_rows` int UNSIGNED NOT NULL,
  `successful_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `exports`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_import_rows`
--

CREATE TABLE `failed_import_rows` (
  `id` bigint UNSIGNED NOT NULL,
  `data` json NOT NULL,
  `import_id` bigint UNSIGNED NOT NULL,
  `validation_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giang_viens`
--

CREATE TABLE `giang_viens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ma_so` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ho_ten` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dien_thoai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hinh_anh_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gioi_tinh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nam_sinh` date DEFAULT NULL,
  `don_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_khau_noi_lam_viec` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trinh_do` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chuyen_mon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `so_nam_kinh_nghiem` int DEFAULT NULL,
  `tom_tat_kinh_nghiem` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tinh_trang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang giảng dạy',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `giang_viens`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoc_viens`
--

CREATE TABLE `hoc_viens` (
  `id` bigint UNSIGNED NOT NULL,
  `msnv` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_ten` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gioi_tinh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nam_sinh` date DEFAULT NULL,
  `ngay_vao` date DEFAULT NULL,
  `chuc_vu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `don_vi_id` bigint UNSIGNED DEFAULT NULL,
  `don_vi_phap_nhan_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sdt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tinh_trang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hinh_anh_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoc_viens`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoc_vien_hoan_thanhs`
--

CREATE TABLE `hoc_vien_hoan_thanhs` (
  `id` bigint UNSIGNED NOT NULL,
  `hoc_vien_id` bigint UNSIGNED NOT NULL,
  `khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `ket_qua_khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `ngay_hoan_thanh` date DEFAULT NULL,
  `chung_chi_da_cap` tinyint(1) NOT NULL DEFAULT '0',
  `ghi_chu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `da_duyet` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `chi_phi_dao_tao` decimal(15,2) DEFAULT NULL,
  `chung_chi_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chung_chi_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chung_chi_tap_tin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `so_chung_nhan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_duyet` timestamp NULL DEFAULT NULL,
  `chung_chi_het_han` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoc_vien_hoan_thanhs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoc_vien_khong_hoan_thanhs`
--

CREATE TABLE `hoc_vien_khong_hoan_thanhs` (
  `id` bigint UNSIGNED NOT NULL,
  `hoc_vien_id` bigint UNSIGNED NOT NULL,
  `khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `ket_qua_khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `ly_do_khong_hoan_thanh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `co_the_ghi_danh_lai` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoc_vien_khong_hoan_thanhs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `imports`
--

CREATE TABLE `imports` (
  `id` bigint UNSIGNED NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `total_rows` int UNSIGNED NOT NULL,
  `successful_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `jobs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_chuyen_des`
--

CREATE TABLE `ket_qua_chuyen_des` (
  `id` bigint UNSIGNED NOT NULL,
  `ket_qua_khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `chuyen_de_id` bigint UNSIGNED DEFAULT NULL,
  `ten_chuyen_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lich_hoc_id` bigint UNSIGNED DEFAULT NULL,
  `diem` decimal(5,2) DEFAULT NULL,
  `trang_thai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ly_do_vang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_khoa_hocs`
--

CREATE TABLE `ket_qua_khoa_hocs` (
  `id` bigint UNSIGNED NOT NULL,
  `dang_ky_id` bigint UNSIGNED NOT NULL,
  `tong_so_gio_ke_hoach` decimal(6,2) DEFAULT NULL,
  `tong_so_gio_thuc_te` decimal(6,2) DEFAULT NULL,
  `diem_trung_binh` decimal(5,2) DEFAULT NULL,
  `tong_gio_hoc` decimal(6,2) DEFAULT NULL,
  `ket_qua_goi_y` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `danh_gia_ren_luyen` text COLLATE utf8mb4_unicode_ci,
  `co_mat` tinyint(1) NOT NULL DEFAULT '1',
  `ly_do_vang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diem` decimal(4,2) DEFAULT NULL,
  `diem_tong_khoa` decimal(10,2) DEFAULT NULL,
  `ket_qua` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `da_chuyen_duyet` tinyint(1) NOT NULL DEFAULT '0',
  `needs_review` tinyint(1) NOT NULL DEFAULT '0',
  `can_hoc_lai` tinyint(1) NOT NULL DEFAULT '0',
  `hoc_phi` decimal(15,2) DEFAULT NULL,
  `nguoi_nhap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_nhap` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ket_qua_khoa_hocs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoa_hocs`
--

CREATE TABLE `khoa_hocs` (
  `id` bigint UNSIGNED NOT NULL,
  `chuong_trinh_id` bigint UNSIGNED NOT NULL,
  `ma_khoa_hoc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_khoa_hoc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nam` year NOT NULL,
  `trang_thai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Soạn thảo',
  `yeu_cau_phan_tram_gio` smallint UNSIGNED DEFAULT NULL,
  `yeu_cau_diem_tb` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tam_hoan` tinyint(1) NOT NULL DEFAULT '0',
  `da_chuyen_ket_qua` tinyint(1) NOT NULL DEFAULT '0',
  `thoi_gian_chuyen_ket_qua` timestamp NULL DEFAULT NULL,
  `nguoi_chuyen_ket_qua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ly_do_tam_hoan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khoa_hocs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoa_hoc_edits`
--

CREATE TABLE `khoa_hoc_edits` (
  `id` bigint UNSIGNED NOT NULL,
  `khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_hocs`
--

CREATE TABLE `lich_hocs` (
  `id` bigint UNSIGNED NOT NULL,
  `khoa_hoc_id` bigint UNSIGNED NOT NULL,
  `chuyen_de_id` bigint UNSIGNED DEFAULT NULL,
  `giang_vien_id` bigint UNSIGNED DEFAULT NULL,
  `dia_diem_id` bigint UNSIGNED DEFAULT NULL,
  `ngay_hoc` date NOT NULL,
  `buoi` int UNSIGNED DEFAULT NULL COMMENT 'Số buổi trong khóa/phiên',
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `dia_diem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `so_bai_kiem_tra` int UNSIGNED NOT NULL DEFAULT '0',
  `so_gio_giang` decimal(4,1) DEFAULT NULL,
  `tuan` int DEFAULT NULL,
  `thang` int DEFAULT NULL,
  `nam` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_hocs`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `model_has_roles`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `permissions`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quy_tac_ma_khoas`
--

CREATE TABLE `quy_tac_ma_khoas` (
  `id` bigint UNSIGNED NOT NULL,
  `loai_hinh_dao_tao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tien_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mau_so` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quy_tac_ma_khoas`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `role_has_permissions`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tuy_chon_ket_quas`
--

CREATE TABLE `tuy_chon_ket_quas` (
  `id` bigint UNSIGNED NOT NULL,
  `loai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gia_tri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tuy_chon_ket_quas`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `chuong_trinhs`
--
ALTER TABLE `chuong_trinhs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chuong_trinhs_ma_chuong_trinh_unique` (`ma_chuong_trinh`);

--
-- Chỉ mục cho bảng `chuong_trinh_chuyen_de`
--
ALTER TABLE `chuong_trinh_chuyen_de`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chuong_trinh_chuyen_de_chuong_trinh_id_foreign` (`chuong_trinh_id`),
  ADD KEY `chuong_trinh_chuyen_de_chuyen_de_id_foreign` (`chuyen_de_id`);

--
-- Chỉ mục cho bảng `chuyen_des`
--
ALTER TABLE `chuyen_des`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chuyen_des_ma_so_unique` (`ma_so`);

--
-- Chỉ mục cho bảng `chuyen_de_giang_vien`
--
ALTER TABLE `chuyen_de_giang_vien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chuyen_de_giang_vien_chuyen_de_id_foreign` (`chuyen_de_id`),
  ADD KEY `chuyen_de_giang_vien_giang_vien_id_foreign` (`giang_vien_id`);

--
-- Chỉ mục cho bảng `dang_kies`
--
ALTER TABLE `dang_kies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dang_kies_hoc_vien_id_khoa_hoc_id_unique` (`hoc_vien_id`,`khoa_hoc_id`),
  ADD KEY `dang_kies_khoa_hoc_id_foreign` (`khoa_hoc_id`);

--
-- Chỉ mục cho bảng `dia_diem_dao_taos`
--
ALTER TABLE `dia_diem_dao_taos`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `diem_danhs`
--
ALTER TABLE `diem_danhs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `diem_danhs_dang_ky_id_lich_hoc_id_unique` (`dang_ky_id`,`lich_hoc_id`),
  ADD KEY `diem_danhs_lich_hoc_id_foreign` (`lich_hoc_id`);

--
-- Chỉ mục cho bảng `don_vis`
--
ALTER TABLE `don_vis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `don_vis_ma_don_vi_unique` (`ma_don_vi`);

--
-- Chỉ mục cho bảng `don_vi_phap_nhans`
--
ALTER TABLE `don_vi_phap_nhans`
  ADD PRIMARY KEY (`ma_so_thue`);

--
-- Chỉ mục cho bảng `email_accounts`
--
ALTER TABLE `email_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_accounts_email_unique` (`email`);

--
-- Chỉ mục cho bảng `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_logs_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  ADD KEY `email_logs_email_account_id_foreign` (`email_account_id`);

--
-- Chỉ mục cho bảng `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_templates_loai_thong_bao_index` (`loai_thong_bao`);

--
-- Chỉ mục cho bảng `exports`
--
ALTER TABLE `exports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exports_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `failed_import_rows`
--
ALTER TABLE `failed_import_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `failed_import_rows_import_id_foreign` (`import_id`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `giang_viens`
--
ALTER TABLE `giang_viens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `giang_viens_ma_so_unique` (`ma_so`),
  ADD KEY `giang_viens_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `hoc_viens`
--
ALTER TABLE `hoc_viens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hoc_viens_msnv_unique` (`msnv`),
  ADD UNIQUE KEY `hoc_viens_email_unique` (`email`),
  ADD KEY `hoc_viens_don_vi_id_foreign` (`don_vi_id`),
  ADD KEY `hoc_viens_don_vi_phap_nhan_id_foreign` (`don_vi_phap_nhan_id`);

--
-- Chỉ mục cho bảng `hoc_vien_hoan_thanhs`
--
ALTER TABLE `hoc_vien_hoan_thanhs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoc_vien_hoan_thanh_hoc_vien_id_foreign` (`hoc_vien_id`),
  ADD KEY `hoc_vien_hoan_thanh_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  ADD KEY `hoc_vien_hoan_thanh_ket_qua_khoa_hoc_id_foreign` (`ket_qua_khoa_hoc_id`);

--
-- Chỉ mục cho bảng `hoc_vien_khong_hoan_thanhs`
--
ALTER TABLE `hoc_vien_khong_hoan_thanhs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoc_vien_khong_hoan_thanh_hoc_vien_id_foreign` (`hoc_vien_id`),
  ADD KEY `hoc_vien_khong_hoan_thanh_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  ADD KEY `hoc_vien_khong_hoan_thanh_ket_qua_khoa_hoc_id_foreign` (`ket_qua_khoa_hoc_id`);

--
-- Chỉ mục cho bảng `imports`
--
ALTER TABLE `imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imports_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `ket_qua_chuyen_des`
--
ALTER TABLE `ket_qua_chuyen_des`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ket_qua_chuyen_des_ket_qua_khoa_hoc_id_foreign` (`ket_qua_khoa_hoc_id`),
  ADD KEY `ket_qua_chuyen_des_chuyen_de_id_foreign` (`chuyen_de_id`),
  ADD KEY `ket_qua_chuyen_des_lich_hoc_id_foreign` (`lich_hoc_id`);

--
-- Chỉ mục cho bảng `ket_qua_khoa_hocs`
--
ALTER TABLE `ket_qua_khoa_hocs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ket_qua_khoa_hocs_dang_ky_id_foreign` (`dang_ky_id`);

--
-- Chỉ mục cho bảng `khoa_hocs`
--
ALTER TABLE `khoa_hocs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `khoa_hocs_chuong_trinh_id_foreign` (`chuong_trinh_id`);

--
-- Chỉ mục cho bảng `khoa_hoc_edits`
--
ALTER TABLE `khoa_hoc_edits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `khoa_hoc_edits_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  ADD KEY `khoa_hoc_edits_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `lich_hocs`
--
ALTER TABLE `lich_hocs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lich_hocs_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  ADD KEY `lich_hocs_giang_vien_id_foreign` (`giang_vien_id`),
  ADD KEY `lich_hocs_chuyen_de_id_foreign` (`chuyen_de_id`),
  ADD KEY `idx_lich_hoc_thoi_gian` (`ngay_hoc`,`gio_bat_dau`,`gio_ket_thuc`),
  ADD KEY `idx_lich_hoc_gv` (`giang_vien_id`,`ngay_hoc`),
  ADD KEY `idx_lich_hoc_dia_diem` (`dia_diem`,`ngay_hoc`),
  ADD KEY `idx_lich_hoc_khoa` (`khoa_hoc_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Chỉ mục cho bảng `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Chỉ mục cho bảng `quy_tac_ma_khoas`
--
ALTER TABLE `quy_tac_ma_khoas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quy_tac_ma_khoas_loai_hinh_dao_tao_unique` (`loai_hinh_dao_tao`),
  ADD UNIQUE KEY `quy_tac_ma_khoas_tien_to_unique` (`tien_to`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Chỉ mục cho bảng `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `tuy_chon_ket_quas`
--
ALTER TABLE `tuy_chon_ket_quas`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chuong_trinhs`
--
ALTER TABLE `chuong_trinhs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `chuong_trinh_chuyen_de`
--
ALTER TABLE `chuong_trinh_chuyen_de`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `chuyen_des`
--
ALTER TABLE `chuyen_des`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `chuyen_de_giang_vien`
--
ALTER TABLE `chuyen_de_giang_vien`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `dang_kies`
--
ALTER TABLE `dang_kies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT cho bảng `dia_diem_dao_taos`
--
ALTER TABLE `dia_diem_dao_taos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `diem_danhs`
--
ALTER TABLE `diem_danhs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT cho bảng `don_vis`
--
ALTER TABLE `don_vis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT cho bảng `email_accounts`
--
ALTER TABLE `email_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT cho bảng `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `exports`
--
ALTER TABLE `exports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `failed_import_rows`
--
ALTER TABLE `failed_import_rows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `giang_viens`
--
ALTER TABLE `giang_viens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `hoc_viens`
--
ALTER TABLE `hoc_viens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=453;

--
-- AUTO_INCREMENT cho bảng `hoc_vien_hoan_thanhs`
--
ALTER TABLE `hoc_vien_hoan_thanhs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `hoc_vien_khong_hoan_thanhs`
--
ALTER TABLE `hoc_vien_khong_hoan_thanhs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `imports`
--
ALTER TABLE `imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `ket_qua_chuyen_des`
--
ALTER TABLE `ket_qua_chuyen_des`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ket_qua_khoa_hocs`
--
ALTER TABLE `ket_qua_khoa_hocs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `khoa_hocs`
--
ALTER TABLE `khoa_hocs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT cho bảng `khoa_hoc_edits`
--
ALTER TABLE `khoa_hoc_edits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_hocs`
--
ALTER TABLE `lich_hocs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT cho bảng `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=236;

--
-- AUTO_INCREMENT cho bảng `quy_tac_ma_khoas`
--
ALTER TABLE `quy_tac_ma_khoas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `tuy_chon_ket_quas`
--
ALTER TABLE `tuy_chon_ket_quas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chuong_trinh_chuyen_de`
--
ALTER TABLE `chuong_trinh_chuyen_de`
  ADD CONSTRAINT `chuong_trinh_chuyen_de_chuong_trinh_id_foreign` FOREIGN KEY (`chuong_trinh_id`) REFERENCES `chuong_trinhs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chuong_trinh_chuyen_de_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chuyen_de_giang_vien`
--
ALTER TABLE `chuyen_de_giang_vien`
  ADD CONSTRAINT `chuyen_de_giang_vien_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chuyen_de_giang_vien_giang_vien_id_foreign` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_viens` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `dang_kies`
--
ALTER TABLE `dang_kies`
  ADD CONSTRAINT `dang_kies_hoc_vien_id_foreign` FOREIGN KEY (`hoc_vien_id`) REFERENCES `hoc_viens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dang_kies_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `diem_danhs`
--
ALTER TABLE `diem_danhs`
  ADD CONSTRAINT `diem_danhs_dang_ky_id_foreign` FOREIGN KEY (`dang_ky_id`) REFERENCES `dang_kies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diem_danhs_lich_hoc_id_foreign` FOREIGN KEY (`lich_hoc_id`) REFERENCES `lich_hocs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_email_account_id_foreign` FOREIGN KEY (`email_account_id`) REFERENCES `email_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `email_logs_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `exports`
--
ALTER TABLE `exports`
  ADD CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `failed_import_rows`
--
ALTER TABLE `failed_import_rows`
  ADD CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `giang_viens`
--
ALTER TABLE `giang_viens`
  ADD CONSTRAINT `giang_viens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `hoc_viens`
--
ALTER TABLE `hoc_viens`
  ADD CONSTRAINT `hoc_viens_don_vi_id_foreign` FOREIGN KEY (`don_vi_id`) REFERENCES `don_vis` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hoc_viens_don_vi_phap_nhan_id_foreign` FOREIGN KEY (`don_vi_phap_nhan_id`) REFERENCES `don_vi_phap_nhans` (`ma_so_thue`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `hoc_vien_hoan_thanhs`
--
ALTER TABLE `hoc_vien_hoan_thanhs`
  ADD CONSTRAINT `hoc_vien_hoan_thanh_hoc_vien_id_foreign` FOREIGN KEY (`hoc_vien_id`) REFERENCES `hoc_viens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hoc_vien_hoan_thanh_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hoc_vien_khong_hoan_thanhs`
--
ALTER TABLE `hoc_vien_khong_hoan_thanhs`
  ADD CONSTRAINT `hoc_vien_khong_hoan_thanh_hoc_vien_id_foreign` FOREIGN KEY (`hoc_vien_id`) REFERENCES `hoc_viens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hoc_vien_khong_hoan_thanh_ket_qua_khoa_hoc_id_foreign` FOREIGN KEY (`ket_qua_khoa_hoc_id`) REFERENCES `ket_qua_khoa_hocs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hoc_vien_khong_hoan_thanh_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ket_qua_chuyen_des`
--
ALTER TABLE `ket_qua_chuyen_des`
  ADD CONSTRAINT `ket_qua_chuyen_des_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ket_qua_chuyen_des_ket_qua_khoa_hoc_id_foreign` FOREIGN KEY (`ket_qua_khoa_hoc_id`) REFERENCES `ket_qua_khoa_hocs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ket_qua_chuyen_des_lich_hoc_id_foreign` FOREIGN KEY (`lich_hoc_id`) REFERENCES `lich_hocs` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `ket_qua_khoa_hocs`
--
ALTER TABLE `ket_qua_khoa_hocs`
  ADD CONSTRAINT `ket_qua_khoa_hocs_dang_ky_id_foreign` FOREIGN KEY (`dang_ky_id`) REFERENCES `dang_kies` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `khoa_hocs`
--
ALTER TABLE `khoa_hocs`
  ADD CONSTRAINT `khoa_hocs_chuong_trinh_id_foreign` FOREIGN KEY (`chuong_trinh_id`) REFERENCES `chuong_trinhs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `khoa_hoc_edits`
--
ALTER TABLE `khoa_hoc_edits`
  ADD CONSTRAINT `khoa_hoc_edits_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `khoa_hoc_edits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lich_hocs`
--
ALTER TABLE `lich_hocs`
  ADD CONSTRAINT `lich_hocs_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lich_hocs_giang_vien_id_foreign` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_viens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lich_hocs_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

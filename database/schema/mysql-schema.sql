/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chuong_trinh_chuyen_de`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chuong_trinh_chuyen_de` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chuong_trinh_id` bigint unsigned NOT NULL,
  `chuyen_de_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chuong_trinh_chuyen_de_chuong_trinh_id_foreign` (`chuong_trinh_id`),
  KEY `chuong_trinh_chuyen_de_chuyen_de_id_foreign` (`chuyen_de_id`),
  CONSTRAINT `chuong_trinh_chuyen_de_chuong_trinh_id_foreign` FOREIGN KEY (`chuong_trinh_id`) REFERENCES `chuong_trinhs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chuong_trinh_chuyen_de_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chuong_trinhs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chuong_trinhs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ma_chuong_trinh` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_chuong_trinh` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoi_luong` decimal(8,2) NOT NULL DEFAULT '0.00',
  `muc_tieu_dao_tao` text COLLATE utf8mb4_unicode_ci,
  `loai_hinh_dao_tao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tinh_trang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang áp dụng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chuong_trinhs_ma_chuong_trinh_unique` (`ma_chuong_trinh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chuyen_de_giang_vien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chuyen_de_giang_vien` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chuyen_de_id` bigint unsigned NOT NULL,
  `giang_vien_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `chuyen_de_giang_vien_chuyen_de_id_foreign` (`chuyen_de_id`),
  KEY `chuyen_de_giang_vien_giang_vien_id_foreign` (`giang_vien_id`),
  CONSTRAINT `chuyen_de_giang_vien_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chuyen_de_giang_vien_giang_vien_id_foreign` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_viens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chuyen_des`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chuyen_des` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ma_so` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_chuyen_de` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoi_luong` decimal(8,2) NOT NULL,
  `doi_tuong_dao_tao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `muc_tieu` text COLLATE utf8mb4_unicode_ci,
  `noi_dung` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `trang_thai_tai_lieu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bai_giang_path` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chuyen_des_ma_so_unique` (`ma_so`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dang_kies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dang_kies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoc_vien_id` bigint unsigned NOT NULL,
  `khoa_hoc_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dang_kies_hoc_vien_id_khoa_hoc_id_unique` (`hoc_vien_id`,`khoa_hoc_id`),
  KEY `dang_kies_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  CONSTRAINT `dang_kies_hoc_vien_id_foreign` FOREIGN KEY (`hoc_vien_id`) REFERENCES `hoc_viens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dang_kies_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diem_danh_buoi_hocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diem_danh_buoi_hocs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dang_ky_id` bigint unsigned NOT NULL,
  `lich_hoc_id` bigint unsigned NOT NULL,
  `trang_thai` enum('co_mat','vang_phep','vang_khong_phep') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'co_mat',
  `ly_do_vang` text COLLATE utf8mb4_unicode_ci,
  `diem_buoi_hoc` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `diem_danh_buoi_hocs_dang_ky_id_lich_hoc_id_unique` (`dang_ky_id`,`lich_hoc_id`),
  KEY `diem_danh_buoi_hocs_lich_hoc_id_foreign` (`lich_hoc_id`),
  CONSTRAINT `diem_danh_buoi_hocs_dang_ky_id_foreign` FOREIGN KEY (`dang_ky_id`) REFERENCES `dang_kies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diem_danh_buoi_hocs_lich_hoc_id_foreign` FOREIGN KEY (`lich_hoc_id`) REFERENCES `lich_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diem_danhs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diem_danhs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dang_ky_id` bigint unsigned NOT NULL,
  `lich_hoc_id` bigint unsigned NOT NULL,
  `trang_thai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ly_do_vang` text COLLATE utf8mb4_unicode_ci,
  `diem_buoi_hoc` decimal(4,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `diem_danhs_dang_ky_id_foreign` (`dang_ky_id`),
  KEY `diem_danhs_lich_hoc_id_foreign` (`lich_hoc_id`),
  CONSTRAINT `diem_danhs_dang_ky_id_foreign` FOREIGN KEY (`dang_ky_id`) REFERENCES `dang_kies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diem_danhs_lich_hoc_id_foreign` FOREIGN KEY (`lich_hoc_id`) REFERENCES `lich_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `don_vi_phap_nhans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `don_vi_phap_nhans` (
  `ma_so_thue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_don_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia_chi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ghi_chu` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ma_so_thue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `don_vis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `don_vis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ma_don_vi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_hien_thi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phong_bo_phan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cong_ty_ban_nvqt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thaco_tdtv` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `noi_lam_viec_chi_tiet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `don_vis_ma_don_vi_unique` (`ma_don_vi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL DEFAULT '587',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `encryption_tls` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_accounts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `khoa_hoc_id` bigint unsigned DEFAULT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_account_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_logs_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  KEY `email_logs_email_account_id_foreign` (`email_account_id`),
  CONSTRAINT `email_logs_email_account_id_foreign` FOREIGN KEY (`email_account_id`) REFERENCES `email_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `email_logs_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ten_mau` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai_thong_bao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tieu_de` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `noi_dung` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_templates_loai_thong_bao_index` (`loai_thong_bao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `data` json NOT NULL,
  `import_id` bigint unsigned NOT NULL,
  `validation_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `giang_viens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giang_viens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `ma_so` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ho_ten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hinh_anh_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gioi_tinh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nam_sinh` date DEFAULT NULL,
  `don_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_khau_noi_lam_viec` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trinh_do` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chuyen_mon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `so_nam_kinh_nghiem` int DEFAULT NULL,
  `tom_tat_kinh_nghiem` text COLLATE utf8mb4_unicode_ci,
  `tinh_trang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang giảng dạy',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `giang_viens_ma_so_unique` (`ma_so`),
  KEY `giang_viens_user_id_foreign` (`user_id`),
  CONSTRAINT `giang_viens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hoc_vien_hoan_thanh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoc_vien_hoan_thanh` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoc_vien_id` bigint unsigned NOT NULL,
  `khoa_hoc_id` bigint unsigned NOT NULL,
  `ket_qua_khoa_hoc_id` bigint unsigned NOT NULL,
  `ngay_hoan_thanh` date DEFAULT NULL,
  `chung_chi_da_cap` tinyint(1) NOT NULL DEFAULT '0',
  `ghi_chu` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hoc_vien_hoan_thanh_hoc_vien_id_foreign` (`hoc_vien_id`),
  KEY `hoc_vien_hoan_thanh_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  KEY `hoc_vien_hoan_thanh_ket_qua_khoa_hoc_id_foreign` (`ket_qua_khoa_hoc_id`),
  CONSTRAINT `hoc_vien_hoan_thanh_hoc_vien_id_foreign` FOREIGN KEY (`hoc_vien_id`) REFERENCES `hoc_viens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hoc_vien_hoan_thanh_ket_qua_khoa_hoc_id_foreign` FOREIGN KEY (`ket_qua_khoa_hoc_id`) REFERENCES `ket_qua_khoa_hocs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hoc_vien_hoan_thanh_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hoc_vien_khong_hoan_thanh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoc_vien_khong_hoan_thanh` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoc_vien_id` bigint unsigned NOT NULL,
  `khoa_hoc_id` bigint unsigned NOT NULL,
  `ket_qua_khoa_hoc_id` bigint unsigned NOT NULL,
  `ly_do_khong_hoan_thanh` text COLLATE utf8mb4_unicode_ci,
  `co_the_ghi_danh_lai` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hoc_vien_khong_hoan_thanh_hoc_vien_id_foreign` (`hoc_vien_id`),
  KEY `hoc_vien_khong_hoan_thanh_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  KEY `hoc_vien_khong_hoan_thanh_ket_qua_khoa_hoc_id_foreign` (`ket_qua_khoa_hoc_id`),
  CONSTRAINT `hoc_vien_khong_hoan_thanh_hoc_vien_id_foreign` FOREIGN KEY (`hoc_vien_id`) REFERENCES `hoc_viens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hoc_vien_khong_hoan_thanh_ket_qua_khoa_hoc_id_foreign` FOREIGN KEY (`ket_qua_khoa_hoc_id`) REFERENCES `ket_qua_khoa_hocs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hoc_vien_khong_hoan_thanh_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hoc_viens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoc_viens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msnv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_ten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gioi_tinh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nam_sinh` date DEFAULT NULL,
  `ngay_vao` date DEFAULT NULL,
  `chuc_vu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `don_vi_id` bigint unsigned DEFAULT NULL,
  `don_vi_phap_nhan_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sdt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tinh_trang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hinh_anh_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hoc_viens_msnv_unique` (`msnv`),
  UNIQUE KEY `hoc_viens_email_unique` (`email`),
  KEY `hoc_viens_don_vi_id_foreign` (`don_vi_id`),
  KEY `hoc_viens_don_vi_phap_nhan_id_foreign` (`don_vi_phap_nhan_id`),
  CONSTRAINT `hoc_viens_don_vi_id_foreign` FOREIGN KEY (`don_vi_id`) REFERENCES `don_vis` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hoc_viens_don_vi_phap_nhan_id_foreign` FOREIGN KEY (`don_vi_phap_nhan_id`) REFERENCES `don_vi_phap_nhans` (`ma_so_thue`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ket_qua_chuyen_des`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ket_qua_chuyen_des` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ket_qua_khoa_hoc_id` bigint unsigned NOT NULL,
  `chuyen_de_id` bigint unsigned DEFAULT NULL,
  `ten_chuyen_de` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lich_hoc_id` bigint unsigned DEFAULT NULL,
  `diem` decimal(5,2) DEFAULT NULL,
  `trang_thai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ly_do_vang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ket_qua_chuyen_des_ket_qua_khoa_hoc_id_foreign` (`ket_qua_khoa_hoc_id`),
  KEY `ket_qua_chuyen_des_chuyen_de_id_foreign` (`chuyen_de_id`),
  KEY `ket_qua_chuyen_des_lich_hoc_id_foreign` (`lich_hoc_id`),
  CONSTRAINT `ket_qua_chuyen_des_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ket_qua_chuyen_des_ket_qua_khoa_hoc_id_foreign` FOREIGN KEY (`ket_qua_khoa_hoc_id`) REFERENCES `ket_qua_khoa_hocs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ket_qua_chuyen_des_lich_hoc_id_foreign` FOREIGN KEY (`lich_hoc_id`) REFERENCES `lich_hocs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ket_qua_khoa_hocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ket_qua_khoa_hocs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dang_ky_id` bigint unsigned NOT NULL,
  `co_mat` tinyint(1) NOT NULL DEFAULT '1',
  `ly_do_vang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diem` decimal(4,2) DEFAULT NULL,
  `ket_qua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_hoc_lai` tinyint(1) NOT NULL DEFAULT '0',
  `hoc_phi` decimal(15,2) DEFAULT NULL,
  `nguoi_nhap` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_nhap` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ket_qua_khoa_hocs_dang_ky_id_foreign` (`dang_ky_id`),
  CONSTRAINT `ket_qua_khoa_hocs_dang_ky_id_foreign` FOREIGN KEY (`dang_ky_id`) REFERENCES `dang_kies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `khoa_hoc_edits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `khoa_hoc_edits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `khoa_hoc_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `khoa_hoc_edits_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  KEY `khoa_hoc_edits_user_id_foreign` (`user_id`),
  CONSTRAINT `khoa_hoc_edits_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `khoa_hoc_edits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `khoa_hocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `khoa_hocs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chuong_trinh_id` bigint unsigned NOT NULL,
  `ma_khoa_hoc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nam` year NOT NULL,
  `trang_thai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Soạn thảo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `khoa_hocs_chuong_trinh_id_foreign` (`chuong_trinh_id`),
  CONSTRAINT `khoa_hocs_chuong_trinh_id_foreign` FOREIGN KEY (`chuong_trinh_id`) REFERENCES `chuong_trinhs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lich_hocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lich_hocs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `khoa_hoc_id` bigint unsigned NOT NULL,
  `chuyen_de_id` bigint unsigned DEFAULT NULL,
  `giang_vien_id` bigint unsigned DEFAULT NULL,
  `ngay_hoc` date NOT NULL,
  `buoi` int unsigned DEFAULT NULL COMMENT 'Số buổi trong khóa/phiên',
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `dia_diem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tuan` int DEFAULT NULL,
  `thang` int DEFAULT NULL,
  `nam` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lich_hocs_khoa_hoc_id_foreign` (`khoa_hoc_id`),
  KEY `lich_hocs_giang_vien_id_foreign` (`giang_vien_id`),
  KEY `lich_hocs_chuyen_de_id_foreign` (`chuyen_de_id`),
  CONSTRAINT `lich_hocs_chuyen_de_id_foreign` FOREIGN KEY (`chuyen_de_id`) REFERENCES `chuyen_des` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lich_hocs_giang_vien_id_foreign` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_viens` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lich_hocs_khoa_hoc_id_foreign` FOREIGN KEY (`khoa_hoc_id`) REFERENCES `khoa_hocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quy_tac_ma_khoas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quy_tac_ma_khoas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loai_hinh_dao_tao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tien_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mau_so` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quy_tac_ma_khoas_loai_hinh_dao_tao_unique` (`loai_hinh_dao_tao`),
  UNIQUE KEY `quy_tac_ma_khoas_tien_to_unique` (`tien_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tuy_chon_ket_quas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tuy_chon_ket_quas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gia_tri` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_08_27_082135_create_chuyen_des_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_08_28_013239_create_don_vis_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_08_28_020533_create_imports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_08_28_020534_create_exports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_08_28_020535_create_failed_import_rows_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_08_28_025748_create_tuy_chon_ket_quas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_08_28_125719_create_giang_viens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_08_28_125720_create_hoc_viens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_08_28_125721_create_khoa_hocs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_08_28_125722_create_lich_hocs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_08_28_125725_create_dang_kies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_08_28_125726_create_ket_qua_khoa_hocs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_08_28_125727_create_diem_danhs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_08_28_131618_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_08_28_163938_add_diem_buoi_hoc_to_diem_danhs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_08_29_013041_rename_columns_in_don_vis_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_08_29_013300_add_tinh_trang_to_hoc_viens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_29_024119_remove_don_vi_tra_luong_from_don_vis_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_29_040007_create_don_vi_phap_nhans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_29_082615_add_don_vi_phap_nhan_id_to_hoc_viens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_29_083325_add_sdt_to_hoc_viens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_30_213639_create_chuyen_de_giang_vien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_08_31_110028_add_fields_to_chuyen_des_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_08_31_125106_change_bai_giang_path_to_json_in_chuyen_des_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_08_31_134645_create_chuong_trinhs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_08_31_134648_create_chuong_trinh_chuyen_de_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_08_31_142847_create_quy_tac_ma_khoas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_08_31_142937_update_khoa_hocs_table_for_planning',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_09_01_145014_add_planning_fields_to_lich_hocs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_09_02_132921_add_tinh_trang_to_chuong_trinhs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_09_02_132925_add_tinh_trang_to_giang_viens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_09_02_132929_change_thoi_luong_to_decimal_in_chuyen_des_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_09_03_000001_update_ket_qua_khoa_hocs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_09_03_000002_create_ket_qua_chuyen_des_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_09_04_081031_create_email_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_09_04_081314_create_email_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_09_04_142716_fix_khoa_hocs_trang_thai_values',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_09_05_135450_create_email_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_09_06_001000_update_email_logs_add_account_subject_content',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_09_06_143216_add_mau_so_to_quy_tac_ma_khoas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_09_07_000002_add_ten_hien_thi_to_don_vis',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_09_07_000003_make_msnv_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_09_08_000000_add_buoi_to_lich_hocs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_09_08_000001_create_khoa_hoc_edits',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_09_08_072225_create_diem_danh_buoi_hocs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_09_08_072226_create_hoc_vien_hoan_thanh_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_09_08_072226_create_hoc_vien_khong_hoan_thanh_table',1);

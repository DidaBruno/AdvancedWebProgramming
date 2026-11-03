CREATE DATABASE IF NOT EXISTS radovi
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE radovi;

CREATE TABLE IF NOT EXISTS diplomski_radovi (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    naziv_rada VARCHAR(500) NOT NULL,
    tekst_rada MEDIUMTEXT,
    link_rada  VARCHAR(500) NOT NULL,
    oib_tvrtke VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

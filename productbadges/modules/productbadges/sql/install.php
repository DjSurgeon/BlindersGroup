<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-10
 * @related_html none
 * @database productbadge, productbadge_shop, productbadge_lang, productbadge_product
 *
 * Install SQL script for the productbadges module.
 * Creates the necessary database tables for badges, shop associations, languages, and product associations.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

/**
 * ============ TABLE 1: MAIN BADGES ============
 * This table stores visual properties like colors and position.
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadge` (
  `id_productbadge` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `color_bg` VARCHAR(7) NOT NULL,
  `color_text` VARCHAR(7) NOT NULL,
  `position` VARCHAR(16) NOT NULL DEFAULT "top-left",
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_productbadge`),
  INDEX `idx_active` (`active`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

/**
 * ============ TABLE 2: BADGES BY SHOP ============
 * Allows enabling/disabling badges independently for each shop in a multi-shop context.
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadge_shop` (
  `id_productbadge` INT(11) UNSIGNED NOT NULL,
  `id_shop` INT(11) UNSIGNED NOT NULL,
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_productbadge`, `id_shop`),
  INDEX `idx_shop` (`id_shop`),
  CONSTRAINT `FK_pb_shop` FOREIGN KEY (`id_productbadge`) REFERENCES `' . _DB_PREFIX_ . 'productbadge` (`id_productbadge`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

/**
 * ============ TABLE 3: BADGES BY LANGUAGE AND SHOP ============
 * Stores the translated text for each badge per shop and language.
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadge_lang` (
  `id_productbadge` INT(11) UNSIGNED NOT NULL,
  `id_shop` INT(11) UNSIGNED NOT NULL,
  `id_lang` INT(11) UNSIGNED NOT NULL,
  `text` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id_productbadge`, `id_shop`, `id_lang`),
  INDEX `idx_lang` (`id_lang`),
  CONSTRAINT `FK_pb_lang` FOREIGN KEY (`id_productbadge`) REFERENCES `' . _DB_PREFIX_ . 'productbadge` (`id_productbadge`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

/**
 * ============ TABLE 4: BADGE-PRODUCT RELATION ============
 * Pivot table for the many-to-many relationship between badges and products.
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadge_product` (
  `id_productbadge` INT(11) UNSIGNED NOT NULL,
  `id_product` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_productbadge`, `id_product`),
  INDEX `idx_product` (`id_product`),
  CONSTRAINT `FK_pb_prod` FOREIGN KEY (`id_productbadge`) REFERENCES `' . _DB_PREFIX_ . 'productbadge` (`id_productbadge`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Execute all queries
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;

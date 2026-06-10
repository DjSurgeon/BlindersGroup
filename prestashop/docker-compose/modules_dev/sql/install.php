<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-10
 * @related_html none
 * @database productbadges, productbadges_shop, productbadges_lang, productbadges_product
 *
 * Install SQL script for the productbadges module (Development Environment).
 * Creates the necessary database tables for badges, shop associations, languages, and product associations.
 */

$sql = array();

/**
 * ============ TABLE 1: MAIN BADGES ============
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges` (
    `id_productbadge` int(11) NOT NULL AUTO_INCREMENT,
    `bg_color` varchar(32) NOT NULL,
    `text_color` varchar(32) NOT NULL,
    `position` varchar(32) NOT NULL DEFAULT \'top-left\',
    `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_productbadge`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/**
 * ============ TABLE 2: BADGES BY SHOP ============
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_shop` (
    `id_productbadge` int(11) NOT NULL,
    `id_shop` int(11) NOT NULL,
    `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    PRIMARY KEY  (`id_productbadge`, `id_shop`),
    KEY `id_shop` (`id_shop`),
    CONSTRAINT `FK_pb_shop` FOREIGN KEY (`id_productbadge`) REFERENCES `' . _DB_PREFIX_ . 'productbadges` (`id_productbadge`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/**
 * ============ TABLE 3: BADGES BY LANGUAGE AND SHOP ============
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_lang` (
    `id_productbadge` int(11) NOT NULL,
    `id_lang` int(11) NOT NULL,
    `id_shop` int(11) NOT NULL,
    `text` varchar(255) NOT NULL,
    PRIMARY KEY  (`id_productbadge`, `id_shop`, `id_lang`),
    CONSTRAINT `FK_pb_lang` FOREIGN KEY (`id_productbadge`) REFERENCES `' . _DB_PREFIX_ . 'productbadges` (`id_productbadge`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/**
 * ============ TABLE 4: BADGE-PRODUCT RELATION ============
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_product` (
    `id_productbadge` int(11) NOT NULL,
    `id_product` int(10) unsigned NOT NULL,
    PRIMARY KEY  (`id_productbadge`, `id_product`),
    KEY `id_product` (`id_product`),
    CONSTRAINT `FK_pb_prod` FOREIGN KEY (`id_productbadge`) REFERENCES `' . _DB_PREFIX_ . 'productbadges` (`id_productbadge`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        include_once dirname(__FILE__).'/uninstall.php';
        return false;
    }
}

return true;

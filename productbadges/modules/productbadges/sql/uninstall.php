<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-10
 * @related_html none
 * @database productbadge, productbadge_shop, productbadge_lang, productbadge_product
 *
 * Uninstall SQL script for the productbadges module.
 * Drops all module-related tables in the correct order to respect foreign key constraints.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

/**
 * ============ DROP TABLES (in reverse dependency order) ============
 * Removing relationship tables first to avoid foreign key violations.
 */
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadge_product`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadge_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadge_shop`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadge`;';

$success = true;

// Execute all queries
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        $success = false;
    }
}

return $success;

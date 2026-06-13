<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-11
 * @related_html none
 * @database productbadges, productbadges_shop, productbadges_lang, productbadges_product
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_product`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges`;';

$success = true;

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        $success = false;
    }
}

return $success;

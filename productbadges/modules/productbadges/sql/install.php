<?php
/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 * ...
 */

$sql = array();

/*
 * Table structure for table `ps_productbadges`
 * (Añadiremos los CREATE TABLE aquí en la siguiente fase)
 */

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

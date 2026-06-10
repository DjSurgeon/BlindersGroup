<?php
/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 * ...
 */

$sql = array();

/*
 * (Añadiremos los DROP TABLE aquí en la siguiente fase)
 */

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

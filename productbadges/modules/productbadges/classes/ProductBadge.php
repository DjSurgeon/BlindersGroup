<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-11
 * @related_html none
 * @database productbadges, productbadges_shop, productbadges_lang, productbadges_product
 */

/**
 * ObjectModel for ProductBadges.
 * Represents a single badge entity and handles its DB relations.
 * 
 * @package productbadges
 * @category entities
 */
class ProductBadge extends ObjectModel
{
    public $id_productbadge;
    public $bg_color;
    public $text_color;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;
    public $text;

    public static $definition = array(
        'table' => 'productbadges',
        'primary' => 'id_productbadge',
        'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'bg_color'   => array('type' => self::TYPE_STRING, 'validate' => 'isColorHexValue', 'required' => true, 'size' => 32),
            'text_color' => array('type' => self::TYPE_STRING, 'validate' => 'isColorHexValue', 'required' => true, 'size' => 32),
            'position'   => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'active'     => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'date_add'   => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd'   => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'text'       => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 255),
        ),
    );

    public function getProducts()
    {
        // TODO: Implement logic to read from productbadges_product
        return array();
    }

    public function updateProducts($product_ids)
    {
        // TODO: Implement logic to clear old associations and insert new ones
        return true;
    }

    public function removeAllProducts()
    {
        // TODO: Implement logic to delete from productbadges_product
        return true;
    }
}

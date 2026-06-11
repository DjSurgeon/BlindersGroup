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
    /**
     * @var int ID
     * @access public
     */
    public $id_productbadge;

    /**
     * @var string Background Color (Hex)
     * @access public
     */
    public $bg_color;

    /**
     * @var string Text Color (Hex)
     * @access public
     */
    public $text_color;

    /**
     * @var string Position (top-left, top-right)
     * @access public
     */
    public $position;

    /**
     * @var bool Is active
     * @access public
     */
    public $active;

    /**
     * @var string Object creation date
     * @access public
     */
    public $date_add;

    /**
     * @var string Object last modification date
     * @access public
     */
    public $date_upd;

    /**
     * @var string Multi-lang Text
     * @access public
     */
    public $text;

    /**
     * @see ObjectModel::$definition
     * @var array
     * @access public
     */
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

    /**
     * Get all product IDs associated with this badge.
     * 
     * @return array Array of product IDs.
     */
    public function getProducts()
    {
        // TODO: Implement logic to read from productbadges_product
        return array();
    }

    /**
     * Update the products associated with this badge (Many-to-Many).
     * 
     * @param array $product_ids Array of product IDs to associate.
     * @return bool True if update is successful.
     */
    public function updateProducts($product_ids)
    {
        // TODO: Implement logic to clear old associations and insert new ones
        return true;
    }

    /**
     * Clear all product associations for this badge.
     * 
     * @return bool True if removal is successful.
     */
    public function removeAllProducts()
    {
        // TODO: Implement logic to delete from productbadges_product
        return true;
    }
}

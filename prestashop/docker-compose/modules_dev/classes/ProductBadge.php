<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-11
 *
 * ObjectModel for ProductBadges.
 * Represents a single badge entity and handles its DB relations.
 */

class ProductBadge extends ObjectModel
{
    /** @var int ID */
    public $id_productbadge;

    /** @var string Background Color (Hex) */
    public $bg_color;

    /** @var string Text Color (Hex) */
    public $text_color;

    /** @var string Position (top-left, top-right) */
    public $position;

    /** @var bool Is active */
    public $active;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var string Multi-lang Text */
    public $text;

    /**
     * @see ObjectModel::$definition
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

            /* Lang fields */
            'text'       => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 255),
        ),
    );

    /**
     * Get all product IDs associated with this badge
     * @return array Array of product IDs
     */
    public function getProducts()
    {
        // TODO: Implement logic to read from productbadges_product
        return array();
    }

    /**
     * Update the products associated with this badge (Many-to-Many)
     * @param array $product_ids
     * @return bool
     */
    public function updateProducts($product_ids)
    {
        // TODO: Implement logic to clear old associations and insert new ones
        return true;
    }

    /**
     * Clear all product associations for this badge
     * @return bool
     */
    public function removeAllProducts()
    {
        // TODO: Implement logic to delete from productbadges_product
        return true;
    }
}

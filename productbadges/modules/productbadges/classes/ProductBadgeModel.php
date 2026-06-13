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
class ProductBadgeModel extends ObjectModel
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
        'multilang_shop' => true,
        'multishop' => true,
        'fields' => array(
            'bg_color'   => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'text_color' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'position'   => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'active'     => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'date_add'   => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd'   => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'text'       => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
        ),
    );

    public function getProducts()
    {
        if (!$this->id) {
            return array();
        }

        $sql = 'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'productbadges_product` 
                WHERE `id_productbadge` = ' . (int)$this->id;

        $results = Db::getInstance()->executeS($sql);
        if (!$results) {
            return array();
        }

        $product_ids = array();
        foreach ($results as $row) {
            $product_ids[] = (int)$row['id_product'];
        }

        return $product_ids;
    }

    public function updateProducts($product_ids)
    {
        $this->removeAllProducts();

        if (empty($product_ids) || !is_array($product_ids)) {
            return true;
        }

        $insert_data = array();
        foreach ($product_ids as $id_product) {
            $insert_data[] = array(
                'id_productbadge' => (int)$this->id,
                'id_product' => (int)$id_product
            );
        }

        return Db::getInstance()->insert('productbadges_product', $insert_data);
    }

    public function removeAllProducts()
    {
        if (!$this->id) {
            return true;
        }

        return Db::getInstance()->delete(
            'productbadges_product', 
            'id_productbadge = ' . (int)$this->id
        );
    }

    public function delete()
    {
        $this->removeAllProducts();
        return parent::delete();
    }
}

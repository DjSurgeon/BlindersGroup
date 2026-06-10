<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-10
 * @related_html none
 * @database none
 *
 * This file is part of the productbadges module for PrestaShop.
 * It handles the module installation, uninstallation, and core hooks.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Productbadges
 *
 * @package productbadges
 * @category front_office_features
 */
class Productbadges extends Module
{
    /**
     * Productbadges constructor.
     *
     * @access public
     */
    public function __construct()
    {
        $this->name = 'productbadges';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Sergio Jimenez';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Badges');
        $this->description = $this->l('Show custom badges on products.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Install the module and register required hooks.
     *
     * @return bool
     * @access public
     */
    public function install()
    {
        if (!include(dirname(__FILE__).'/sql/install.php')) {
            return false;
        }

        return parent::install() &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayProductFlags');
    }

    /**
     * Uninstall the module and clean up the database.
     *
     * @return bool
     * @access public
     */
    public function uninstall()
    {
        if (!include(dirname(__FILE__).'/sql/uninstall.php')) {
            return false;
        }

        return parent::uninstall();
    }
}

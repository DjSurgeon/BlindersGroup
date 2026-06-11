<?php
/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2024 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @version 1.0.0
 * @author Sergio Jimenez
 * @last_modified 2026-06-11
 * @related_html none
 * @database productbadges, productbadges_shop, productbadges_lang, productbadges_product
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Main class for Product Badges module.
 * 
 * @package productbadges
 * @category front_office_features
 */
class Productbadges extends Module
{
    /**
     * @var string
     * @access public
     */
    public $name;

    /**
     * @var string
     * @access public
     */
    public $tab;

    /**
     * @var string
     * @access public
     */
    public $version;

    /**
     * @var string
     * @access public
     */
    public $author;

    /**
     * @var int
     * @access public
     */
    public $need_instance;

    /**
     * @var bool
     * @access public
     */
    public $bootstrap;

    /**
     * @var string
     * @access public
     */
    public $displayName;

    /**
     * @var string
     * @access public
     */
    public $description;

    /**
     * @var array
     * @access public
     */
    public $ps_versions_compliancy;

    /**
     * Productbadges constructor.
     * Sets module metadata and configuration defaults.
     */
    public function __construct()
    {
        $this->name = 'productbadges';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Sergio Jimenez';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Badges');
        $this->description = $this->l('Show custom badges on products.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Installs the module.
     * Includes SQL installation, tab creation, configuration defaults, and hook registration.
     * 
     * @return bool True if installation is successful, false otherwise.
     */
    public function install()
    {
        if (!include_once dirname(__FILE__).'/sql/install.php') {
            return false;
        }

        return parent::install() &&
            $this->installTab() &&
            $this->installConfiguration() &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayProductFlags');
    }

    /**
     * Uninstalls the module.
     * Includes SQL uninstallation, tab removal, and configuration deletion.
     * 
     * @return bool True if uninstallation is successful, false otherwise.
     */
    public function uninstall()
    {
        if (!include_once dirname(__FILE__).'/sql/uninstall.php') {
            return false;
        }

        return parent::uninstall() &&
            $this->uninstallTab() &&
            $this->uninstallConfiguration();
    }

    /**
     * Installs the Admin Tab for the module.
     * 
     * @return bool True if tab creation is successful, false otherwise.
     */
    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminProductBadges';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Product Badges';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * Uninstalls the Admin Tab for the module.
     * 
     * @return bool True if tab removal is successful, false otherwise.
     */
    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminProductBadges');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    /**
     * Installs default configuration values.
     * 
     * @return bool True if configuration is successful, false otherwise.
     */
    public function installConfiguration()
    {
        return Configuration::updateValue('PRODUCTBADGES_LIVE', 1) &&
            Configuration::updateValue('PRODUCTBADGES_USE_LIST', 1) &&
            Configuration::updateValue('PRODUCTBADGES_USE_PRODUCT', 1) &&
            Configuration::updateValue('PRODUCTBADGES_MAX_ITEMS', 3);
    }

    /**
     * Uninstalls configuration values.
     * 
     * @return bool True if configuration removal is successful, false otherwise.
     */
    public function uninstallConfiguration()
    {
        return Configuration::deleteByName('PRODUCTBADGES_LIVE') &&
            Configuration::deleteByName('PRODUCTBADGES_USE_LIST') &&
            Configuration::deleteByName('PRODUCTBADGES_USE_PRODUCT') &&
            Configuration::deleteByName('PRODUCTBADGES_MAX_ITEMS');
    }
}

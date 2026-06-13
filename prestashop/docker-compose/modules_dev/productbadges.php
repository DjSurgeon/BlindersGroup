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

require_once dirname(__FILE__) . '/classes/ProductBadgeModel.php';

/**
 * Main class for Product Badges module.
 * @package productbadges
 * @category front_office_features
 */
class Productbadges extends Module
{
    public $name;
    public $tab;
    public $version;
    public $author;
    public $need_instance;
    public $bootstrap;
    public $displayName;
    public $description;
    public $ps_versions_compliancy;

    public function __construct()
    {
        $this->name = 'productbadges';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Sergio Jimenez';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('Product Badges');
        $this->description = $this->l('Show custom badges on products.');
    }

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

    public function uninstall()
    {
        if (!include_once dirname(__FILE__).'/sql/uninstall.php') {
            return false;
        }

        return parent::uninstall() &&
            $this->uninstallTab() &&
            $this->uninstallConfiguration();
    }

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

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminProductBadges');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function installConfiguration()
    {
        return Configuration::updateValue('PRODUCTBADGES_LIVE', 1) &&
            Configuration::updateValue('PRODUCTBADGES_USE_LIST', 1) &&
            Configuration::updateValue('PRODUCTBADGES_USE_PRODUCT', 1) &&
            Configuration::updateValue('PRODUCTBADGES_MAX_ITEMS', 3);
    }

    public function uninstallConfiguration()
    {
        return Configuration::deleteByName('PRODUCTBADGES_LIVE') &&
            Configuration::deleteByName('PRODUCTBADGES_USE_LIST') &&
            Configuration::deleteByName('PRODUCTBADGES_USE_PRODUCT') &&
            Configuration::deleteByName('PRODUCTBADGES_MAX_ITEMS');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitProductbadgesModule')) {
            $live = (int) Tools::getValue('PRODUCTBADGES_LIVE');
            $use_list = (int) Tools::getValue('PRODUCTBADGES_USE_LIST');
            $use_product = (int) Tools::getValue('PRODUCTBADGES_USE_PRODUCT');
            $max_items = Tools::getValue('PRODUCTBADGES_MAX_ITEMS');

            if (!Validate::isInt($max_items) || $max_items <= 0) {
                $output .= $this->displayError($this->l('Invalid max items value. It must be a positive integer.'));
            } else {
                Configuration::updateValue('PRODUCTBADGES_LIVE', $live);
                Configuration::updateValue('PRODUCTBADGES_USE_LIST', $use_list);
                Configuration::updateValue('PRODUCTBADGES_USE_PRODUCT', $use_product);
                Configuration::updateValue('PRODUCTBADGES_MAX_ITEMS', (int) $max_items);
                $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
            }
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Global Active'),
                        'name' => 'PRODUCTBADGES_LIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in frontend'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show in product lists'),
                        'name' => 'PRODUCTBADGES_USE_LIST',
                        'is_bool' => true,
                        'desc' => $this->l('Display badges on category and search pages'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show in product page'),
                        'name' => 'PRODUCTBADGES_USE_PRODUCT',
                        'is_bool' => true,
                        'desc' => $this->l('Display badges on the main product page'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Max badges per product'),
                        'name' => 'PRODUCTBADGES_MAX_ITEMS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Maximum number of badges to display on a single product.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->name;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductbadgesModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'PRODUCTBADGES_LIVE' => Tools::getValue('PRODUCTBADGES_LIVE', Configuration::get('PRODUCTBADGES_LIVE')),
            'PRODUCTBADGES_USE_LIST' => Tools::getValue('PRODUCTBADGES_USE_LIST', Configuration::get('PRODUCTBADGES_USE_LIST')),
            'PRODUCTBADGES_USE_PRODUCT' => Tools::getValue('PRODUCTBADGES_USE_PRODUCT', Configuration::get('PRODUCTBADGES_USE_PRODUCT')),
            'PRODUCTBADGES_MAX_ITEMS' => Tools::getValue('PRODUCTBADGES_MAX_ITEMS', Configuration::get('PRODUCTBADGES_MAX_ITEMS')),
        );
    }
}

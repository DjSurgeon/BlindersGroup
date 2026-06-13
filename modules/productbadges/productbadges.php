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
            $this->registerHook('actionProductFlagsModifier') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductUpdate');
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

    public function hookActionProductFlagsModifier($params)
    {
        if (!Configuration::get('PRODUCTBADGES_LIVE')) {
            return;
        }

        $controller_name = Tools::getValue('controller');
        if (!$controller_name && isset($this->context->controller->php_self)) {
            $controller_name = $this->context->controller->php_self;
        }

        $is_product_page = ($controller_name === 'product');

        if ($is_product_page && !Configuration::get('PRODUCTBADGES_USE_PRODUCT')) {
            return;
        }
        if (!$is_product_page && !Configuration::get('PRODUCTBADGES_USE_LIST')) {
            return;
        }

        $id_product = (int) $params['product']['id_product'];
        if (!$id_product) {
            return;
        }

        $max_items = (int) Configuration::get('PRODUCTBADGES_MAX_ITEMS');
        if ($max_items <= 0) {
            $max_items = 999;
        }

        $id_lang = (int) $this->context->language->id;

        $badges = Db::getInstance()->executeS(
            'SELECT a.`id_productbadge`, a.`position`, b.`text`
             FROM `' . _DB_PREFIX_ . 'productbadges` a
             INNER JOIN `' . _DB_PREFIX_ . 'productbadges_product` pp ON (a.`id_productbadge` = pp.`id_productbadge`)
             LEFT JOIN `' . _DB_PREFIX_ . 'productbadges_lang` b ON (a.`id_productbadge` = b.`id_productbadge` AND b.`id_lang` = ' . $id_lang . ')
             WHERE pp.`id_product` = ' . $id_product . ' AND a.`active` = 1
             LIMIT ' . $max_items
        );

        if ($badges) {
            foreach ($badges as $badge) {
                $type = 'productbadge-' . $badge['id_productbadge'];
                if ($badge['position'] == 'top-right') {
                    $type .= ' pb-right';
                }
                
                $params['flags'][$type] = array(
                    'type' => $type,
                    'label' => $badge['text']
                );
            }
        }
    }

    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('PRODUCTBADGES_LIVE')) {
            return;
        }

        $badges = Db::getInstance()->executeS(
            'SELECT `id_productbadge`, `bg_color`, `text_color`, `position` 
             FROM `' . _DB_PREFIX_ . 'productbadges` 
             WHERE `active` = 1'
        );

        if ($badges) {
            $this->context->smarty->assign('productbadges_css', $badges);
            return $this->display(__FILE__, 'views/templates/front/header.tpl');
        }

        return '';
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int) $params['id_product'];
        if (!$id_product) {
            return '';
        }

        // Obtener todos los badges activos
        $badges = Db::getInstance()->executeS(
            'SELECT a.*, b.`text` 
             FROM `' . _DB_PREFIX_ . 'productbadges` a
             LEFT JOIN `' . _DB_PREFIX_ . 'productbadges_lang` b 
               ON (a.`id_productbadge` = b.`id_productbadge` AND b.`id_lang` = ' . (int) $this->context->language->id . ')
             WHERE a.`active` = 1'
        );

        // Obtener badges asignados a este producto
        $assigned_badges = Db::getInstance()->executeS(
            'SELECT `id_productbadge` 
             FROM `' . _DB_PREFIX_ . 'productbadges_product` 
             WHERE `id_product` = ' . (int) $id_product
        );

        $assigned_ids = array();
        if ($assigned_badges) {
            foreach ($assigned_badges as $ab) {
                $assigned_ids[] = (int) $ab['id_productbadge'];
            }
        }

        $this->context->smarty->assign(array(
            'productbadges' => $badges,
            'assigned_badges' => $assigned_ids,
            'max_items' => (int) Configuration::get('PRODUCTBADGES_MAX_ITEMS')
        ));

        return $this->display(__FILE__, 'views/templates/admin/hook/admin_products_extra.tpl');
    }

    public function hookActionProductUpdate($params)
    {
        $id_product = (int) $params['id_product'];
        if (!$id_product) {
            return;
        }

        // Solo procesar si el formulario contenía el array de productbadges (incluso si está vacío, se envía un array vacío si el checkbox existía en el DOM)
        // En PS 1.7 Symfony, los checkboxes extra a veces llegan en Tools::getValue
        $submitted_badges = Tools::getValue('productbadges');

        if ($submitted_badges !== false) {
            // Limpiar las asociaciones previas
            Db::getInstance()->delete('productbadges_product', 'id_product = ' . (int) $id_product);

            if (is_array($submitted_badges) && !empty($submitted_badges)) {
                $insert_data = array();
                foreach ($submitted_badges as $id_badge) {
                    $insert_data[] = array(
                        'id_productbadge' => (int) $id_badge,
                        'id_product' => (int) $id_product
                    );
                }
                if (!empty($insert_data)) {
                    Db::getInstance()->insert('productbadges_product', $insert_data);
                }
            }
        }
    }
}

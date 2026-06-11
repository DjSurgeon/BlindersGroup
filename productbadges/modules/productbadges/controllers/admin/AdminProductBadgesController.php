<?php
/**
 * @version 1.0.0
 * @author Sergio Jimenez
 */

require_once dirname(__FILE__) . '/../../classes/ProductBadgeModel.php';

class AdminProductBadgesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'productbadges';
        $this->className = 'ProductBadgeModel';
        $this->lang = true;
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->context = Context::getContext();
        $this->identifier = 'id_productbadge';
        
        parent::__construct();
        
        $this->fields_list = array(
            'id_productbadge' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'a!id_productbadge'
            ),
            'text' => array(
                'title' => $this->l('Badge Text'),
                'filter_key' => 'b!text',
            ),
            'bg_color' => array(
                'title' => $this->l('Background Color'),
                'type' => 'color',
            ),
            'text_color' => array(
                'title' => $this->l('Text Color'),
                'type' => 'color',
            ),
            'position' => array(
                'title' => $this->l('Position'),
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ),
        );
    }

    public function renderForm()
    {
        // Load the product IDs for this badge if we are editing
        $product_ids_str = '';
        if ($this->display == 'edit' && $this->object && $this->object->id) {
            $product_ids = $this->object->getProducts();
            $product_ids_str = implode(',', $product_ids);
        } else if (Tools::isSubmit('product_ids')) {
            $product_ids_str = Tools::getValue('product_ids');
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Product Badge'),
                'icon' => 'icon-tag'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Badge Text'),
                    'name' => 'text',
                    'lang' => true,
                    'required' => true,
                    'desc' => $this->l('Text to display on the badge.')
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Background Color'),
                    'name' => 'bg_color',
                    'required' => true,
                    'desc' => $this->l('Background color (e.g. #FF0000).')
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Text Color'),
                    'name' => 'text_color',
                    'required' => true,
                    'desc' => $this->l('Text color (e.g. #FFFFFF).')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Position'),
                    'name' => 'position',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('id' => 'top-left', 'name' => $this->l('Top Left')),
                            array('id' => 'top-right', 'name' => $this->l('Top Right')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product IDs'),
                    'name' => 'product_ids',
                    'desc' => $this->l('Comma separated product IDs to assign this badge (e.g. 1,5,12).')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'required' => true,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $this->fields_value['product_ids'] = $product_ids_str;

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddproductbadges')) {
            // Server-side validation for colors
            $bg_color = Tools::getValue('bg_color');
            $text_color = Tools::getValue('text_color');
            
            if (!Validate::isColor($bg_color) || !preg_match('/^#[0-9a-fA-F]{6}$/', $bg_color)) {
                $this->errors[] = $this->l('Invalid background color format. It must be a valid HEX color (e.g., #FF0000).');
            }
            if (!Validate::isColor($text_color) || !preg_match('/^#[0-9a-fA-F]{6}$/', $text_color)) {
                $this->errors[] = $this->l('Invalid text color format. It must be a valid HEX color (e.g., #FFFFFF).');
            }

            // Validate position
            $position = Tools::getValue('position');
            if (!in_array($position, array('top-left', 'top-right'))) {
                $this->errors[] = $this->l('Invalid position selected.');
            }

            // Validate product IDs
            $product_ids_raw = Tools::getValue('product_ids');
            if (!empty($product_ids_raw)) {
                $ids = explode(',', $product_ids_raw);
                foreach ($ids as $id) {
                    if (!Validate::isUnsignedInt(trim($id))) {
                        $this->errors[] = $this->l('Product IDs must be a comma-separated list of positive integers.');
                        break;
                    }
                }
            }

            if (!empty($this->errors)) {
                $this->display = 'edit';
                return false;
            }
        }

        return parent::postProcess();
    }

    protected function afterAdd($object)
    {
        $this->saveProductAssociations($object);
        return parent::afterAdd($object);
    }

    protected function afterUpdate($object)
    {
        $this->saveProductAssociations($object);
        return parent::afterUpdate($object);
    }

    private function saveProductAssociations($object)
    {
        $product_ids_raw = Tools::getValue('product_ids');
        $valid_ids = array();
        
        if (!empty($product_ids_raw)) {
            $ids = explode(',', $product_ids_raw);
            foreach ($ids as $id) {
                $id = (int)trim($id);
                if ($id > 0) {
                    $valid_ids[] = $id;
                }
            }
        }
        
        // Use the model's method to handle M:M relations
        $object->updateProducts($valid_ids);
    }
}

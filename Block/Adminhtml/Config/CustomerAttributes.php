<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class CustomerAttributes extends AbstractFieldArray
{
    /**
     * @var CustomerAttributes\Mapping
     */
    protected $_customerAttrRenderer;

    /**
     * Returns renderer for country element
     *
     * @return \Magento\Braintree\Block\Adminhtml\Form\Field\Countries
     */
    protected function getCustomerAttrRenderer()
    {
        if (!$this->_customerAttrRenderer) {
            $this->_customerAttrRenderer = $this->getLayout()->createBlock(
                CustomerAttributes\Mapping::class,
                'mapping.renderer',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_customerAttrRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('attributes', [
            'label' => __('Customer Attributes'),
            'renderer' => $this->getCustomerAttrRenderer(),
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare array row
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $attr = $row->getAttributes();
        $options = [];
        if ($attr) {
            $options['option_' . $this->getCustomerAttrRenderer()->calcOptionHash($attr)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}

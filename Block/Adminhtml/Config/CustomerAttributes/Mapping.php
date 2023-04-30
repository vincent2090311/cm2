<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Block\Adminhtml\Config\CustomerAttributes;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Fuutur\CampaignMonitor\Model\Config\CustomerAttributes;

class Mapping extends Select
{
   /**
    * Constructor
    *
    * @param Context $context
    * @param CustomerAttributes $customerAttributes
    * @param array $data
    */
    public function __construct(
        protected Context $context,
        protected CustomerAttributes $customerAttributes,
        protected array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerAttributes = $customerAttributes;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $customerAttrs = $this->customerAttributes->getCustomerCustomFields();
            foreach ($customerAttrs as $code => $label) {
                $this->addOption($code, $label);
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}

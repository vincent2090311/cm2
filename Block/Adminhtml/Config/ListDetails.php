<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class ListDetails extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Fuutur_CampaignMonitor::campaignmonitor/list_details.phtml';

    /**
     * @param Context $context
     * @param CampaignMonitor $api
     * @param array $data
     */
    public function __construct(
        protected Context $context,
        protected CampaignMonitor $api,
        protected array $data = []
    ) {
        parent::__construct($context, $data);
        $this->api = $api;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return subscriber list details
     *
     * @return string
     */
    public function getSelectedListDetails()
    {
        $storeId = 0;
        $data = $this->getRequest()->getParams();
        if (array_key_exists(ScopeInterface::SCOPE_WEBSITE, $data)) {
            $websiteId = $data[ScopeInterface::SCOPE_WEBSITE];
            $website = $this->storeManager->getWebsite($websiteId);
            $storeId = $website->getDefaultGroup()->getDefaultStoreId();
        } elseif (array_key_exists(ScopeInterface::SCOPE_STORE, $data)) {
            $storeId = $data[ScopeInterface::SCOPE_STORE];
        }

        $response = $this->api->getListStats($storeId);
        return $response;
    }
}

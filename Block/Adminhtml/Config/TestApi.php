<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;

class TestApi extends Field
{
    private const TEST_URL = 'campaignmonitor/api/test';

    /**
     * @var string
     */
    protected $_template = 'Fuutur_CampaignMonitor::campaignmonitor/test.phtml';

    /**
     * @param Context $context
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        protected Context $context,
        protected UrlInterface $backendUrl,
        protected array $data = []
    ) {
        parent::__construct($context, $data);
        $this->backendUrl = $backendUrl;
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
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->backendUrl->getUrl(self::TEST_URL, ['_current' => true]);
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(Button::class)
            ->setData(['id' => 'test_api','label' => __('Test Api')]);
        return $button->toHtml();
    }
}

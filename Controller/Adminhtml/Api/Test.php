<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Controller\Adminhtml\Api;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;

class Test extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Fuutur_CampaignMonitor::createsend';

    public const ADMINHTML_SYSTEM_CONFIG_EDIT  = 'adminhtml/system_config/edit';
    public const ERR_API_CALL_ERROR            = 'API Test Error';
    public const LOG_API_CALL_SUCCESSFUL       = 'API Test Successful.';

    /**
     * @param Context $context
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param CampaignMonitor $api
     */
    public function __construct(
        protected Context $context,
        protected State $state,
        protected StoreManagerInterface $storeManager,
        protected CampaignMonitor $api
    ) {
        parent::__construct($context);
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->api = $api;
    }

    /**
     * Performs a test API call.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $jsonData = [
            'status' => 'error',
            'message' => self::ERR_API_CALL_ERROR
        ];
        if (Area::AREA_ADMINHTML == $this->state->getAreaCode()) {
            $data = $this->getRequest()->getParams();
            $storeId = 0;
            if (array_key_exists(ScopeInterface::SCOPE_WEBSITE, $data)) {
                $websiteId = $data[ScopeInterface::SCOPE_WEBSITE];
                $website = $this->storeManager->getWebsite($websiteId);
                $storeId = $website->getDefaultGroup()->getDefaultStoreId();
            } elseif (array_key_exists(ScopeInterface::SCOPE_STORE, $data)) {
                $storeId = $data[ScopeInterface::SCOPE_STORE];
            }

            $response = $this->api->getClients($storeId);
            if ($response) {
                $jsonData = [
                    'status' => 'success',
                    'message' => self::LOG_API_CALL_SUCCESSFUL
                ];
            } else {
                $jsonData = [
                    'status' => 'error',
                    'message' => self::ERR_API_CALL_ERROR
                ];
            }
        }
        return $resultJson->setData($jsonData);
    }
}

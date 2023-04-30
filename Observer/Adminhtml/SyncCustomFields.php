<?php

namespace Fuutur\CampaignMonitor\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Backend\Model\Session;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;
use Magento\Store\Model\Store;

class SyncCustomFields implements ObserverInterface
{
    /**
     * @param Session $backendSession
     * @param CampaignMonitor $api
     */
    public function __construct(
        private Session $backendSession,
        private CampaignMonitor $api
    ) {
        $this->backendSession = $backendSession;
        $this->api = $api;
    }

    /**
     * Sync Campaignmonitor custom field
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $error = $this->api->createCustomerCustomFields(Store::DEFAULT_STORE_ID);
        if (count($error) > 0) {
            foreach ($error as $key => $msg) {
                $this->backendSession->addError($msg);
            }
        }
    }
}

<?php

namespace Fuutur\CampaignMonitor\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;

class DeleteCustomer implements ObserverInterface
{
    /**
     * @param CampaignMonitor $api
     */
    public function __construct(
        private CampaignMonitor $api
    ) {
        $this->api = $api;
    }

    /**
     * Unsubscribes a user when delete customer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $this->apiFactory->unsubscribe(
            $customer->getEmail(),
            $customer->getStoreId()
        );
    }
}

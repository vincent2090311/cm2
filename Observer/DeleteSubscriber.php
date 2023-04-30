<?php

namespace Fuutur\CampaignMonitor\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;

class DeleteSubscriber implements ObserverInterface
{
    /**
     * @param CampaignMonitor $api
     */
    public function __construct(
        private CampaignMonitor $api,
    ) {
        $this->api = $api;
    }

    /**
     * Unsubscribes a user when delete subscriber
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $this->api->unsubscribe(
            $subscriber->getSubscriberEmail(),
            $subscriber->getStoreId()
        );
    }
}

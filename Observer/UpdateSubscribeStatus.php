<?php

namespace Fuutur\CampaignMonitor\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;
use Magento\Newsletter\Model\Subscriber;

class UpdateSubscribeStatus implements ObserverInterface
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
     * Subscribes a new user when given a request event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        if ($subscriber->getSubscriberStatus() == Subscriber::STATUS_SUBSCRIBED) {
            $this->api->subscribe(
                $subscriber->getSubscriberEmail(),
                $subscriber->getStoreId()
            );
        } else {
            $this->api->unsubscribe(
                $subscriber->getSubscriberEmail(),
                $subscriber->getStoreId()
            );
        }
    }
}

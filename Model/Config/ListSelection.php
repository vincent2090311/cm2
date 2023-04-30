<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Model\Config;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;
use Magento\Store\Model\ScopeInterface;

class ListSelection
{
    /**
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param CampaignMonitor $api
     */
    public function __construct(
        protected RequestInterface $request,
        protected StoreManagerInterface $storeManager,
        protected CampaignMonitor $api
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->api = $api;
    }

    /**
     * Get list subscriber list
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $storeId = 0;
        $data = $this->request->getParams();
        if (array_key_exists(ScopeInterface::SCOPE_WEBSITE, $data)) {
            $websiteId = $data[ScopeInterface::SCOPE_WEBSITE];
            $website = $this->storeManager->getWebsite($websiteId);
            $storeId = $website->getDefaultGroup()->getDefaultStoreId();
        } elseif (array_key_exists(ScopeInterface::SCOPE_STORE, $data)) {
            $storeId = $data[ScopeInterface::SCOPE_STORE];
        }

        $response = $this->api->getSubscriberLists($storeId);

        if ($response) {
            array_unshift($response, [
                'value'  => '',
                'label'  => __('Please select subscriber list')
            ]);
        }

        return $response;
    }
}

<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        protected Context $context,
        protected SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * Check can log api response data
     *
     * @return bool
     */
    public function canLog() : bool
    {
        return $this->scopeConfig->isSetFlag('createsend_general/advanced/logging');
    }

    /**
     * Returns a sanitized version of the API key configuration value
     *
     * @param int $storeId
     * @return string
     */
    public function getApiKey($storeId) : string
    {
        $apikey = (string)$this->scopeConfig->getValue(
            'createsend_general/api/api_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return trim($apikey);
    }

    /**
     * Returns a sanitized version of the API key Client ID configuration value
     *
     * @param int $storeId
     * @return string
     */
    public function getApiClientId($storeId) : string
    {
        $clientId = (string)$this->scopeConfig->getValue(
            'createsend_general/api/api_client_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return trim($clientId);
    }

    /**
     * Returns a sanitized version of the list id configuration value
     *
     * @param int $storeId
     * @return string
     */
    public function getListId($storeId) : string
    {
        return (string)$this->scopeConfig->getValue(
            'createsend_general/api/list_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns a sanitized version of the list id configuration value
     *
     * @param int $storeId
     * @return array
     */
    public function getCustomFieldsConfig($storeId) : array
    {
        $customFields = $this->scopeConfig->getValue(
            'createsend_general/advanced/customer_attributes',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$customFields) {
            return [];
        }

        $customFields = $this->serializer->unserialize($customFields);
        return array_column($customFields, 'attributes');
    }
}

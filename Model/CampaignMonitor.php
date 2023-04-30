<?php

namespace Fuutur\CampaignMonitor\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Api\GroupRepositoryInterface;
use Fuutur\CampaignMonitor\Helper\Data;
use Fuutur\CampaignMonitor\Model\Config\CustomerAttributes;
use Fuutur\CampaignMonitor\Logger\Info;
use CS_REST_General as CmGeneral;
use CS_REST_Clients as CmClients;
use CS_REST_Lists as CmLists;
use CS_REST_Subscribers as CmSubscribers;

class CampaignMonitor
{
    public const CODE_FIELD_KEY_EXISTS = 255;

    public const FIELD_TYPE_TEXT               = 'Text';
    public const FIELD_TYPE_NUMBER             = 'Number';
    public const FIELD_TYPE_DATE               = 'Date';
    public const FIELD_TYPE_SELECT_ONE         = 'MultiSelectOne';
    public const FIELD_TYPE_SELECT_MANY        = 'MultiSelectMany';
    public const FIELD_TYPE_COUNTRY            = 'Country';

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param CustomerRegistry $customerRegistry
     * @param GroupRepositoryInterface $groupRepository
     * @param Data $helperData
     * @param CustomerAttributes $customerAttributes
     * @param Info $logger
     */
    public function __construct(
        protected ResourceConnection $resource,
        protected StoreManagerInterface $storeManager,
        protected CustomerRegistry $customerRegistry,
        protected GroupRepositoryInterface $groupRepository,
        protected Data $helperData,
        protected CustomerAttributes $customerAttributes,
        protected Info $logger
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->customerRegistry = $customerRegistry;
        $this->groupRepository = $groupRepository;
        $this->helperData = $helperData;
        $this->customerAttributes = $customerAttributes;
        $this->logger = $logger;
    }

    /**
     * Gets all the Campaign Monitor clients for the given scope/scopeId
     *
     * @param int $storeId
     * @return array|null
     */
    public function getClients($storeId) : array
    {
        $apiKey = $this->helperData->getApiKey($storeId);

        $auth = ['api_key' => $apiKey];
        $wrap = new CmGeneral($auth);
        $result = $wrap->get_clients();

        if ($result->was_successful()) {
            $this->logger->log($result->response);

            $clients = [];
            foreach ($result->response as $resp) {
                $clients[] = [
                    'value'  => $resp->ClientID,
                    'label'  => $resp->Name
                ];
            }
            return $clients;
        } else {
            return [];
        }
    }

    /**
     * Gets all the Campaign Monitor subscriber lists for the given clientId
     * using credentials from given scope/scopeId for use in an HTML select.
     * The last option will be a 'Create a new list' option
     *
     * On API Error, returns a single element array with key 'value' => 'error'
     *
     * @param int $storeId
     * @return array|null
     */
    public function getSubscriberLists($storeId) : array
    {
        $apiKey = $this->helperData->getApiKey($storeId);
        $clientId = $this->helperData->getApiClientId($storeId);

        $auth = ['api_key' => $apiKey];
        $wrap = new CmClients($clientId, $auth);
        $result = $wrap->get_lists();

        if ($result->was_successful()) {
            $this->logger->log($result->response);

            $list = [];
            foreach ($result->response as $resp) {
                $list[] = [
                    'value'  => $resp->ListID,
                    'label'  => $resp->Name
                ];
            }
            return $list;
        } else {
            return [];
        }
    }

    /**
     * Gets the Campaign Monitor list stats for the given listId
     *
     * @param int $storeId
     * @return array|null
     */
    public function getListStats($storeId) : array
    {
        $apiKey = $this->helperData->getApiKey($storeId);
        $listId = $this->helperData->getListId($storeId);
        $auth = ['api_key' => $apiKey];
        $wrap = new CmLists($listId, $auth);
        $result = $wrap->get_stats();

        if ($result->was_successful()) {
            $this->logger->log($result->response);

            return (array) $result->response;
        } else {
            return [];
        }
    }

    /**
     * Creates Campaign Monitor customer custom fields on the list id defined in the scope.
     *
     * @param int $storeId
     * @return array List of errors, grouped by error message
     */
    public function createCustomerCustomFields($storeId)
    {
        $apiKey = $this->helperData->getApiKey($storeId);
        $listId = $this->helperData->getListId($storeId);
        $auth = ['api_key' => $apiKey];
        $wrap = new CmLists($listId, $auth);

        $errors = [];
        $linkedAttributes = $this->helperData->getCustomFieldsConfig($storeId);
        foreach ($linkedAttributes as $attr) {
            $fieldName = $this->customerAttributes->getCustomFieldName($attr);
            $dataType = $this->customerAttributes->getFieldType($attr);
            $options = $this->customerAttributes->getFieldOptions($attr);

            $result = $wrap->create_custom_field([
                'FieldName' => $fieldName,
                'DataType' => $dataType,
                'Options' => $options
            ]);

            if (!$result->was_successful()) {
                $this->logger->log($result->response);

                if ($result->response->Code != self::CODE_FIELD_KEY_EXISTS) {
                    $errors[] = $result->response->Message;
                }
            }
        }

        return $errors;
    }

    /**
     * Subscribes an email address to CM. The list ID will be retrieved from the configuration using the scope/scopeId.
     *
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    public function subscribe($email, $storeId) : bool
    {
        $apiKey = $this->helperData->getApiKey($storeId);
        $listId = $this->helperData->getListId($storeId);
        $auth = ['api_key' => $apiKey];
        $wrap = new CmSubscribers($listId, $auth);

        $data = [
            'EmailAddress' => $email,
            "Resubscribe" => true,
            'ConsentToTrack' => 'Unchanged'
        ];

        try {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $customer = $this->customerRegistry->retrieveByEmail($email, $websiteId);

            $data['Name'] = $customer->getFirstname() .' '. $customer->getLastname();

            $linkedAttributes = $this->helperData->getCustomFieldsConfig($storeId);
            if ($linkedAttributes) {
                $customFields = [];
                foreach ($linkedAttributes as $attr) {
                    switch ($attr) {
                        case 'group_id':
                            $group = $this->groupRepository->getById($customer->getGroupId());
                            $customFields[] = [
                                "Key" => $this->customerAttributes->getCustomFieldName($attr),
                                "Value" => $group->getCode()
                            ];
                            break;
                        case 'gender':
                            $gender = $customer->getAttribute($attr)->getSource()
                                ->getOptionText($customer->getData($attr));
                            $customFields[] = [
                                "Key" => $this->customerAttributes->getCustomFieldName($attr),
                                "Value" => $gender
                            ];
                            break;
                        default:
                            $customFields[] = [
                                "Key" => $this->customerAttributes->getCustomFieldName($attr),
                                "Value" => $customer->getData($attr)
                            ];
                            break;
                    }
                }

                if ($customFields) {
                    $data['CustomFields'] = $customFields;
                }
            }
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }

        $result = $wrap->add($data);
        if ($result->was_successful()) {
            $this->logger->log($result->response);

            $this->updateSubscriberListId($email, $listId);
            return true;
        }

        return false;
    }

    /**
     * Un-subscribes an email address from CM list of scope/scopeId
     *
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    public function unsubscribe($email, $storeId) : bool
    {
        $apiKey = $this->helperData->getApiKey($storeId);
        $listId = $this->helperData->getListId($storeId);
        $auth = ['api_key' => $apiKey];
        $wrap = new CmSubscribers($listId, $auth);
        $result = $wrap->unsubscribe($email);

        if ($result->was_successful()) {
            $this->logger->log($result->response);

            $this->updateSubscriberListId($email, $listId, false);
            return true;
        }
        return false;
    }

    /**
     * Update subscriber list id
     *
     * @param string $email
     * @param string $listId
     * @param bool $isSubscribe
     * @return void
     */
    private function updateSubscriberListId($email, $listId, $isSubscribe = true)
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('newsletter_subscriber');
        $select = $connection->select()
            ->from($table, ['cm_listid'])
            ->where("subscriber_email = ?", $email);
        $cm_listid = array_filter(explode(',', (string)$connection->fetchOne($select)));

        if ($isSubscribe) {
            $cm_listid[] = $listId;
        } else {
            $cm_listid = array_diff($cm_listid, [$listId]);
        }

        $data = ['cm_listid' => implode(',', array_unique($cm_listid))];
        $where = ['subscriber_email = ?' => $email];
        $connection->update($table, $data, $where);
    }
}

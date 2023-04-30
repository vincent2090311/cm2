<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Model\Config;

use Fuutur\CampaignMonitor\Model\Config\Attributes\AbstractAttributes;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Fuutur\CampaignMonitor\Model\CampaignMonitor;

class CustomerAttributes extends AbstractAttributes
{
    /**
     * @var array $_customFieldNameMapping
     **/
    // Custom field name should be less than 100 characters
    protected $_customFieldNameMapping = [
        'created_at'       => 'Customer Date Created',
        'created_in'       => 'Customer Created From Store',
        'dob'              => 'Customer Date Of Birth',
        'gender'           => 'Customer Gender',
        'group_id'         => 'Customer Group'
    ];

    /**
     * @param CollectionFactory $customerAttributeCollection
     */
    public function __construct(
        protected CollectionFactory $customerAttributeCollection
    ) {
        $this->customerAttributeCollection = $customerAttributeCollection;
        $this->initAttributes();
    }

    /**
     * Init customer attributes
     *
     * @return void
     */
    protected function initAttributes()
    {
        $collection = $this->customerAttributeCollection->create();
        $collection->addFieldToFilter('attribute_code', ['in' => array_keys($this->_customFieldNameMapping)]);

        foreach ($collection as $attribute) {
            $code = $attribute->getAttributeCode();
            $input = $attribute->getFrontendInput();

            // Get the attribute type for Campaign Monitor
            if ($input === 'date' || $input === 'datetime') {
                $type = CampaignMonitor::FIELD_TYPE_DATE;
            } elseif ($attribute->getBackendType() == 'int' && $input == 'text') {
                $type = CampaignMonitor::FIELD_TYPE_NUMBER;
            } elseif ($attribute->getBackendType() == 'decimal') {
                $type = CampaignMonitor::FIELD_TYPE_NUMBER;
            } elseif ($code == 'gender' || $input === 'boolean') {
                $type = CampaignMonitor::FIELD_TYPE_SELECT_ONE;

                $allOptions = $attribute->getSource()->getAllOptions(false);
                $options = [];
                foreach ($allOptions as $option) {
                    $options[] = $option['label'];
                }
                $this->_fields[$code]['options'] = $options;
            } else {
                $type = CampaignMonitor::FIELD_TYPE_TEXT;
            }

            // Populate the field list
            $this->_fields[$code]['label'] = $this->_customFieldNameMapping[$code];
            $this->_fields[$code]['type'] = $type;
        }
        asort($this->_fields);
    }

    /**
     * Returns all attribute option labels in an array
     *
     * @param string $field
     * @return array
     */
    public function getFieldOptions($field)
    {
        if (array_key_exists($field, $this->_fields) &&
            $this->_fields[$field]['type'] == CampaignMonitor::FIELD_TYPE_SELECT_ONE
        ) {
            return $this->_fields[$field]['options'];
        } else {
            return [];
        }
    }

    /**
     * Returns all attributes
     *
     * @return array
     */
    public function getCustomerCustomFields()
    {
        return $this->_customFieldNameMapping;
    }
}

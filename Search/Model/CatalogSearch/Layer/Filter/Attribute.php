<?php


namespace Klevu\Search\Model\CatalogSearch\Layer\Filter;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Framework\Helper\String
     */
    protected $_frameworkHelperString;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Magento\Framework\Helper\String $frameworkHelperString)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperData = $searchHelperData;
        $this->_frameworkHelperString = $frameworkHelperString;

        parent::__construct();
    }


    /**
     * Prepare and fetch an attributes options.
     *
     * @return array|null
     */
    protected function _getItemsData()
    {
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            return parent::_getItemsData();
        }

        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);


        if ($data === null) {
            $klevu_filters = $this->_getKlevuAttributeFilters();
            if (!isset($klevu_filters[$this->_requestVar])) {
                return array(); // No results found for filter in Klevu response. Return empty array.
            }
            if ($this->getLayer()->getProductCollection()->count() == 0) {
                return array(); // No visible results found in search
            }

            $klevu_attribute = $klevu_filters[$this->_requestVar];
            $options = $attribute->getFrontend()->getSelectOptions();
            $data = array();
            foreach ($options as $option) {
                $klevu_option = $this->_findKlevuOption($option, $klevu_attribute);
                if (!$klevu_option) {
                    continue; // Skip record since klevu option was not found.
                }
                if (is_array($option['value'])) {
                    continue;
                }
                if ($this->_frameworkHelperString->strlen($option['value'])) {
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($klevu_option['count'])) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => $klevu_option['count'],
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => $klevu_option['count'],
                        );
                    }
                }
            }

            $tags = array(
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * Attempt to find the Klevu option from the array of magento options.
     * Returns false if there were no matches, otherwise returns Klevu option.
     *
     * @param $option
     * @param $klevu_attribute
     * @return array|bool
     */
    protected function _findKlevuOption($option, $klevu_attribute) {

        foreach ($klevu_attribute['options'] as $klevu_option) {
            if(strtolower($option['label']) == strtolower($klevu_option['label'])) {
                return $klevu_option;
            }
        }

        return false;
    }
    /**
     * Returns array of attribute filters from Klevu  [ 'label' => 'T-Shirts', 'count' => 1, 'selected' => false ]
     * @return array
     */
    protected function _getKlevuAttributeFilters() {
        /** @var \Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getProductCollection();
        return $collection->getKlevuFilters();
    }


}

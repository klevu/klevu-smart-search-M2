<?php

namespace Klevu\Search\Model\CatalogSearch\Resource\Layer\Filter;

class Attribute extends \Magento\Catalog\Model\Resource\Eav\Resource\Layer\Filter\Attribute {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperData = $searchHelperData;

        parent::__construct();
    }


    /**
     * Stub method to prevent filters being applied. Klevu handles all filtering.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filter
     * @param int $value
     * @return $this|\Magento\Catalog\Model\Resource\Layer\Filter\Attribute
     */
    public function applyFilterToCollection($filter, $value) {
        // If the Klevu module is not configured/enabled, run the parent method.
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            parent::applyFilterToCollection($filter, $value);
        }

        return $this;
    }
}

<?php

namespace Klevu\Search\Model\CatalogSearch\Layer\Filter;

class Category extends \Magento\Catalog\Model\Layer\Filter\Category {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_catalogModelCategory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_frameworkAppRequestInterface;

    /**
     * @var \Magento\Framework\Helper\Data
     */
    protected $_frameworkHelperData;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Magento\Catalog\Model\Category $catalogModelCategory, 
        \Magento\Framework\App\RequestInterface $frameworkAppRequestInterface, 
        \Magento\Framework\Helper\Data $frameworkHelperData)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperData = $searchHelperData;
        $this->_catalogModelCategory = $catalogModelCategory;
        $this->_frameworkAppRequestInterface = $frameworkAppRequestInterface;
        $this->_frameworkHelperData = $frameworkHelperData;

        parent::__construct();
    }


    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            return parent::_getItemsData();
        }

        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);


        if ($data === null) {
            // Fetch filters from Klevu
            $filters = $this->_getKlevuCategoryFilters();
            
            if ($this->getLayer()->getProductCollection()->count() == 0) {
                return array(); // No visible results found in search
            }

            // Prepare all the available category names
            $category_names = array();
            foreach($filters as $filter) {
                $category_names[] = $filter['label'];
            }
            $category   = $this->getCategory();
            // Get the all categories returned from klevu, and apply the current parent category.
            $categories = $this->_catalogModelCategory->getCollection()
                ->addAttributeToSelect('is_active')
                ->addFieldToFilter('name', array('in' => $category_names));

            $this->getLayer()->getProductCollection()
                ->addCountToCategories($categories);

            $data = array();
            $k_cat = $this->_frameworkAppRequestInterface->getParam('cat');
            if(!isset($k_cat)) {
                foreach ($categories as $category) {
                    // Ensure the category exists within the Klevu filters.
                    if (!$klevu_category = $this->_findKlevuCategory($category, $filters)) {
                        continue;
                    }

                    if ($category->getIsActive() && $category->getProductCount()) {
                        $data[] = array(
                            'label' => $this->_frameworkHelperData->escapeHtml($category->getName()),
                            'value' => $category->getId(),
                            'count' => $klevu_category['count'],
                        );
                    }
                } 
            }
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }

        return $data;
    }

    protected function _findKlevuCategory($category, $filters) {
        foreach($filters as $filter) {
            if(strtolower($filter['label']) == strtolower($category->getName())) {
                return $filter;
            }
        }
        return false;
    }

    /**
     * This method in \Magento\Catalog\Model\Layer\Filter\Category would ensure when a category filter is remove, the parent
     * category is applied as a filter. This isn't expected functionality for Klevu, and has been reset to a null value.
     * @return null
     */
    public function getResetValue() {
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            return parent::getResetValue();
        }

        return null;
    }

    /**
     * Returns array of category filters from Klevu  [ 'label' => 'T-Shirts', 'count' => 1, 'selected' => false ]
     * @return array
     */
    protected function _getKlevuCategoryFilters() {
        /** @var \Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getProductCollection();
        $klevu_filters = $collection->getKlevuFilters();
        if (!empty($klevu_filters['category'])) {
            return $klevu_filters['category']['options'];
        }

        return array();
    }
}

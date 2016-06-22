<?php

namespace Klevu\Search\Model\CatalogSearch\Layer\Filter;

class Price extends \Magento\Catalog\Model\Layer\Filter\Price {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_frameworkAppRequestInterface;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Magento\Framework\App\RequestInterface $frameworkAppRequestInterface)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperData = $searchHelperData;
        $this->_frameworkAppRequestInterface = $frameworkAppRequestInterface;

        parent::__construct();
    }


    public function apply(\Zend\Controller\Request\AbstractRequest $request, $filterBlock) {
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            return parent::apply($request, $filterBlock);
        }

        // In Magento 1.7 the price filter parameter was changed from the "<index>,<range>"
        // format to the "<from>-<to>" format. Klevu uses the latter, so in <1.7 we need
        // to parse the parameter manually
        if (version_compare(Mage::getVersion(), "1.7", ">=")) {
            return parent::apply($request, $filterBlock);
        } else {
            $filter = $request->getParam($this->getRequestVar());
            if (!$filter) {
                return $this;
            }

            $filter = explode("-", $filter);
            if (count($filter) != 2) {
                return $this;
            }

            list($from, $to) = $filter;

            $this->setInterval(array($from, $to));

            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
            );
        }
    }

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            return parent::_getItemsData();
        }

        $klevu_price_filters = $this->_getKlevuPriceFilters();
        $data = array();
        $k_price = $this->_frameworkAppRequestInterface->getParam('price');
        if(!isset($k_price)) {
            if (!empty($klevu_price_filters) && $this->getLayer()->getProductCollection()->count() > 0) {
                foreach ($klevu_price_filters as $filter) {
                    $prices = explode(" - ", $filter['label']);
                    $fromPrice = $prices[0];
                    $toPrice = $prices[1];

                    $data[] = array(
                        'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                        'value' => $fromPrice . '-' . $toPrice,
                        'count' => $filter['count'],
                    );
                }
            }
        }

        return $data;
    }

    protected function _renderRangeLabel($fromPrice, $toPrice) {
        //if (method_exists(get_parent_class($this), "_renderRangeLabel")) {
           // return parent::_renderRangeLabel($fromPrice, $toPrice);
       // } else {
            $range = $toPrice - $fromPrice;
            $value = $toPrice / $range;
            return parent::_renderItemLabel($range, $value);
        //}
    }

    /**
     * Returns array of price ranges from Klevu  [ 'label' => '10 - 25', 'count' => 1, 'selected' => false ]
     * @return array
     */
    protected function _getKlevuPriceFilters() {
        /** @var \Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getProductCollection();
        $klevu_filters = $collection->getKlevuFilters();
        if (!empty($klevu_filters['Price Range'])) {
            return $klevu_filters['Price Range']['options'];
        }

        return array();
    }
}

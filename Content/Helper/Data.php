<?php
namespace Klevu\Content\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_frameworkAppRequestInterface;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Model\Api\Action\Idsearch
     */
    protected $_apiActionIdsearch;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Klevu\Search\Model\Api\Action\Searchtermtracking
     */
    protected $_apiActionSearchtermtracking;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    public function __construct(\Magento\Framework\App\RequestInterface $frameworkAppRequestInterface, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Model\Api\Action\Idsearch $apiActionIdsearch, 
        \Klevu\Search\Helper\Data $searchHelperData, 
        \Klevu\Search\Model\Api\Action\Searchtermtracking $apiActionSearchtermtracking, 
        \Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface)
    {
        $this->_frameworkAppRequestInterface = $frameworkAppRequestInterface;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_apiActionIdsearch = $apiActionIdsearch;
        $this->_searchHelperData = $searchHelperData;
        $this->_apiActionSearchtermtracking = $apiActionSearchtermtracking;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;

    }

    protected $_klevu_Content_parameters;
    protected $_klevu_Content_response;
    protected $_klevu_Cms_Data;
    
    const XML_PATH_CMS_SYNC_ENABLED = "klevu_search/product_sync/enabledcms";
    const XML_PATH_EXCLUDED_CMS_PAGES = "klevu_search/cmscontent/excludecms";
    const XML_PATH_EXCLUDEDCMS_PAGES = "klevu_search/cmscontent/excludecms_pages";
    const XML_PATH_CMS_ENABLED_ON_FRONT = "klevu_search/cmscontent/enabledcmsfront";
    /**
     * Return the Klevu api content filters
     * @return array
     */
    public function getContentSearchFilters()
    {
        if (empty($this->_klevu_Content_parameters)) {
            $q = $this->_frameworkAppRequestInterface->getParam('q');
            $this->_klevu_Content_parameters = array(
                'ticket' => $this->_searchHelperConfig->getJsApiKey() ,
                'noOfResults' => 1000,
                'term' => $q,
                'klevuSort' => 'rel',
                'paginationStartsFrom' => 0,
                'enableFilters' => 'true',
                'category' => 'KLEVU_CMS',
                'fl' => 'name,shortDesc,url',
                'klevuShowOutOfStockProducts' => 'true',
                'filterResults' => $this->_getPreparedFilters() ,
            );
            $this->log(\Zend\Log\Logger::DEBUG, sprintf("Starting search for term: %s", $q));
        }
        return $this->_klevu_Content_parameters;
    }
    /**
     * Send the API Request and return the API Response.
     * @return \Klevu\Search\Model\Api\Response
     */
    public function getKlevuResponse()
    {
        if (!$this->_klevu_Content_response) {
            $this->_klevu_Content_response = $this->_apiActionIdsearch->execute($this->getContentSearchFilters());
        }
        return $this->_klevu_Content_response;
    }
    
    /**
     * Return the Klevu api search filters
     * @return array
     */
    public function getContentSearchTracking($noOfTrackingResults,$queryType) {
        $q = $this->_frameworkAppRequestInterface->getParam('q');
        $this->_klevu_tracking_parameters = array(
            'klevu_apiKey' => $this->_searchHelperConfig->getJsApiKey(),
            'klevu_term' => $q,
            'klevu_totalResults' => $noOfTrackingResults,
            'klevu_shopperIP' => $this->_searchHelperData->getIp(),
            'klevu_typeOfQuery' => $queryType,
            'Klevu\typeOfRecord' => 'KLEVU_CMS'
        );
        $this->log(\Zend\Log\Logger::DEBUG, sprintf("Content Search tracking for term: %s", $q));
        return $this->_klevu_tracking_parameters;
    }
    
    /**
     * This method executes the the Klevu API request if it has not already been called, and takes the result
     * with the result
     * We then add all these values to our class variable $_klevu_\Cms\Data.
     *
     * @return array
     */
    Public function getCmsData()
    {
        if (empty($this->_klevu_Cms_Data)) {
            // If no results, return an empty array
            if (!$this->getKlevuResponse()->hasData('result')) {
                return array();
            }
            foreach($this->getKlevuResponse()->getData('result') as $key => $value) {
                $value["name"] = $value['name'];
                $value["url"] = $value["url"];
                if (!empty($value['shortDesc'])) {
                    $value["shortDesc"] = $value['shortDesc'];
                }
                $cms_data[] = $value;
            }
            $this->_klevu_Cms_Data = $cms_data;
            
            $response_meta = $this->getKlevuResponse()->getData('meta');
            $this->_apiActionSearchtermtracking->execute($this->getContentSearchTracking(count($this->_klevu_Cms_Data),$response_meta['typeOfQuery']));
            $this->log(\Zend\Log\Logger::DEBUG, sprintf("Cms count returned: %s", count($this->_klevu_Cms_Data)));
        }
        return $this->_klevu_Cms_Data;
    }
    /**
     * Print Log in Klevu log file.
     *
     * @param int $level ,string $message
     *
     */
    protected function log($level, $message)
    {
        $this->_searchHelperData->log($level, $message);
    }
    /**
     * Get excluded cms page for store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return strings
     */
    public function getExcludedCmsPages($store = null)
    {
        return $this->_appConfigScopeConfigInterface->getValue(static ::XML_PATH_EXCLUDED_CMS_PAGES, $store);
    }
    
    /**
     * Get excluded cms page for store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return strings
     */
    public function getExcludedPages($store = null)
    {
        $values = unserialize($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_EXCLUDEDCMS_PAGES, $store));
        if (is_array($values)) {
            return $values;
        }
        return array();
    }
    
    
    /**
     * Get value of cms synchronize for the given store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function getCmsSyncEnabledFlag($store = null)
    {
        return intval($this->_appConfigScopeConfigInterface->getValue(static ::XML_PATH_CMS_SYNC_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store));
    }
    /**
     * Check if Cms Sync is enabled for the given store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function isCmsSyncEnabled($store = null)
    {
        $flag = $this->getCmsSyncEnabledFlag($store);
        return in_array($flag, array(
            \Klevu\Search\Model\System\Config\Source\Yesnoforced::YES,
        ));
    }
    /**
     * Get value of cms synchronize for the given store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function getCmsSyncEnabledOnFront($store = null)
    {
		
        return intval($this->_appConfigScopeConfigInterface->getValue(static ::XML_PATH_CMS_ENABLED_ON_FRONT,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store));
    }
    /**
     * Check if Cms is enabled on frontend for the given store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function isCmsSyncEnabledOnFront($store = null)
    {
        $flag = $this->getCmsSyncEnabledOnFront($store);
        return in_array($flag, array(
            \Klevu\Search\Model\System\Config\Source\Yesnoforced::YES,
        ));
    }
    /**
     * Get the type filters for Content from Klevu .
     *
     * @return array
     */
    public function getKlevuFilters()
    {
        $attributes = array();
        $filters = $this->getKlevuResponse()->getData('filters');
        // If there are no filters, return empty array.
        if (empty($filters)) {
            return array();
        }
        foreach($filters as $filter) {
            $key = (string)$filter['key'];
            $attributes[$key] = array(
                'label' => (string)$filter['label']
            );
            $attributes[$key]['options'] = array();
            if ($filter['options']) {
                foreach($filter['options'] as $option) {
                    $attributes[$key]['options'][] = array(
                        'label' => trim((string)$option['name']) ,
                        'count' => trim((string)$option['count']) ,
                        'selected' => trim((string)$option['selected'])
                    );
                }
            }
        }
        return $attributes;
    }
    /**
     * Get the active filters, then prepare them for Klevu.
     *
     * @return string
     */
    protected function _getPreparedFilters()
    {
        $prepared_filters = array();
        $filter_type = $this->_frameworkAppRequestInterface->getParam('cat');
        if (!empty($filter_type)) {
            switch ($filter_type) {
            case "cat":
                $prepared_filters['category'] = $filter_type;
                break;

            default:
                $prepared_filters['category'] = $filter_type;
                break;
            }
            $this->log(\Zend\Log\Logger::DEBUG, sprintf('Active For Category Filters: %s', var_export($prepared_filters, true)));
            return implode(';;', array_map(function ($v, $k)
            {
                return sprintf('%s:%s', $k, $v);
            }
            , $prepared_filters, array_keys($prepared_filters)));
        }
    }
    
    /**
     * Return the Cms pages.
     *
     * @param int|\Magento\Framework\Model\Store $store
     *
     * @return array
     */
    public function getCmsPageMap($store = null) {
        $cmsmap = unserialize($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_EXCLUDEDCMS_PAGES,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store));
        return (is_array($cmsmap)) ? $cmsmap : array();
    }
    
    public function setCmsPageMap($map, $store = null) {
        unset($map["__empty"]);
        $this->_searchHelperConfig->setStoreConfig(static::XML_PATH_EXCLUDEDCMS_PAGES, serialize($map), $store);
        return $this;
    }

}
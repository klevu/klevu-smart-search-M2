<?php

namespace Klevu\Search\Helper;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\UrlInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Klevu\Search\Model\Product\Sync;
use \Magento\Framework\Model\Store;
use \Klevu\Search\Model\Api\Action\Features;

class Config extends \Magento\Framework\App\Helper\AbstractHelper {
	
	/**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_frameworkAppRequestInterface;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_magentoFrameworkUrlInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;
	

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_frameworkModelStore;


    /**
     * @var \Klevu\Search\Model\Api\Action\Features
     */
    protected $_apiActionFeatures;
	
	protected $_klevu_features_response;
	
	/**
     * @var \Magento\Framework\Config\Data
     */
    protected $_modelConfigData;

    public function __construct(\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface, 
        \Magento\Framework\UrlInterface $magentoFrameworkUrlInterface, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface,
		\Magento\Framework\App\RequestInterface $frameworkAppRequestInterface,
		\Magento\Store\Model\Store $frameworkModelStore,
		\Magento\Framework\App\Config\Value  $modelConfigData
       )
    {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_magentoFrameworkUrlInterface = $magentoFrameworkUrlInterface;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
		$this->_frameworkAppRequestInterface = $frameworkAppRequestInterface;
		$this->_frameworkModelStore = $frameworkModelStore;
		$this->_modelConfigData = $modelConfigData;

    }


    const XML_PATH_EXTENSION_ENABLED = "klevu_search/general/enabled";
    const XML_PATH_TEST_MODE         = "klevu_search/general/test_mode";
    const XML_PATH_TAX_ENABLED       = "klevu_search/tax_setting/enabled";
    const XML_PATH_SECUREURL_ENABLED = "klevu_search/secureurl_setting/enabled";
    const XML_PATH_LANDING_ENABLED   = "klevu_search/searchlanding/landenabled";
    const XML_PATH_JS_API_KEY        = "klevu_search/general/js_api_key";
    const XML_PATH_REST_API_KEY      = "klevu_search/general/rest_api_key";
    const XML_PATH_TEST_JS_API_KEY   = "klevu_search/general/test_js_api_key";
    const XML_PATH_TEST_REST_API_KEY = "klevu_search/general/test_rest_api_key";
    const XML_PATH_PRODUCT_SYNC_ENABLED   = "klevu_search/product_sync/enabled";
    const XML_PATH_PRODUCT_SYNC_FREQUENCY = "klevu_search/product_sync/frequency";
    const XML_PATH_PRODUCT_SYNC_LAST_RUN = "klevu_search/product_sync/last_run";
    const XML_PATH_ATTRIBUTES_ADDITIONAL  = "klevu_search/attributes/additional";
    const XML_PATH_ATTRIBUTES_AUTOMATIC  = "klevu_search/attributes/automatic";
    const XML_PATH_ATTRIBUTES_OTHER       = "klevu_search/attributes/other";
    const XML_PATH_ATTRIBUTES_BOOSTING       = "klevu_search/attributes/boosting";
    const XML_PATH_ORDER_SYNC_ENABLED   = "klevu_search/order_sync/enabled";
    const XML_PATH_ORDER_SYNC_FREQUENCY = "klevu_search/order_sync/frequency";
    const XML_PATH_ORDER_SYNC_LAST_RUN = "klevu_search/order_sync/last_run";
    const XML_PATH_FORCE_LOG = "klevu_search/developer/force_log";
    const XML_PATH_LOG_LEVEL = "klevu_search/developer/log_level";
    const XML_PATH_STORE_ID = "stores/%s/system/store/id";
    const XML_PATH_HOSTNAME = "klevu_search/general/hostname";
    const XML_PATH_RESTHOSTNAME = "klevu_search/general/rest_hostname";
    const XML_PATH_TEST_HOSTNAME = "klevu_search/general/test_hostname";
    const XML_PATH_CLOUD_SEARCH_URL = "klevu_search/general/cloud_search_url";
    const XML_PATH_TEST_CLOUD_SEARCH_URL = "klevu_search/general/test_cloud_search_url";
    const XML_PATH_ANALYTICS_URL = "klevu_search/general/analytics_url";
    const XML_PATH_TEST_ANALYTICS_URL = "klevu_search/general/test_analytics_url";
    const XML_PATH_JS_URL = "klevu_search/general/js_url";
    const XML_PATH_TEST_JS_URL = "klevu_search/general/test_js_url";
    const KLEVU_PRODUCT_FORCE_OLDERVERSION = 2;
    const XML_PATH_SYNC_OPTIONS = "klevu_search/product_sync/sync_options";
    const XML_PATH_UPGRADE_PREMIUM = "klevu_search/general/premium";
    const XML_PATH_RATING = "klevu_search/general/rating_flag";
	const XML_PATH_UPGRADE_FEATURES = "klevu_search/general/upgrade_features";
    const XML_PATH_UPGRADE_TIRES_URL = "klevu_search/general/tiers_url";

    const DATETIME_FORMAT = "Y-m-d H:i:s T";

    /**
     * Set the Enable on Frontend flag in System Configuration for the given store.
     *
     * @param      $flag
     * @param \Magento\Framework\Model\Store|int|null $store Store to set the flag for. Defaults to current store.
     *
     * @return $this
     */
    public function setExtensionEnabledFlag($flag, $store = null) {
        $flag = ($flag) ? 1 : 0;
        $this->setStoreConfig(static::XML_PATH_EXTENSION_ENABLED, $flag,$store);
        return $this;
    }

    /**
     * Check if the \Klevu\Search extension is enabled in the system configuration for the current store.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isExtensionEnabled($store_id = null) {
        return $this->_appConfigScopeConfigInterface->isSetFlag(static::XML_PATH_EXTENSION_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store_id);
    }
    
    /**
     * Check if the Tax is enabled in the system configuration for the current store.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isTaxEnabled($store_id = null) {
			$flag = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_TAX_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store_id);
			return in_array($flag, array(
                \Klevu\Search\Model\System\Config\Source\Taxoptions::YES,
            ));
    }
    
    /**
     * Check if the Secure url is enabled in the system configuration for the current store.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isSecureUrlEnabled($store_id = null) {
        return $this->_appConfigScopeConfigInterface->isSetFlag(static::XML_PATH_SECUREURL_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store_id);
    }
    /**
     * Check if the Landing is enabled in the system configuration for the current store.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isLandingEnabled($store = null) {
        return intval($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_LANDING_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store));
    }

    /**
     * Set the Test Mode flag in System Configuration for the given store.
     *
     * @param      $flag
     * @param null $store Store to use. If not specified, uses the current store.
     *
     * @return $this
     */
    public function setTestModeEnabledFlag($flag, $store = null) {
        $flag = ($flag) ? 1 : 0;
        $this->setStoreConfig(static::XML_PATH_TEST_MODE, $flag, $store);
        return $this;
    }

    /**
     * Return the configuration flag for enabling test mode.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function getTestModeEnabledFlag($store = null) {
        return $this->_appConfigScopeConfigInterface->isSetFlag(static::XML_PATH_TEST_MODE, $store);
    }
    
    /**
     * Set the Tax mode in System Configuration for the given store.
     *
     * @param      $flag
     * @param null $store Store to use. If not specified, uses the current store.
     *
     * @return $this
     */
    public function setTaxEnabledFlag($flag, $store = null) {
    
        $this->setStoreConfig(static::XML_PATH_TAX_ENABLED, $flag, $store);
        return $this;
    }
    
    /**
     * Set the Secure Url mode in System Configuration for the given store.
     *
     * @param      $flag
     * @param null $store Store to use. If not specified, uses the current store.
     *
     * @return $this
     */
    public function setSecureUrlEnabledFlag($flag, $store = null) {
    
        $flag = ($flag) ? 1 : 0;
        $this->setStoreConfig(static::XML_PATH_SECUREURL_ENABLED, $flag, $store);
        return $this;
    }

    /**
     * Check if Test Mode is enabled for the given store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function isTestModeEnabled($store = null) {
        return $this->getTestModeEnabledFlag($store);
    }

    /**
     * Set the JS API key in System Configuration for the given store.
     *
     * @param string                    $key
     * @param \Magento\Framework\Model\Store|int $store     Store to use. If not specified, will use the current store.
     * @param bool                      $test_mode Set the key to be used in Test Mode.
     *
     * @return $this
     */
    public function setJsApiKey($key, $store = null, $test_mode = false) {
        $path = static::XML_PATH_JS_API_KEY;
        $this->setStoreConfig($path, $key, $store);
        return $this;
    }

    /**
     * Return the JS API key configured for the specified store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return string
     */
    public function getJsApiKey($store = null) {
            return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_JS_API_KEY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store);
    }

    /**
     * Set the REST API key in System Configuration for the given store.
     *
     * @param string                    $key
     * @param \Magento\Framework\Model\Store|int $store     Store to use. If not specified, will use the current store.
     * @param bool                      $test_mode Set the key to be used in Test Mode.
     *
     * @return $this
     */
    public function setRestApiKey($key, $store = null, $test_mode = false) {
        $path = static::XML_PATH_REST_API_KEY;
        $this->setStoreConfig($path, $key,$store);
        return $this;
    }

    /**
     * Return the REST API key configured for the specified store.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return mixed
     */
    public function getRestApiKey($store = null) {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_REST_API_KEY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store);
    }

    /**
     * Set the API Hostname value in System Configuration for a given store
     * @param $hostname
     * @param null $store
     * @param bool $test_mode
     * @return $this
     */
    public function setHostname($hostname, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_TEST_HOSTNAME : static::XML_PATH_HOSTNAME;
        $this->setStoreConfig($path, $hostname, $store);
        return $this;
    }

    /**
     * Return the API Hostname configured, used for API requests, for a specified store
     * @param \Magento\Framework\Model\Store|int|null $store
     * @return string
     */
    public function getHostname($store = null) {
        $hostname = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_HOSTNAME,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store->getId());
        return ($hostname) ? $hostname : \Klevu\Search\Helper\Api::ENDPOINT_DEFAULT_HOSTNAME;
    }
    
    /**
     * Return the API Rest Hostname configured, used for API requests, for a specified store
     * @param \Magento\Framework\Model\Store|int|null $store
     * @return string
     */
    public function getRestHostname($store = null) {
        $hostname = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_RESTHOSTNAME,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ($hostname) ? $hostname : \Klevu\Search\Helper\Api::ENDPOINT_DEFAULT_HOSTNAME;
    }
    
     /**
     * Set the Rest Hostname value in System Configuration for a given store
     * @param $url
     * @param null $store
     * @param bool $test_mode
     * @return $this
     */
    public function setRestHostname($url, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_RESTHOSTNAME : static::XML_PATH_RESTHOSTNAME;
        $this->setStoreConfig($path, $url, $store);
        return $this;
    }

    /**
     * @param $url
     * @param null $store
     * @param bool $test_mode
     * @return $this
     */
    public function setCloudSearchUrl($url, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_TEST_CLOUD_SEARCH_URL : static::XML_PATH_CLOUD_SEARCH_URL;
        $this->setStoreConfig($path, $url, $store);
        return $this;
    }

    /**
     * @param null $store
     * @return string
     */
    public function getCloudSearchUrl($store = null) {
        if($this->isTestModeEnabled($store)) {
            $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_TEST_CLOUD_SEARCH_URL, $store);
        } else {
            $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_CLOUD_SEARCH_URL, $store);
        }

        return ($url) ? $url : \Klevu\Search\Helper\Api::ENDPOINT_DEFAULT_HOSTNAME;
    }

    /**
     * @param $url
     * @param null $store
     * @param bool $test_mode
     * @return $this
     */
    public function setAnalyticsUrl($url, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_TEST_ANALYTICS_URL : static::XML_PATH_ANALYTICS_URL;
        $this->setStoreConfig($path, $url, $store);
        return $this;
    }

    /**
     * @param null $store
     * @return string
     */
    public function getAnalyticsUrl($store = null) {
        //if($this->isTestModeEnabled($store)) {
        //    $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_TEST_ANALYTICS_URL);
        //} else {
            $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ANALYTICS_URL);
        //}
        return ($url) ? $url : \Klevu\Search\Helper\Api::ENDPOINT_DEFAULT_HOSTNAME;
    }

    /**
     * @param $url
     * @param null $store
     * @param bool $test_mode
     * @return $this
     */
    public function setJsUrl($url, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_TEST_JS_URL : static::XML_PATH_JS_URL;
        $this->setStoreConfig($path, $url, $store);
        return $this;
    }

    /**
     * @param null $store
     * @return string
     */
    public function getJsUrl($store = null) {
        if($this->isTestModeEnabled(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_TEST_JS_URL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_JS_URL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return ($url) ? $url : \Klevu\Search\Helper\Api::ENDPOINT_DEFAULT_HOSTNAME;
    }

    /**
     * Check if the Klevu Search extension is configured for the given store.
     *
     * @param null $store_id
     *
     * @return bool
     */
    public function isExtensionConfigured($store_id = null) {
        $js_api_key = $this->getJsApiKey($store_id);
        $rest_api_key = $this->getRestApiKey($store_id);
        return (
            $this->isExtensionEnabled($store_id)
            && !empty($js_api_key)
            && !empty($rest_api_key)
        );
    }

    /**
     * Return the system configuration setting for enabling Product Sync for the specified store.
     * The returned value can have one of three possible meanings: Yes, No and Forced. The
     * values mapping to these meanings are available as constants on
     * \Klevu\Search\Model\System\Config\Source\Yesnoforced.
     *
     * @param $store_id
     *
     * @return int
     */
    public function getProductSyncEnabledFlag($store_id = null) {
    
        return intval($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_PRODUCT_SYNC_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    /**
     * Check if Product Sync is enabled for the specified store and domain.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isProductSyncEnabled($store_id = null) {

        $flag = $this->getProductSyncEnabledFlag($store_id);

        // static::KLEVU_PRODUCT_FORCE_OLDERVERSION for handling of older version of klevu 
        //if ($this->_searchHelperData->isProductionDomain($this->_magentoFrameworkUrlInterface->getBaseUrl())) {
            return in_array($flag, array(
                \Klevu\Search\Model\System\Config\Source\Yesnoforced::YES,
                static::KLEVU_PRODUCT_FORCE_OLDERVERSION
            ));
        //} else {
        //    return $flag === \Klevu\Search\Model\System\Config\Source\Yesnoforced::FORCED;
        //}
    }

    /**
     * Return the configured frequency expression for Product Sync.
     *
     * @return string
     */
    public function getProductSyncFrequency() {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_PRODUCT_SYNC_FREQUENCY);
    }

    /**
     * Set the last Product Sync run time in System Configuration for the given store.
     *
     * @param DateTime|string                $datetime If string is passed, it will be converted to DateTime.
     * @param \Magento\Framework\Model\Store|int|null $store
     *
     * @return $this
     */
    public function setLastProductSyncRun($datetime = "now", $store = null) {
        if (!$datetime instanceof DateTime) {
            $datetime = new \DateTime($datetime);
        }

        $this->setStoreConfig(static::XML_PATH_PRODUCT_SYNC_LAST_RUN, $datetime->format(static::DATETIME_FORMAT), $store);

        return $this;
    }

    /**
     * Check if Product Sync has ever run for the given store.
     *
     * @param \Magento\Framework\Model\Store|int|null $store
     *
     * @return bool
     */
    public function hasProductSyncRun($store = null) {
        $config = $this->_appConfigScopeConfigInterface;
        if (!$config->getValue(static::XML_PATH_PRODUCT_SYNC_LAST_RUN,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store)) {
            return false;
        }

        return true;
    }

    public function setAdditionalAttributesMap($map, $store = null) {
        unset($map["__empty"]);
        $this->setStoreConfig(static::XML_PATH_ATTRIBUTES_ADDITIONAL, serialize($map), $store);
        return $this;
    }

    /**
     * Return the map of additional Klevu attributes to Magento attributes.
     *
     * @param int|\Magento\Framework\Model\Store $store
     *
     * @return array
     */
    public function getAdditionalAttributesMap($store = null) {
        $map = unserialize($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ATTRIBUTES_ADDITIONAL, $store));

        return (is_array($map)) ? $map : array();
    }

    /**
     * Set the automatically mapped attributes
     * @param array $map
     * @param int|\Magento\Framework\Model\Store $store
     * @return $this
     */
    public function setAutomaticAttributesMap($map, $store = null) {
        unset($map["__empty"]);
        $this->setStoreConfig(static::XML_PATH_ATTRIBUTES_AUTOMATIC, serialize($map), $store);
        return $this;
    }

    /**
     * Returns the automatically mapped attributes
     * @param int|\Magento\Framework\Model\Store $store
     * @return array
     */
    public function getAutomaticAttributesMap($store = null) {
        $map = unserialize($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ATTRIBUTES_AUTOMATIC, $store));

        return (is_array($map)) ? $map : array();
    }

    /**
     * Return the System Configuration setting for enabling Order Sync for the given store.
     * The returned value can have one of three possible meanings: Yes, No and Forced. The
     * values mapping to these meanings are available as constants on
     * \Klevu\Search\Model\System\Config\Source\Yesnoforced.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return int
     */
    public function getOrderSyncEnabledFlag($store = null) {
        return intval($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ORDER_SYNC_ENABLED, $store));
    }

    /**
     * Check if Order Sync is enabled for the given store on the current domain.
     *
     * @param \Magento\Framework\Model\Store|int $store
     *
     * @return bool
     */
    public function isOrderSyncEnabled($store = null) {
        $flag = $this->getOrderSyncEnabledFlag($store);
            return in_array($flag, array(
                \Klevu\Search\Model\System\Config\Source\Yesnoforced::YES,
                static::KLEVU_PRODUCT_FORCE_OLDERVERSION
            ));
    }

    /**
     * Return the configured frequency expression for Order Sync.
     *
     * @return string
     */
    public function getOrderSyncFrequency() {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ORDER_SYNC_FREQUENCY);
    }

    /**
     * Set the last Order Sync run time in System Configuration.
     *
     * @param DateTime|string $datetime If string is passed, it will be converted to DateTime.
     *
     * @return $this
     */
    public function setLastOrderSyncRun($datetime = "now") {
        if (!$datetime instanceof DateTime) {
            $datetime = new DateTime($datetime);
        }

        $this->setGlobalConfig(static::XML_PATH_ORDER_SYNC_LAST_RUN, $datetime->format(static::DATETIME_FORMAT));

        return $this;
    }

    /**
     * Check if default Magento log settings should be overridden to force logging for this module.
     *
     * @return bool
     */
    public function isLoggingForced() {
        return $this->_appConfigScopeConfigInterface->isSetFlag(static::XML_PATH_FORCE_LOG);
    }

    /**
     * Return the minimum log level configured. Default to \Zend\Log\Logger::WARN.
     *
     * @return int
     */
    public function getLogLevel() {
        $log_level = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_LOG_LEVEL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ($log_level !== null) ? intval($log_level) : \Zend\Log\Logger::INFO;
    }
	
    /**
     * @param $url
     * @param null $store
     * @param bool $test_mode
     * @return $this
     */
    public function setTiresUrl($url, $store = null, $test_mode = false) {
        $path = static::XML_PATH_UPGRADE_TIRES_URL;
        $this->setStoreConfig($path, $url, $store);
        return $this;
    }
    

	/**
     * @param null $store
     * @return string
     */
    public function getTiresUrl($store = null) {
        $url = $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_UPGRADE_TIRES_URL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store);

        return ($url) ? $url : \Klevu\Search\Helper\Api::ENDPOINT_DEFAULT_HOSTNAME;
    }


    /**
     * Return an multi-dimensional array of magento and klevu attributes that are mapped by default.
     * @return array
     */
    public function getDefaultMappedAttributes() {
        return array(
            "magento_attribute" => array(
                "name",
                "sku",
                "image",
                "description",
                "short_description",
                "price",
                "price",
                "tax_class_id",
                "weight",
                "rating"),
            "klevu_attribute" => array(
                "name",
                "sku",
                "image",
                "desc",
                "shortDesc",
                "price",
                "salePrice",
                "salePrice",
                "weight",
                "rating"
            )
        );
    }

    /**
     * Returns array of other attributes map from store configuration.
     *
     * @param \Magento\Framework\Model\Store|int|null $store
     * @return array
     */
    public function getOtherAttributesToIndex($store = null) {
        if ($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ATTRIBUTES_OTHER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store)) {
            return explode(",", $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ATTRIBUTES_OTHER,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store));
        }

        return array();
    }

    /**
     * Return the boosting attribute defined in store configuration.
     *
     * @param \Magento\Framework\Model\Store|int|null $store
     * @return array
     */
    public function getBoostingAttribute($store = null) {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_ATTRIBUTES_BOOSTING,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store);
    }

    /**
     * Set the global scope System Configuration value for the given key.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    protected function setGlobalConfig($key, $value) {
        $saveconfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Config\Model\ResourceModel\Config');
        $saveconfig->saveConfig($key, $value, "default",0);
		
		$config = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ReinitableConfigInterface');
        $config->reinit();
        return $this;
    }

    /**
     * Set the store scope System Configuration value for the given key.
     *
     * @param string                         $key
     * @param string                         $value
     * @param \Magento\Framework\Model\Store|int|null $store If not given, current store will be used.
     *
     * @return $this
     */
    public function setStoreConfig($key, $value, $store = null) {
        $config = $this->_appConfigScopeConfigInterface;

        $config = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Config\Model\ResourceModel\Config');
        $scope_id = $this->_storeModelStoreManagerInterface->getStore($store)->getId();
        if ($scope_id !== null) {
            $config->saveConfig($key, $value, "stores",$scope_id);
            $this->_resetConfig();
        }
        return $this;
    }
    
    /**
     * Clear config cache
     */
    protected function _resetConfig()
    {
        \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ReinitableConfigInterface')->reinit();
      
    }
    /**
     * Return the configuration flag for sync options.
     *
     *
     * @return int
     */
    public function getSyncOptionsFlag() {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_SYNC_OPTIONS);
    }
    
    /**
     * save sync option value
     *
     * @param string $value
     *
     * @return
     */
    public function saveSyncOptions($value) {
        $this->setGlobalConfig(static::XML_PATH_SYNC_OPTIONS, $value);
        return $this;
    }
    
    /**
     * save upgrade button value
     *
     * @param string $value
     *
     * @return
     */
    public function saveUpgradePremium($value) {
        $this->setGlobalConfig(static::XML_PATH_UPGRADE_PREMIUM, $value);
        return $this;
    }
    
    /**
     * save upgrade rating value
     *
     * @param string $value
     *
     * @return
     */
    public function saveRatingUpgradeFlag($value) {
        $this->setGlobalConfig(static::XML_PATH_RATING, $value);
        return $this;
    }
    
    /**
     * get upgrade rating value
     *
     * @return int 
     */
    public function getRatingUpgradeFlag() {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_RATING);
    }
	
	
    /**
     * get feature update
     *
     * @return bool 
     */
    public function getFeaturesUpdate($elemnetID) {

        try {
            if (!$this->_klevu_features_response) {
				$pro_sync = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Model\Product\Sync');
                $this->_klevu_features_response = $pro_sync->getFeatures();
            }
            $features = $this->_klevu_features_response;

            if(!empty($features) && !empty($features['disabled'])) {
                $checkStr = explode("_",$elemnetID);
                $disable_features =  explode(",",$features['disabled']);
                $code = $this->_frameworkAppRequestInterface->getParam('store');// store level
                $store = $this->_frameworkModelStore->load($code);

                if(in_array("preserves_layout", $disable_features) && $this->_frameworkAppRequestInterface->getParam('section')=="klevu_search") {
                    // when some upgrade plugin if default value set to 1 means preserve layout
                    // then convert to klevu template layout
                    if($this->_appConfigScopeConfigInterface->getValue(\Klevu\Search\Helper\Config::XML_PATH_LANDING_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store) == 1){
                        $this->setStoreConfig(\Klevu\Search\Helper\Config::XML_PATH_LANDING_ENABLED,2,$store);
                    }
                }


                if (in_array($checkStr[count($checkStr)-1], $disable_features)  && $this->_frameworkAppRequestInterface->getParam('section')=="klevu_search") {
                        $check = $checkStr[count($checkStr)-1];
                        if(!empty($check)) {
                            $configs = $this->_modelConfigData->getCollection()
                            ->addFieldToFilter('path', array("like" => '%/'.$check.'%'))->load();
                            $data = $configs->getData();
                            if(!empty($data)) {
                                $this->setStoreConfig($data[0]['path'],0,$store);
                            }
                            return $features;
                        }
                }
      
            }                
        } catch(Exception $e) {
                $this->_searchHelperData->log(\Zend\Logger::CRIT, sprintf("Error occured while getting features based on account %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage()));
        }
        return;
    }
    
    
    public function  executeFeatures($restApi,$store) {
        if(!$this->_klevu_enabled_feature_response) {
            $param =  array("restApiKey" => $restApi,"store" => $store->getId());
            $features_request = $this->_apiActionFeatures->execute($param);
            if($features_request->isSuccess() === true) {
                $this->_klevu_enabled_feature_response = $features_request->getData();
                $this->saveUpgradeFetaures(serialize($this->_klevu_enabled_feature_response),$store);
            } else {
                if(!empty($restApi)) {
                    $this->_klevu_enabled_feature_response = unserialize($this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_UPGRADE_FEATURES, $store));
                }
                $this->_searchHelperData->log(\Zend\Logger::INFO,sprintf("failed to fetch feature details (%s)",$features_request->getMessage()));
            }
        }  
        return $this->_klevu_enabled_feature_response;        
    }
	
	/**
     * Save the upgrade features defined in store configuration.
     *
     * @param \Magento\Framework\Model\Store|int|null $store
     */
    
    public function saveUpgradeFetaures($value,$store=null) {
        $this->setStoreConfig(static::XML_PATH_UPGRADE_FEATURES,$value,$store);
    }
	
    /**
     * Return the upgrade features defined in store configuration.
     *
     * @param \Magento\Framework\Model\Store|int|null $store
     * @return array
     */
    public function getUpgradeFetaures($store = null) {
        return $this->_appConfigScopeConfigInterface->getValue(static::XML_PATH_UPGRADE_FEATURES,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store);
    }
	
	

}

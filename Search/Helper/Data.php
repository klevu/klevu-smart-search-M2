<?php

namespace Klevu\Search\Helper;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Backend\Model\Url;
use \Klevu\Search\Helper\Config;
use \Psr\Log\LoggerInterface;
use \Magento\Catalog\Model\Product;


class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendModelUrl;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_psrLogLoggerInterface;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogModelProduct;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelperData;
    
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_modelEntityType;
    
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_modelEntityAttribute;
	
	/**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
	
	/**
     * @var Magento\Directory\Model\CurrencyFactory
     */
	protected $_currencyFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Magento\Backend\Model\Url $backendModelUrl, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Psr\Log\LoggerInterface $psrLogLoggerInterface, 
        \Magento\Catalog\Model\Product $catalogModelProduct, 
        \Magento\Catalog\Helper\Data $taxHelperData,
        \Magento\Eav\Model\Entity\Type $modelEntityType, 
        \Magento\Eav\Model\Entity\Attribute $modelEntityAttribute,
		\Magento\Directory\Model\CurrencyFactory $currencyFactory,
		\Magento\Framework\Locale\CurrencyInterface $localeCurrency)
    {
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_backendModelUrl = $backendModelUrl;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_psrLogLoggerInterface = $psrLogLoggerInterface;
        $this->_catalogModelProduct = $catalogModelProduct;
        $this->_taxHelperData = $taxHelperData;
        $this->_modelEntityType = $modelEntityType;
        $this->_modelEntityAttribute = $modelEntityAttribute;
		$this->_localeCurrency = $localeCurrency;
		$this->_currencyFactory = $currencyFactory;

    }


    const LOG_FILE = "Klevu_Search.log";

    const ID_SEPARATOR = "-";

    const SANITISE_STRING = "/:|,|;/";

    /**
     * Given a locale code, extract the language code from it
     * e.g. en_GB => en, fr_FR => fr
     *
     * @param string $locale
     */
    function getLanguageFromLocale($locale) {
        if (strlen($locale) == 5 && strpos($locale, "_") == 2) {
            return substr($locale, 0, 2);
        }

        return $locale;
    }

    /**
     * Return the language code for the given store.
     *
     * @param int|\Magento\Framework\Model\Store $store
     *
     * @return string
     */
    function getStoreLanguage($store = null) {
        if ($store = $this->_storeModelStoreManagerInterface->getStore($store)) {
            return $this->getLanguageFromLocale($store->getConfig(\Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE));
        }
    }
    
    /**
     * Return the timezone for the given store.
     *
     * @param int|\Magento\Framework\Model\Store $store
     *
     * @return string
     */
    function getStoreTimeZone($store = null) {
        if ($store = $this->_storeModelStoreManagerInterface->getStore($store)) {
            return $this->getLanguageFromLocale($store->getConfig(\Magento\Directory\Helper\Data::XML_PATH_DEFAULT_TIMEZONE));
        }
    }

    /**
     * Check if the given domain is considered to be a valid domain for a production environment.
     *
     * @param $domain
     *
     * @return bool
     */
    public function isProductionDomain($domain) {
        return preg_match("/\b(staging|dev|local)\b/", $domain) == 0;
    }

    /**
     * Generate a Klevu product ID for the given product.
     *
     * @param int      $product_id Magento ID of the product to generate a Klevu ID for.
     * @param null|int $parent_id  Optional Magento ID of the parent product.
     *
     * @return string
     */
    public function getKlevuProductId($product_id, $parent_id = 0) {
        if ($parent_id != 0) {
            $parent_id .= static::ID_SEPARATOR;
        } else {
            $parent_id = "";
        }

        return sprintf("%s%s", $parent_id, $product_id);
    }

    /**
     * Convert a Klevu product ID back into a Magento product ID. Returns an
     * array with "product_id" element for the product ID and a "parent_id"
     * element for the parent product ID or 0 if the Klevu product doesn't have
     * a parent.
     *
     * @param $klevu_id
     *
     * @return array
     */
    public function getMagentoProductId($klevu_id) {
        $parts = explode(static::ID_SEPARATOR, $klevu_id, 2);

        if (count($parts) > 1) {
            return array('product_id' => $parts[1], 'parent_id' => $parts[0]);
        } else {
            return array('product_id' => $parts[0], 'parent_id' => "0");
        }
    }

    /**
     * Format bytes into a human readable representation, e.g.
     * 6815744 => 6.5M
     *
     * @param     $bytes
     * @param int $precision
     *
     * @return string
     */
    public function bytesToHumanReadable($bytes, $precision = 2) {
        $suffixes = array("", "k", "M", "G", "T", "P");
        $base = log($bytes) / log(1024);
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    /**
     * Convert human readable formatting of bytes to bytes, e.g.
     * 6.5M => 6815744
     *
     * @param $string
     *
     * @return int
     */
    public function humanReadableToBytes($string) {
        $suffix = strtolower(substr($string, -1));
        $result = substr($string, 0, -1);

        switch ($suffix) {
            case 'g': // G is the max unit as of PHP 5.5.12
                $result *= 1024;
            case 'm':
                $result *= 1024;
            case 'k':
                $result *= 1024;
                break;
            default:
                $result = $string;
        }

        return ceil($result);
    }

    /**
     * Return the configuration data for a "Sync All Products" button displayed
     * on the Manage Products page in the backend.
     *
     * @return array
     */
    public function getSyncAllButtonData() {
        return array(
            'label'   => __("Sync All Products to Klevu"),
            'onclick' => sprintf("setLocation('%s')", $this->_backendModelUrl->getUrl("adminhtml/klevu_search/sync_all"))
        );
    }

    /**
     * Write a log message to the \Klevu\Search log file.
     *
     * @param int    $level
     * @param string $message
     */
    public function log($level, $message) {
        $config = $this->_searchHelperConfig;
        if ($level <= $config->getLogLevel()) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.Static::LOG_FILE);
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($message);
        }
    }

    /**
     * Remove the characters used to organise the other attribute values from the
     * passed in string.
     *
     * @param string $value
     * @return string
     */
    public function santiseAttributeValue($value) {
        if (is_array($value) && !empty($value)) {
            $sanitised_array = array();
            foreach($value as $item) {
                    if (is_array($item) || is_object($item)){
                    
                    } else {
                        $sanitised_array[] = preg_replace(self::SANITISE_STRING, " ", $item);
                    }
                   
            }
            return $sanitised_array;
        }
        return preg_replace(self::SANITISE_STRING, " ", $value);
    }

    /**
     Generate a Klevu product sku with parent product.
     *
     * @param string      $product_sku Magento Sku of the product to generate a Klevu sku for.
     * @param null $parent_sku  Optional Magento Parent Sku of the parent product.
     *
     * @return string
     */
    public function getKlevuProductSku($product_sku, $parent_sku = "") {
        if (!empty($parent_sku)) {
            $parent_sku .= static::ID_SEPARATOR;
        } else {
            $parent_sku = "";
        }
        return sprintf("%s%s", $parent_sku, $product_sku);
    }
    
    /**
     Get Original price for group product.
     *
     * @param object $product.
     *
     * @return
     */    
    public function getGroupProductOriginalPrice($product,$store){
        try {
            $groupProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $config = $this->_searchHelperConfig;
            $groupPrices = array();
            foreach ($groupProductIds as $ids) {
                foreach ($ids as $id) {
                    $groupProduct = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($id);
                    if($config->isTaxEnabled($store->getId())) {
                        $groupPrices[] = $this->_taxHelperData->getTaxPrice($groupProduct,$groupProduct->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), true, null, null, null, $store,false);
                    } else {
                        $groupPrices[] = $groupProduct->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
                    }
                }
            }
            asort($groupPrices);
            $product->setPrice(array_shift($groupPrices));
        } catch(\Exception $e) {
            $this->_searchHelperData->log(\Zend\Log\Logger::WARN, sprintf("Unable to get original group price for product id %s",$product->getId()));
        }            
    }
    
    
    /**
     Get Min price for group product.
     *
     * @param object $product.
     *
     * @return
     */    
    public function getGroupProductMinPrice($product,$store){
        $groupProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $config = $this->_searchHelperConfig;
        $groupPrices = array();
            foreach ($groupProductIds as $ids) {
                foreach ($ids as $id) {
                    $groupProduct = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($id);
                    if($config->isTaxEnabled($store->getId())) {
                        $groupPrices[] = $this->_taxHelperData->getTaxPrice($groupProduct, $groupProduct->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(), true, null, null, null, $store,false);
                    } else {
                        $groupPrices[] = $groupProduct->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
                    }
                }
            }
        asort($groupPrices);
        $product->setFinalPrice(array_shift($groupPrices));       
    }
    
    /**
     * Get Min price for group product.
     *
     * @param object $product.
     *
     * @return
     */    
    public function getBundleProductPrices($item,$store){
        $config = $this->_searchHelperConfig;
        if($config->isTaxEnabled($store->getId())) {
                return $item->getPriceModel()->getTotalPrices($item, null, true, false);
        } else {
                return $item->getPriceModel()->getTotalPrices($item, null, null, false);
        }
    }
    
    /**
     * Get the is active attribute id
     *
     * @return string
     */
    public function getIsActiveAttributeId(){
        $entity_type = $this->_modelEntityType->loadByCode("catalog_category");
        $entity_typeid = $entity_type->getId();
        $attributecollection = $this->_modelEntityAttribute->getCollection()->addFieldToFilter("entity_type_id", $entity_typeid)->addFieldToFilter("attribute_code", "is_active");
        $attribute = $attributecollection->getFirstItem();
        return $attribute->getAttributeId();
    }
    
    /**
     * Get the client ip
     *
     * @return string
     */
    public function getIp() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(!empty($_SERVER['HTTP_X_FORWARDED']))
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        else if(!empty($_SERVER['HTTP_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(!empty($_SERVER['HTTP_FORWARDED']))
            $ip = $_SERVER['HTTP_FORWARDED'];
        else if(!empty($_SERVER['REMOTE_ADDR']))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = 'UNKNOWN';
     
        return $ip;
    }
	
	
	/**
     * Get the currecy switcher data
     *
     * @return string
     */
	public function getCurrencyData($store) {
	    $baseCurrencyCode = $store->getBaseCurrency()->getCode();
		$currentCurrencyCode = $store->getCurrentCurrencyCode();
		if($baseCurrencyCode != $currentCurrencyCode){
	        $availableCurrencies = $store->getAvailableCurrencyCodes();
			$currencyResource = $this->_currencyFactory
            ->create()
            ->getResource();
            $currencyRates = $currencyResource->getCurrencyRates($baseCurrencyCode, array_values($availableCurrencies));
	        if(count($availableCurrencies) > 1) { 
                foreach($currencyRates as $key => &$value){
					$Symbol = $this->_localeCurrency->getCurrency($key)->getSymbol() ? $this->_localeCurrency->getCurrency($key)->getSymbol() : $this->_localeCurrency->getCurrency($key)->getShortName();
			        $value = sprintf("'%s':'%s:%s'", $key,$value,$Symbol);
		        }
		        $currency = implode(",",$currencyRates);
			    return $currency;
		    }
	    }
	}
}

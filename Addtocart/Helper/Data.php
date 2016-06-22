<?php
namespace Klevu\Addtocart\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface)
    {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;

    }

    const XML_PATH_ADDTOCART_ENABLED = "klevu_search/add_to_cart/enabledaddtocartfront";
    
    /**
     * Check if the add to cart is enabled in the system configuration for the current store.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isAddtocartEnabled($store_id = null) {
        return $this->_appConfigScopeConfigInterface->isSetFlag(static::XML_PATH_ADDTOCART_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store_id);
    }
}
	 
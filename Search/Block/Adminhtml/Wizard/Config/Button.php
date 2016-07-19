<?php
/**
 * Class \Klevu\Search\Block\Adminhtml\Wizard\Config\Button
 *
 * @method string getHtmlId()
 * @method string getWizardUrl()
 */
namespace Klevu\Search\Block\Adminhtml\Wizard\Config;

class Button extends \Magento\Config\Block\System\Config\Form\Field {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;
    
    protected $_template = 'klevu/search/wizard/config/button.phtml';
    
    public function __construct(\Magento\Backend\Block\Template\Context $context,
    \Klevu\Search\Helper\Config $searchHelperConfig)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_storeModelStoreManagerInterface = $context->getStoreManager();

        parent::__construct($context);
    }



    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
       
        // Only show the current scope hasn't been configured yet
        switch($element->getScope()) {
            case "stores":
                if ($this->hasApiKeys($element->getScopeId())) {
                    return "";
                }
                break;
            case "websites":
                $website = $this->_storeModelStoreManagerInterface->getWebsite($element->getScopeId());
                if ($this->hasApiKeys($website->getStores())) {
                    return "";
                }
                break;
            default:
                if ($this->hasApiKeys()) {
                    return "";
                }
        }
        
        if ($element->getScope() == "stores") {
            $this->setStoreId($element->getScopeId());
        }

        // Remove the scope information so it doesn't get printed out
        $element
            ->unsScope()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }
    
    
    

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $this->addData(array(
            'html_id' => $element->getHtmlId(),
            "button_label"    => "Start Wizard",
            'wizard_url' => $this->getUrl("klevu_search/wizard/user")
        ));

        return $this->_toHtml();
    }

    /**
     * Check if the given stores all have Klevu API keys. If no stores are given, checks
     * all configured stores.
     *
     * @param null $stores
     *
     * @return bool true if all stores have API keys, false otherwise.
     */
    protected function hasApiKeys($stores = null) {

        $config = $this->_searchHelperConfig;

        if ($stores === null) {
            $stores = $this->_storeModelStoreManagerInterface->getStores(false);
        }

        if (!is_array($stores)) {
            $stores = array($stores);
        }

        foreach ($stores as $store) {

            if (!$config->getJsApiKey($store) || !$config->getRestApiKey($store)) {
                return false;
            }
        }

        return true;
    }
}

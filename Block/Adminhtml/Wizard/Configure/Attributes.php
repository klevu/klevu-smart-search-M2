<?php

namespace Klevu\Search\Block\Adminhtml\Wizard\Configure;

class Attributes extends \Magento\Backend\Block\Template {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Model\Session $searchModelSession, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchModelSession = $searchModelSession;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;

        parent::__construct();
    }


    /**
     * Return the submit URL for the store configuration form.
     *
     * @return string
     */
    protected function getFormActionUrl() {
        return $this->getUrl("adminhtml/klevu_search_wizard/configure_attributes_post");
    }

    protected function getAttributeMappingsHtml() {
        $element = new \Magento\Framework\Data\Form\Element\Text(array(
            "name" => "attributes",
            "label" => __("Additional Attributes"),
            "comment" => __('Here you can set optional product attributes sent to Klevu by mapping them to your Magento attributes. If you specify multiple mappings for the same Klevu attribute, only the first mapping found on the product sent will be used, except for the "Other" attribute where all existing mappings are used.'),
            "tooltip" => "",
            "hint"    => "",
            "value"   => $this->_searchHelperConfig->getAdditionalAttributesMap($this->getStore()),
            "inherit" => false,
            "class"   => "",
            "can_use_default_value" => false,
            "can_use_website_value" => false
        ));
        $element->setForm(new \Magento\Framework\Data\Form());

        /** @var \Klevu\Search\Block\Adminhtml\Form\Field\Attribute\Mappings $renderer */
        $renderer = Mage::getBlockSingleton("klevu_search/adminhtml_form_field_attribute_mappings");
        $renderer->setTemplate("klevu/search/wizard/form/field/array.phtml");

        return $renderer->render($element);
    }

    /**
     * Return the Store model for the currently configured store.
     *
     * @return \Magento\Framework\Model\Store|null
     */
    protected function getStore() {
        if (!$this->hasData('store')) {
            $store_code = $this->_searchModelSession->getConfiguredStoreCode();

            $this->setData('store', $this->_storeModelStoreManagerInterface->getStore($store_code));
        }

        return $this->getData('store');
    }
}

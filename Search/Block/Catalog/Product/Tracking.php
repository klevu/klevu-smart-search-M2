<?php

namespace Klevu\Search\Block\Catalog\Product;

class Tracking extends \Magento\Framework\View\Element\Template {
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_magentoFrameworkRegistry;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    public function __construct(\Magento\Framework\Registry $magentoFrameworkRegistry, 
        \Klevu\Search\Helper\Config $searchHelperConfig)
    {
        $this->_magentoFrameworkRegistry = $magentoFrameworkRegistry;
        $this->_searchHelperConfig = $searchHelperConfig;

        parent::__construct();
    }


    /**
     * JSON of required tracking parameter for Klevu Product Click Tracking, based on current product
     * @return string
     * @throws Exception
     */
    public function getJsonTrackingData() {
        // Get the product
        $product = $this->_magentoFrameworkRegistry->registry('current_product');
        $api_key = $this->_searchHelperConfig->getJsApiKey();

            $product = array(
                'klevu_apiKey' => $api_key,
                'klevu_term'   => '',
                'klevu_type'   => 'clicked',
                'klevu_productId' => $product->getId(),
                'klevu_productName' => $product->getName(),
                'klevu_productUrl' => $product->getProductUrl(),
                'Klevu\typeOfRecord' => 'KLEVU_PRODUCT'
            );

        return json_encode($product);
    }
}

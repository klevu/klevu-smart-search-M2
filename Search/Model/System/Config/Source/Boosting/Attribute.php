<?php

namespace Klevu\Search\Model\System\Config\Source\Boosting;

class Attribute extends \Magento\Backend\Helper\Data {
   /**
     * Selected products for mass-update
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_products;

    /**
     * Array of same attributes for selected products
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $_attributes;

    /**
     * Excluded from batch update attribute codes
     *
     * @var string[]
     */
    protected $_excludedAttributes = ['url_key'];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productsFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;

    }

    /**
     * Return collection of same attributes for selected products without unique
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function toOptionArray()
    {
    
    
        $boost_option = [
            'value' => null,
            'label' => ''
        ];
        $options = [
            [
                'value' => null,
                'label' => '--- No Attribute Selected ---'
            ],
            $boost_option
        ];
        if ($this->_attributes === null) {
            $this->_attributes = $this->_eavConfig->getEntityType(
                \Magento\Catalog\Model\Product::ENTITY
            )->getAttributeCollection();

            if ($this->_excludedAttributes) {
                $this->_attributes->addFieldToFilter('attribute_code', ['nin' => $this->_excludedAttributes]);
            }


            foreach ($this->_attributes as $attribute) {
                
                $options[] =
                [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getAttributeCode()
                ];

            }
        }

        return $options;
    }

}

<?php
namespace Klevu\Search\Model\Catalog\Model;

class Config extends \Magento\Catalog\Model\Config
{
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperData = $searchHelperData;

        //parent::__construct();
    }

   /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        if (!$this->_searchHelperConfig->isExtensionConfigured() || !$this->_searchHelperData->isCatalogSearch()) {
            $options = array(
            'position'  => __('Position')
            );
            foreach ($this->getAttributesUsedForSortBy() as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
                $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
            }
        }else {
            $options = array(
            'position'  => __('Position'),
            'name' => __('Name'),
            'price' => __('Price'), 
            );
        
        }

        return $options;
    }
}

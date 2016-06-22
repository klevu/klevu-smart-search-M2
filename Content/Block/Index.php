<?php
namespace Klevu\Content\Block;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Klevu\Content\Helper\Data
     */
    protected $_contentHelperData;

    public function __construct(\Klevu\Content\Helper\Data $contentHelperData)
    {
        $this->_contentHelperData = $contentHelperData;

        parent::__construct();
    }

    /**
     * Get the Klevu other content
     * @return array
     */
    public function getCmsContent()
    {
        $collection = $this->_contentHelperData->getCmsData();
        return $collection;
    }
    /**
     * Return the Klevu other content filters
     * @return array
     */
    public function getContentFilters()

    {
        $filters = $this->_contentHelperData->getKlevuFilters();
        return $filters;
    }
}
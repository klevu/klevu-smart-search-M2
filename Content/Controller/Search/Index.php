<?php

namespace Klevu\Content\Controller\Search;

class Index extends \Klevu\Content\Controller\Search
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_magentoFrameworkUrlInterface;

    public function __construct(\Magento\Framework\UrlInterface $magentoFrameworkUrlInterface)
    {
        $this->_magentoFrameworkUrlInterface = $magentoFrameworkUrlInterface;

        parent::__construct();
    }

    public function execute()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle(__("Content Search"));
        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
        $breadcrumbs->addCrumb("home", array(
            "label" => __("Home") ,
            "title" => __("Home") ,
            "link" => $this->_magentoFrameworkUrlInterface->getBaseUrl()
        ));
        $breadcrumbs->addCrumb("titlename", array(
            "label" => __("Content Search") ,
            "title" => __("Content Search")
        ));
        $this->renderLayout();
    }
}

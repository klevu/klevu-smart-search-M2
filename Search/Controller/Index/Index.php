<?php

namespace Klevu\Search\Controller\Index;

class Index extends  \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_magentoFrameworkUrlInterface;

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {

        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;

    }

    public function execute() {
	$query = $this->getRequest()->getParam('q');		
        $this->_view->loadLayout();
	$this->_view->getPage()->getConfig()->getTitle()->set(__("Search results for: '%1'",$query));
        $this->_view->renderLayout();
    }
}

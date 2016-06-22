<?php
namespace Klevu\Content\Model\Observer;

 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Interceptor;

class ScheduleOtherContent implements ObserverInterface {

    /**
     * @var \Klevu\Content\Helper\Data
     */
    protected $_contentHelperData;

    /**
     * @var \Klevu\Content\Model\Content
     */
    protected $_contentModelContent;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendModelSession;

    public function __construct(\Klevu\Content\Helper\Data $contentHelperData, 
        \Klevu\Content\Model\Content $contentModelContent, 
        \Magento\Backend\Model\Session $backendModelSession)
    {
        $this->_contentHelperData = $contentHelperData;
        $this->_contentModelContent = $contentModelContent;
        $this->_backendModelSession = $backendModelSession;

    }

	/**
     * Run Other content based on event call.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->_contentModelContent->schedule();
    }
    
    
}
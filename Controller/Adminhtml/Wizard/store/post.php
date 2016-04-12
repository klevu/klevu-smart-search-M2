<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard\store;

class post extends \Magento\Backend\App\Action
{
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Klevu\Search\Helper\Api
     */
    protected $_searchHelperApi;

    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Klevu\Search\Model\Order\Sync
     */
    protected $_modelOrderSync;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Klevu\Search\Helper\Api $searchHelperApi, 
        \Magento\Backend\Model\Session $searchModelSession, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Klevu\Search\Model\Order\Sync $modelOrderSync)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperApi = $searchHelperApi;
        $this->_searchModelSession = $searchModelSession;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_modelProductSync = $modelProductSync;
        $this->_modelOrderSync = $modelOrderSync;

        parent::__construct($context);
    }

    public function execute() {

        $request = $this->getRequest();

        if (!$request->isPost() || !$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $config = $this->_searchHelperConfig;
        $api = $this->_searchHelperApi;
        $session = $this->_searchModelSession;
        $customer_id = $session->getConfiguredCustomerId();

        if (!$customer_id) {
            $this->messageManager->addError(__("You must configure a user first."));
            return $this->_redirect("*/*/configure_user");
        }

        $store_code = $request->getPost("store");
        if (strlen($store_code) == 0) {
            $this->messageManager->addError(__("Must select a store"));
            return $this->_forward("store");
        }

        try {
            $store = $this->_storeModelStoreManagerInterface->getStore($store_code);
        } catch (\Magento\Framework\Model\Store\Exception $e) {
            $this->messageManager->addError(__("Selected store does not exist."));
            return $this->_forward("store");
        }

        // Setup the live and test Webstores
        foreach (array(false) as $test_mode) {
            $result = $api->createWebstore($customer_id, $store, $test_mode);
            if ($result["success"]) {
                $config->setJsApiKey($result["webstore"]->getJsApiKey(), $store, $test_mode);
                $config->setRestApiKey($result["webstore"]->getRestApiKey(), $store, $test_mode);
                $config->setHostname($result["webstore"]->getHostedOn(), $store, $test_mode);
                $config->setCloudSearchUrl($result['webstore']->getCloudSearchUrl(), $store, $test_mode);
                $config->setAnalyticsUrl($result['webstore']->getAnalyticsUrl(), $store, $test_mode);
                $config->setJsUrl($result['webstore']->getJsUrl(), $store, $test_mode);
                $config->setRestHostname($result['webstore']->getRestHostname(), $store, $test_mode);
                if (isset($result["message"])) {
                    $this->messageManager->addSuccess(__($result["message"]));
                    $this->_searchModelSession->setFirstSync($store_code);
                }
            } else {
                $this->messageManager->addError(__($result["message"]));
                return $this->_forward("store");
            }
        }
        $this->messageManager->addSuccess("Store configured successfully. Saved API credentials.");

        $config->setTaxEnabledFlag($request->getPost("tax_enable"), $store);
        $config->setSecureUrlEnabledFlag($request->getPost("secureurl_setting"), $store);
        $this->_view->loadLayout();
        //$this->_view->initLayoutMessages("klevu_search/session");
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();

        // Clear Product Sync and Order Sync data for the newly configured store
        $this->_modelProductSync->clearAllProducts($store);
        //$this->_modelOrderSync->clearQueue($store);

        $session->setConfiguredStoreCode($store_code);

        $this->messageManager->addSuccess("Store configured successfully. Saved API credentials.");

        // Schedule a Product Sync
        $this->_modelProductSync->schedule();
       

    }
}

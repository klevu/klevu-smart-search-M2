<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard\userplan;

class post extends \Magento\Backend\App\Action
{
    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    /**
     * @var \Klevu\Search\Helper\Api
     */
    protected $_searchHelperApi;

    /**
     * @var \Magento\Framework\Model\Session
     */
    protected $_frameworkModelSession;

    public function __construct(\Magento\Backend\App\Action\Context $context,
        \Klevu\Search\Helper\Api $searchHelperApi, 
        \Magento\Backend\Model\Session $searchModelSession)
    {
        $this->_searchHelperApi = $searchHelperApi;
        $this->_searchModelSession = $searchModelSession;

        parent::__construct($context);
    }

    public function execute() {

        /* if partner account selected as UserPlan then change plan to trial*/
                $partnerAccount = false;
                $request = $this->getRequest();
                $session = $this->_searchModelSession;
                $userPlan = $request->getPost("userPlan");
                if($userPlan=="partnerAccount"){
                   $partnerAccount = true;
                }
                 
                if(empty($userPlan)) {
                    $this->messageManager->addError(__("Not sure, which plan to select? Select Premium to try all features free for 14-days."));
                    return $this->_forward("userplan");
                
                }
                
                $api = $this->_searchHelperApi;
                $result = $api->createUser(
                    $this->_searchModelSession->getKlevuNewEmail(),
                    $this->_searchModelSession->getKlevuNewPassword(),
                    $userPlan,
                    $partnerAccount,
                    $this->_searchModelSession->getKlevuNewUrl(),
                    $this->_searchModelSession->getMerchantEmail(),
                    $this->_searchModelSession->getContactNo()
                );
                
        if ($result["success"]) {
            $this->_searchModelSession->setConfiguredCustomerId($result["customer_id"]);
            if (isset($result["message"])) {
                $this->messageManager->addSuccess(__($result["message"]));
            }
            return $this->_forward("store");
        } else {
            $this->messageManager->addError(__($result["message"]));
            return $this->_forward("userplan");
        }
       
       
        return $this->_forward("store");
    }    
}

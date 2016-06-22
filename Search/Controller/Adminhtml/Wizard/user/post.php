<?php

namespace Klevu\Search\Controller\Adminhtml\Wizard\user;

class post extends \Magento\Backend\App\Action
{
    /**
     * @var \Klevu\Search\Helper\Api
     */
    protected $_searchHelperApi;

    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;
    
    public function __construct(\Magento\Backend\App\Action\Context $context,
        \Klevu\Search\Helper\Api $searchHelperApi, 
        \Magento\Backend\Model\Session $searchModelSession)
    {
        $this->_searchHelperApi = $searchHelperApi;
        $this->_searchModelSession = $searchModelSession;
        parent::__construct($context);
    }

    public function execute() {

        $request = $this->getRequest();

        if (!$request->isPost() || !$request->isAjax()) {
            return $this->_redirect('adminhtml/dashboard');
        }

        $api = $this->_searchHelperApi;
        $session = $this->_searchModelSession;
        $this->_searchModelSession->setHideStep("no"); 
        if ($request->getPost("klevu_existing_email")) {
            $result = $api->getUser(
                $request->getPost("klevu_existing_email"),
                $request->getPost("klevu_existing_password")
            );
            
            if ($result["success"]) {
                $this->_searchModelSession->setHideStep("yes");
                $this->_searchModelSession->setConfiguredCustomerId($result["customer_id"]);
                if (isset($result["message"])) {
                    $this->messageManager->addSuccess(__($result["message"]));
                }
                return $this->_forward("store");
            } else {
                $this->messageManager->addError(__($result["message"]));
                return $this->_forward("user");
            }
        } else {
            $termsconditions = $request->getPost("termsconditions");
            $klevu_new_email = $request->getPost("klevu_new_email");
            $klevu_new_password = $request->getPost("klevu_new_password");
            $userPlan = $request->getPost("userPlan");
            $partnerAccount = false;
            $klevu_new_url = $request->getPost("klevu_new_url");
            $merchantEmail = $request->getPost("merchantEmail");
            $contactNo = $request->getPost("countyCode")."-".$request->getPost("contactNo");
            $error = true;
            if(empty($klevu_new_email) || empty($klevu_new_password) || empty($klevu_new_url)
            || empty($merchantEmail) ) {
                $this->messageManager->addError(__("Missing details in the form. Please check."));
                return $this->_forward("user");
            } else if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i",$klevu_new_email)) {
                $this->messageManager->addError(__("Please enter valid Primary Email."));
                return $this->_forward("user");
            } else if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i",$merchantEmail)) {
                $this->messageManager->addError(__("Please enter valid Retailer Email."));
                return $this->_forward("user");
            }else if(empty($termsconditions)){
                $this->messageManager->addError(__("Please accept terms and conditions."));
                return $this->_forward("user");
            }else {
                   
                    $result = $api->checkUserDetail(
                        $request->getPost("klevu_new_email")
                    );

                    if ($result["success"]) {
                        $this->_searchModelSession->setTermsconditions($request->getPost("termsconditions"));
                        $this->_searchModelSession->setKlevuNewEmail($request->getPost("klevu_new_email"));
                        $this->_searchModelSession->setKlevuNewPassword($request->getPost("klevu_new_password"));
                        $this->_searchModelSession->setKlevuNewUrl($request->getPost("klevu_new_url"));
                        $this->_searchModelSession->setMerchantEmail($request->getPost("merchantEmail"));
                        $contactNo = $request->getPost("countyCode")."-".$request->getPost("contactNo");
                        $this->_searchModelSession->setContactNo($contactNo);
                        return $this->_forward("userplan");
                    } else {
                            $this->messageManager->addError(__($result["message"]));
                            return $this->_forward("user");
                    }
            }
        }

    }
}

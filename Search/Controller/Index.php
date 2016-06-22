<?php
/**
 * Klevu FrontEnd Controller
 */
namespace Klevu\Search\Controller;

class Index extends \Magento\Framework\App\Action\Action {
    

    public function execute() {
      
	    $this->loadLayout();  
        $query = $this->getRequest()->getParam('q');
        if(!empty($query)) {   
            $head = $this->getLayout()->getBlock('head');        
            $head->setTitle(__(sprintf("Search results for: '%s'",$query)));
        } else {
            $this->getLayout()->getBlock("head")->setTitle(__("Search"));
        }
	    if($breadcrumbs = $this->getLayout()->getBlock("breadcrumbs")) {
            $breadcrumbs->addCrumb("home", array(
                "label" => __("Home"),
                "title" => __("Home"),
                "link"  => $this->_magentoFrameworkUrlInterface->getBaseUrl()
		   ));

            $breadcrumbs->addCrumb("Search Result", array(
                "label" => __("Search Result"),
                "title" => __("Search Result")
		   ));
        }
        $this->renderLayout(); 
    }
}

<?php

namespace Klevu\Search\Block\Adminhtml\Wizard\Configure;

class Userplan extends \Magento\Backend\Block\Template {

    /**
     * Return the submit URL for the user configuration form.
     *
     * @return string
     */
    public function getFormActionUrl() {
        return $this->getUrl('klevu_search/wizard/userplan_post');
    }

    /**
     * Return the base URL for the store.
     *
     * @return string
     */
    public function getStoreUrl() {
        return $this->getBaseUrl();
    }
    

    
}

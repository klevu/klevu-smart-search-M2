<?php

namespace Klevu\Search\Block\Adminhtml\Wizard\Configure;

class Store extends \Magento\Backend\Block\Template {

    /**
     * Return the submit URL for the store configuration form.
     *
     * @return string
     */
    public function getFormActionUrl() {
        return $this->getUrl("klevu_search/wizard/store_post");
    }

    /**
     * Return the list of stores that can be selected to be configured (i.e. haven't
     * been configured already), organised by website name and group name.
     *
     * @return array
     */
    public function getStoreSelectData() {
        $stores = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface')->getStores(false);
        $config = \Magento\Framework\App\ObjectManager::getInstance()->get('\Klevu\Search\Helper\Config');

        $data = array();

        foreach ($stores as $store) {
            /** @var \Magento\Framework\Model\Store $store */
            if ($config->getJsApiKey($store) && $config->getRestApiKey($store)) {
                // Skip already configured stores
                continue;
            }

            $website = $store->getWebsite()->getName();
            $group = $store->getGroup()->getName();

            if (!isset($data[$website])) {
                $data[$website] = array();
            }
            if (!isset($data[$website][$group])) {
                $data[$website][$group] = array();
            }

            $data[$website][$group][] = $store;
        }
        return $data;
    }
}

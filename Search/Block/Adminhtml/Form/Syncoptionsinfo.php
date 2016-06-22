<?php

namespace Klevu\Search\Block\Adminhtml\Form;
class Syncoptionsinfo extends \Magento\Config\Block\System\Config\Form\Field {

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        if($this->getSyncOptionsSelected() ==1) {
            $mode = __("Updates only (syncs data immediately)");
        } else {
            $mode = __("All data (syncs data on CRON execution");
        }
        return $mode;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $this->setData('scope', $element->getScope());

        // Remove the inheritance checkbox
        $element
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }



    public function getSyncOptionsSelected() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Helper\Config')->getSyncOptionsFlag();
    }

}

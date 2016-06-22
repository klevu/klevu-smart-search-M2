<?php

namespace Klevu\Search\Block\Adminhtml\Form\Field\Store\Level;

class Label extends \Magento\Config\Block\System\Config\Form\Field {

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        if ($element->getScope() == "stores") {
            return $element->getEscapedValue();
        } else {
            return __("Switch to store scope to set");
        }
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $this->setData('scope', $element->getScope());

        // Remove the inheritance checkbox
        $element
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }
}

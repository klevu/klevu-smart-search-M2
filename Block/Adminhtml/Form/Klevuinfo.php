<?php

namespace Klevu\Search\Block\Adminhtml\Form;

class Klevuinfo extends \Magento\Config\Block\System\Config\Form\Fieldset {

    protected function _construct() {
        parent::_construct();

        if (!$this->getTemplate()) {
            // Set the default template
            $this->setTemplate("klevu/search/form/klevuinfo.phtml");
        }
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $html = $this->_getHeaderHtml($element);

        $html .= $this->_toHtml();

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    public function getVersion() {
        return Mage::getConfig()->getModuleConfig('Klevu\Search')->version;
    }

}

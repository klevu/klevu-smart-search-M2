<?php

/**
 * Class \Klevu\Search\Block\Adminhtml\Form\Field\Sync\Button
 *
 * @method setStoreId($id)
 * @method string getStoreId()
 */
namespace Klevu\Search\Block\Adminhtml\Form\Field\Sync;

class Button extends \Magento\Config\Block\System\Config\Form\Field {
    
    protected $_template = 'klevu/search/form/field/sync/button.phtml';



    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        if ($element->getScope() == "stores") {
            $this->setStoreId($element->getScopeId());
        }

        // Remove the scope information so it doesn't get printed out
        $element
            ->unsScope()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $url_params = ($this->getStoreId()) ? array("store" => $this->getStoreId()) : array();
        $label_suffix = ($this->getStoreId()) ? " for This Store" : "";

        $this->addData(array(
            "html_id"         => $element->getHtmlId(),
            "button_label"    => sprintf("Sync Data %s", $label_suffix),
            "destination_url" => $this->getUrl("klevu_search/sync/all", $url_params)
        ));

        return $this->_toHtml();
    }
}

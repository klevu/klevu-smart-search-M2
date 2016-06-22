<?php

/**
 * Class \Klevu\Search\Block\Adminhtml\Form\Field\Image\Button
 *
 * @method setStoreId($id)
 * @method string getStoreId()
 */
 
namespace Klevu\Search\Block\Adminhtml\Form\Field\Image;

class Log extends \Magento\Config\Block\System\Config\Form\Field {

    protected function _prepareLayout() {
        parent::_prepareLayout();

        // Set the default template
        if (!$this->getTemplate()) {
            $this->setTemplate('klevu/search/form/field/sync/logbutton.phtml');
        }

        return $this;
    }

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
        $url_params = array("debug" => "klevu");
        $label_suffix = ($this->getStoreId()) ? " for This Store" : "";

        $this->addData(array(
            "html_id"         => $element->getHtmlId(),
            "button_label"    => sprintf("Send Log"),
            "destination_url" => $this->getUrl("search/index/runexternaly", $url_params)
        ));

        return $this->_toHtml();
    }
}

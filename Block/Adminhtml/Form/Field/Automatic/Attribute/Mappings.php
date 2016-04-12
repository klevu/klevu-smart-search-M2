<?php

namespace Klevu\Search\Block\Adminhtml\Form\Field\Automatic\Attribute;

class Mappings extends \Magento\Config\Block\System\Config\Form\Field\Array\AbstractArray {

    /**
     * Check if columns are defined, set template
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('klevu/search/form/field/array_readonly.phtml');
    }

    protected function _prepareToRender() {
        $this->addColumn("klevu_attribute", array(
            'label'    => __("Klevu Attribute")
        ));
        $this->addColumn("magento_attribute", array(
            'label'    => __("Magento Attribute")
        ));
    }
}

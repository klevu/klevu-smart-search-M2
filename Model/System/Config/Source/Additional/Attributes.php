<?php

namespace Klevu\Search\Model\System\Config\Source\Additional;

class Attributes {
    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_searchHelperData = $searchHelperData;

        parent::__construct();
    }


    public function toOptionArray() {
        $helper = $this->_searchHelperData;

        return array(
            array('value' => "brand", 'label' => $helper->__("Brand")),
            array('value' => "model", 'label' => $helper->__("Model")),
            array('value' => "color", 'label' => $helper->__("Color")),
            array('value' => "size" , 'label' => $helper->__("Size"))
        );
    }
}

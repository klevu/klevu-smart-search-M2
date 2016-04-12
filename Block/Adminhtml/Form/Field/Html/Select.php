<?php

namespace Klevu\Search\Block\Adminhtml\Form\Field\Html;

class Select extends \Magento\Backend\Block\Html\Select {

    public function setInputName($value) {
        return $this->setName($value);
    }
}

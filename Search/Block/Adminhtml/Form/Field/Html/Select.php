<?php

namespace Klevu\Search\Block\Adminhtml\Form\Field\Html;

class Select extends \Magento\Framework\View\Element\Html\Select {

    public function setInputName($value) {
        return $this->setName($value);
    }
}

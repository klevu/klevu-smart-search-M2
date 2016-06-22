<?php
namespace Klevu\Content\Block\Adminhtml\Form\System\Config\Field;

class Select extends \Magento\Framework\View\Element\Html\Select
{
    protected function _toHtml()
    {
        $this->setName($this->getInputName());
        $this->setClass('select');
        return trim(preg_replace('/\s+/', ' ',parent::_toHtml()));
    }
}

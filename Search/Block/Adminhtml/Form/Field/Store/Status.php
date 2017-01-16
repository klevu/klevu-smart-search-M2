<?php
namespace Klevu\Search\Block\Adminhtml\Form\Field\Store;

	class Status extends \Magento\Config\Block\System\Config\Form\Field {

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
            $status = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Model\Product\Sync')->getKlevuCronStatus();
            if(!empty($status)) {
                $html = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Model\Product\Sync')->getKlevuCronStatus();
            }else {
                $html = __("-");
            }
            return $html;
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

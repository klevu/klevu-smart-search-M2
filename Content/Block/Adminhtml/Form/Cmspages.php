<?php
namespace Klevu\Content\Block\Adminhtml\Form;


class Cmspages extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
	
	/**
     * @var Customerpage
     */
    protected $_pageRenderer;

    /**
     * Retrieve page column renderer
     *
     * @return Customerpage
     */
    protected function _getpageRenderer()
    {
	    
		
	    $this->_cmsModelPage = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Cms\Model\Page');
	    $cms_pages = $this->_cmsModelPage->getCollection()->addFieldToSelect(array("page_id","title"))->addFieldToFilter('is_active',1);
        $page_ids = $cms_pages->getData();
        foreach ($page_ids as $id) {
            $cmsOptions[$id['page_id']] = $id['title'];
        }
        if (!$this->_pageRenderer) {
            $this->_pageRenderer = $this->getLayout()->createBlock(
                'Klevu\Content\Block\Adminhtml\Form\System\Config\Field\Select',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_pageRenderer->setClass('customer_page_select');
			
			$this->_pageRenderer->setOptions($cmsOptions);
            $this->_pageRenderer->setExtraParams('style="width:200px;"');
        }
        return $this->_pageRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
		
		$this->addColumn('cmspages', array(
            'label' => __('CMS Pages'),
            'renderer'=> $this->_getpageRenderer(),
        ));
		
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Exclude CMS Pages');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getpageRenderer()->calcOptionHash($row->getCmspages())] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
	
}

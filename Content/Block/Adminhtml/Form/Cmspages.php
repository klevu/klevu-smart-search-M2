<?php
namespace Klevu\Content\Block\Adminhtml\Form;

class Cmspages extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsModelPage;

    protected $selectPages = array();
    
    public function __construct(\Magento\Cms\Model\Page $cmsModelPage)
    {
        $this->_cmsModelPage = $cmsModelPage;

        $this->addColumn('cmspages', array(
            'label' => __('CMS Pages'),
            'renderer'=> $this->getRenderer('cmspages'),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Exclude CMS Pages');
        parent::__construct();
        $this->setTemplate('klevu/search/system/config/form/field/array.phtml');

    }
    /**
     * Get all pages in the store.
     *
     * @param int $columnId.
     *
     * @return $selectPages
     */
    protected function getRenderer($columnId) {
        if (!array_key_exists($columnId, $this->selectPages) || !$this->selectPages[$columnId]) {
            $cmsOptions = array();
            switch($columnId) {
                case 'cmspages':
                    $cms_pages = $this->_cmsModelPage->getCollection()->addFieldToSelect(array("page_id","title"))->addFieldToFilter('is_active',1);
                    $page_ids = $cms_pages->getData();
                    foreach ($page_ids as $id) {
                        $cmsOptions[$id['page_id']] = $id['title'];
                    }
                    break;
                default:
            }
            $selectPage = Mage::app()->getLayout()->createBlock('content/adminhtml_form_system_config_field_select')->setIsRenderToJsTemplate(true);
            $selectPage->setOptions($cmsOptions);
            $selectPage->setExtraParams('style="width:200px;"');
            $this->selectPages[$columnId] = $selectPage;
        }

        return $this->selectPages[$columnId];
    }
    
    protected function _prepareArrayRow(\Magento\Framework\Object $row)
    {
        $row->setData('option_extra_attr_' . $this->getRenderer('cmspages')->calcOptionHash($row->getCmspages()),
            'selected="selected"'
        );
    }
}

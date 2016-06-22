<?php

namespace Klevu\Search\Block\Adminhtml\Form\Field\Attribute;

class Mappings extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {
    /**
     * @var \Klevu\Search\Model\System\Config\Source\Additional\Attributes
     */
    protected $_sourceAdditionalAttributes;

    /**
     * @var \Klevu\Search\Model\System\Config\Source\Product\Attributes
     */
    protected $_sourceProductAttributes;

    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_frameworkAppRequestInterface;

    public function __construct(\Klevu\Search\Model\System\Config\Source\Additional\Attributes $sourceAdditionalAttributes, 
        \Klevu\Search\Model\System\Config\Source\Product\Attributes $sourceProductAttributes, 
        \Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Framework\App\RequestInterface $frameworkAppRequestInterface)
    {
        $this->_sourceAdditionalAttributes = $sourceAdditionalAttributes;
        $this->_sourceProductAttributes = $sourceProductAttributes;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_frameworkAppRequestInterface = $frameworkAppRequestInterface;

        parent::__construct();
    }


    protected $klevu_attribute_renderer;

    protected $magento_attribute_renderer;

    protected function _prepareToRender() {
        $this->addColumn("klevu_attribute", array(
            'label'    => __("Klevu Attribute"),
            'renderer' => $this->getKlevuAttributeRenderer()
        ));
        $this->addColumn("magento_attribute", array(
            'label'    => __("Magento Attribute"),
            'renderer' => $this->getMagentoAttributeRenderer()
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = __("Add Mapping");
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $row->setData(
            'option_extra_attr_' . $this->getKlevuAttributeRenderer()->calcOptionHash($row->getData('klevu_attribute')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->getMagentoAttributeRenderer()->calcOptionHash($row->getData('magento_attribute')),
            'selected="selected"'
        );
    }

    /**
     * Return a block to render the select element for Klevu Attribute.
     *
     * @return \Klevu\Search\Block\Adminhtml\Form\Field\Html\Select
     */
    protected function getKlevuAttributeRenderer() {
        if (!$this->klevu_attribute_renderer) {
            /** @var \Magento\Framework\Block\Html\Select $renderer */
            $renderer = $this->getLayout()->createBlock('klevu_search/adminhtml_form_field_html_select', '', array(
                'is_render_to_js_template' => true
            ));
            $renderer->setOptions($this->_sourceAdditionalAttributes->toOptionArray());
            $renderer->setExtraParams('style="width:120px"');

            $this->klevu_attribute_renderer = $renderer;
        }

        return $this->klevu_attribute_renderer;
    }

    /**
     * Return a block to render the select element for Magento Attribute.
     *
     * @return \Klevu\Search\Block\Adminhtml\Form\Field\Html\Select
     */
    protected function getMagentoAttributeRenderer() {
        if (!$this->magento_attribute_renderer) {
            /** @var \Magento\Framework\Block\Html\Select $renderer */
            $renderer = $this->getLayout()->createBlock('klevu_search/adminhtml_form_field_html_select', '', array(
                'is_render_to_js_template' => true
            ));
            $renderer->setOptions($this->getOptions());
            $renderer->setExtraParams('style="width:120px"');

            $this->magento_attribute_renderer = $renderer;
        }

        return $this->magento_attribute_renderer;
    }

    /**
     * Get the options from our product attribute source model, and filter out the search attributes.
     * @return array
     */
    protected function getOptions() {
        $options_with_search_filters = $this->_sourceProductAttributes->toOptionArray();
        $search_attributes_map = $this->_searchHelperConfig->getAutomaticAttributesMap($this->_frameworkAppRequestInterface->getParam('store'));
        $options = array();

        // Flatten the search_attributes_map
        $search_attributes = array();
        foreach($search_attributes_map as $attribute) {
            $search_attributes[] = $attribute['magento_attribute'];
        }

        // We only want options that are not in the search_attributes array.
        foreach($options_with_search_filters as $option) {
            if(!in_array($option['value'], $search_attributes)) {
                $options[] = $option;
            }
        }

        return $options;
    }
}

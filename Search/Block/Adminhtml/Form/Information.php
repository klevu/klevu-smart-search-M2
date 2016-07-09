<?php

namespace Klevu\Search\Block\Adminhtml\Form;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset {

    protected $_template = 'klevu/search/form/information.phtml';


    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
		$html .= $this->_toHtml();
		$proSync = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Model\Product\Sync');
		$check_plan = $proSync->getFeatures();

		
		if(empty($check_plan["errors"]) && !empty($check_plan)) { 
			$html .='<p>';
			if(!empty($check_plan['user_plan_for_store'])) {
					$html .= '<b>My Current Plan: </b>';         
					$html .= ucfirst($check_plan['user_plan_for_store']); 
			}
			if(!empty($check_plan['upgrade_label'])) { 
				 $html .= "  <button type='button' onClick=upgradeLink('".$check_plan["upgrade_url"]."')>".$check_plan['upgrade_label']."</button>
				 &nbsp;&nbsp;<a href='#' onClick='compareplan();'>Compare Plans</a>";
			}  
			$html .= '</p>';
	    } 
		$html .= '<p><b>Prerequisites:</b><br>
		  1. Ensure cron is running <br>
		  2. Indices are uptodate (System &gt; Index Management)<br>
		  3. Products should be enabled and have the visibility set to catalog and search</p>';
        $html .= $this->_getFooterHtml($element);
        return $html;
    }



}

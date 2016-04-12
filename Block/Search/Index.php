<?php
/**
 * Copyright © 2015 Dd . All rights reserved.
 */
namespace Klevu\Search\Block\Search;
class Index extends \Magento\Framework\View\Element\Template
{
	public function _prepareLayout()
	{
	   $query = $this->getRequest()->getParam('q');
	   //set page title
	   $this->pageConfig->getTitle()->set(__(sprintf("Search results for: '%s'",$query)));
	   return parent::_prepareLayout();
	}  
	
}

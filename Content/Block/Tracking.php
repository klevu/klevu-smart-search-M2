<?php
namespace Klevu\Content\Block;

class Tracking extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsModelPage;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig, 
        \Magento\Cms\Model\Page $cmsModelPage)
    {
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_cmsModelPage = $cmsModelPage;

        parent::__construct();
    }

    /**
     * JSON of required tracking parameter for Klevu Product Click Tracking, based on current product
     * @return string
     * @throws Exception
     */
    public function getJsonTrackingData() {
    
        $api_key = $this->_searchHelperConfig->getJsApiKey();
        // Get current Cms page object
        $page = $this->_cmsModelPage;
        if ($page->getId()) {
            $content = array(
                'klevu_apiKey' => $api_key,
                'klevu_term'   => '',
                'klevu_type'   => 'clicked',
                'klevu_productId' => $page->getPageId(),
                'klevu_productName' => $page->getTitle(),
                'klevu_productUrl' => $page->getIdentifier(),
                'Klevu\typeOfRecord' => 'KLEVU_CMS'
            );
            return json_encode($content);
        }
    }
}
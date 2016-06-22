<?php

/**
 * Class \Klevu\Search\Model\Observer
 *
 * @method setIsProductSyncScheduled($flag)
 * @method bool getIsProductSyncScheduled()
 */
namespace Klevu\Search\Model\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Interceptor;
use Magento\Framework\Filesystem\DriverPool\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
class CreateThumb implements ObserverInterface{

    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_magentoFrameworkFilesystem;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;


    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $_modelProductAction;

    public function __construct(
        \Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_searchHelperData = $searchHelperData;
        //parent::__construct();
    }


 
    /**
     * When product image updated from admin this will generate the image thumb.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $image = $observer->getEvent()->getProduct()->getImage();
        if(($image != "no_selection") && (!empty($image))) {
            try {
            
                $dir = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Filesystem\DirectoryList');  
                $mediadir = $dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                $imageResized = $mediadir.DIRECTORY_SEPARATOR."klevu_images".$image;
                $baseImageUrl = $mediadir.DIRECTORY_SEPARATOR."catalog".DIRECTORY_SEPARATOR."product".$image;

                if(file_exists($baseImageUrl)) {
                    list($width, $height, $type, $attr)= getimagesize($baseImageUrl); 
                    if($width > 200 && $height > 200) {
                            if(file_exists($imageResized)) {
                                if (!unlink( $mediadir.'/klevu_images'. $image))
                                {
                                    $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Image Deleting Error:\n%s", $image));  
                                }
                            }
                            $this->_modelProductSync->thumbImageObj($baseImageUrl,$imageResized);
                    }
                }
            } catch(Exception $e) {
                 $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Image Error:\n%s", $e->getMessage()));
            }
        }
    }
    
}
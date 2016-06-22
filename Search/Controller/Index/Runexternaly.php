<?php

namespace Klevu\Search\Controller\Index;

class Runexternaly extends \Klevu\Search\Controller\Index
{
    /**
     * @var \Klevu\Search\Model\Product\Sync
     */
    protected $_modelProductSync;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_magentoFrameworkFilesystem;

    /**
     * @var \Klevu\Search\Model\Api\Action\Debuginfo
     */
    protected $_apiActionDebuginfo;

    /**
     * @var \Magento\Framework\Model\Session
     */
    protected $_frameworkModelSession;

    /**
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexModelIndexer;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Model\Product\Sync $modelProductSync, 
        \Magento\Framework\Filesystem $magentoFrameworkFilesystem, 
        \Klevu\Search\Model\Api\Action\Debuginfo $apiActionDebuginfo, 
        \Magento\Framework\Model\Session $frameworkModelSession, 
        \Magento\Index\Model\Indexer $indexModelIndexer, 
        \Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_modelProductSync = $modelProductSync;
        $this->_magentoFrameworkFilesystem = $magentoFrameworkFilesystem;
        $this->_apiActionDebuginfo = $apiActionDebuginfo;
        $this->_frameworkModelSession = $frameworkModelSession;
        $this->_indexModelIndexer = $indexModelIndexer;
        $this->_searchHelperData = $searchHelperData;

        parent::__construct();
    }

    public function execute(){
        try {
                $debugapi = $this->_modelProductSync->getApiDebug();
                $content="";
                if($this->getRequest()->getParam('debug') == "klevu") {
                    // get last 500 lines from klevu log 
                    $path = $this->_magentoFrameworkFilesystem->getDirPath("log")."/\Klevu\Search.log";
                    if($this->getRequest()->getParam('lines')) {
                        $line = $this->getRequest()->getParam('lines'); 
                    }else {
                        $line = 100;
                    }
                    $content.= $this->getLastlines($path,$line,true);
                   
                    //send php and magento version
                    $content.= "</br>".'****Current Magento version on store:'.Mage::getVersion()."</br>";
                    $content.= "</br>".'****Current PHP version on store:'. phpversion()."</br>";
                    
                    //send cron and  logfile data
                    $cron = $this->_magentoFrameworkFilesystem->getDirPath()."/cron.php";
                    $cronfile = file_get_contents($cron);
                    $content.= nl2br(htmlspecialchars($content)).nl2br(htmlspecialchars($cronfile));
                    $response = $this->_apiActionDebuginfo->debugKlevu(array('apiKey'=>$debugapi,'klevuLog'=>$content,'type'=>'log_file'));
                    if($response->getMessage()=="success") {
                        $this->_frameworkModelSession->addSuccess("Klevu search log sent.");
                    }
                    
                    $content =  serialize($this->_modelProductSync->debugsIds());
                    $response = $this->_apiActionDebuginfo->debugKlevu(array('apiKey'=>$debugapi,'klevuLog'=>$content,'type'=>'product_table'));
                    
                    if($response->getMessage()=="success") {
                        $this->_frameworkModelSession->addSuccess("Status of indexing queue sent.");
                    }else {
                        $this->_frameworkModelSession->addSuccess($response->getMessage());
                    }
                    //send index status data
                    $content ="";
                    $allIndex= $this->_indexModelIndexer->getProcessesCollection();
                    foreach ($allIndex as $index) {
                        $content .= $index->getIndexerCode().":".$index->getStatus().'<br>';
                    }
                    $response = $this->_apiActionDebuginfo->debugKlevu(array('apiKey'=>$debugapi,'klevuLog'=>$content,'type'=>'index'));
                    if($response->getMessage()=="success") {
                        $this->_frameworkModelSession->addSuccess("Status of magento indices sent.");
                    }else {
                        $this->_frameworkModelSession->addSuccess($response->getMessage());
                    }
                    $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("klevu debug data was sent to klevu server successfully."));
                }
                $rest_api = $this->getRequest()->getParam('api');
                if(!empty($rest_api)) {
                    $this->_modelProductSync->sheduleCronExteranally($rest_api);
                    $this->_frameworkModelSession->addSuccess("Cron scheduled externally."); 
                }
                $this->_redirect('search/index/runexternalylog');
                
        }
        catch(Exception $e) {
              $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Product Synchronization was Run externally:\n%s", $e->getMessage()));
        }
    }
    
    function getLastlines($filepath, $lines, $adaptive = true) {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) return false;
        // Sets buffer size
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;
        // Start reading
        $output = '';
        $chunk = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
        // Figure out how far back we should jump
        $seek = min(ftell($f), $buffer);
        // Do the jump (backwards, relative to where we are)
        fseek($f, -$seek, SEEK_CUR);
        // Read a chunk and prepend it to our output
        $output = ($chunk = fread($f, $seek)) . $output;
        // Jump back to where we started reading
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        // Decrease our line counter
        $lines -= substr_count($chunk, "\n");
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
        // Find first newline and remove all text before that
        $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        fclose($f);
        return trim($output);
    }
}

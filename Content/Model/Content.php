<?php
namespace Klevu\Content\Model;

use \Klevu\Search\Model\Sync;
use \Klevu\Search\Helper\Config;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Klevu\Search\Model\Api\Action\Startsession;
use \Klevu\Search\Model\Api\Action\Deleterecords;
use \Klevu\Search\Model\Api\Action\Updaterecords;

class Content extends \Klevu\Search\Model\Product\Sync
{
    /**
     * @var \Magento\Framework\Model\Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var \Klevu\Search\Model\Session
     */
    protected $_searchModelSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * @var \Klevu\Content\Helper\Data
     */
    protected $_contentHelperData;

    /**
     * @var \Klevu\Search\Model\Api\Action\Deleterecords
     */
    protected $_apiActionDeleterecords;

    /**
     * @var \Klevu\Search\Model\Api\Action\Addrecords
     */
    protected $_apiActionAddrecords;

    /**
     * @var \Klevu\Search\Helper\Compat
     */
    protected $_searchHelperCompat;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsModelPage;

    /**
     * @var \Klevu\Search\Model\Api\Action\Updaterecords
     */
    protected $_apiActionUpdaterecords;

    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;
	
	/**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;
	
	
	/**
     * @var \Klevu\Search\Model\Api\Action\Startsession
     */
    protected $_apiActionStartsession;
	
	/**
     * @var \Magento\Cron\Model\Schedule
     */
    protected $_cronModelSchedule;

    public function __construct(\Magento\Framework\App\ResourceConnection $frameworkModelResource, 
        \Magento\Backend\Model\Session $searchModelSession, 
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface, 
        \Klevu\Content\Helper\Data $contentHelperData, 
        \Klevu\Search\Model\Api\Action\Deleterecords $apiActionDeleterecords, 
        \Klevu\Search\Model\Api\Action\Addrecords $apiActionAddrecords, 
        \Klevu\Search\Helper\Compat $searchHelperCompat, 
        \Magento\Cms\Model\Page $cmsModelPage, 
        \Klevu\Search\Model\Api\Action\Updaterecords $apiActionUpdaterecords, 
        \Klevu\Search\Helper\Data $searchHelperData,
		\Klevu\Search\Helper\Config $searchHelperConfig,
		\Klevu\Search\Model\Api\Action\Startsession $apiActionStartsession,
		\Magento\Cron\Model\Schedule $cronModelSchedule,
		\Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
		)
    {
        $this->_frameworkModelResource = $frameworkModelResource;
        $this->_searchModelSession = $searchModelSession;
        $this->_storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->_contentHelperData = $contentHelperData;
        $this->_apiActionDeleterecords = $apiActionDeleterecords;
        $this->_apiActionAddrecords = $apiActionAddrecords;
        $this->_searchHelperCompat = $searchHelperCompat;
        $this->_cmsModelPage = $cmsModelPage;
        $this->_apiActionUpdaterecords = $apiActionUpdaterecords;
        $this->_searchHelperData = $searchHelperData;
		$this->_searchHelperConfig = $searchHelperConfig;
		$this->_apiActionStartsession = $apiActionStartsession;
		$this->_cronModelSchedule = $cronModelSchedule;
		$this->_ProductMetadataInterface = $productMetadataInterface;
		if($this->_ProductMetadataInterface->getEdition() == "Enterprise" && version_compare($this->_ProductMetadataInterface->getVersion(), '2.0.8', '>')===true) {
			$this->_page_value = "row_id";
		} else {
			$this->_page_value = "page_id";
		}


    }

    public function _construct() {
        parent::_construct();

        $this->addData(array(
            "connection" => $this->_frameworkModelResource->getConnection("core_write")
        ));
    }

    public function getJobCode() {
        return "klevu_search_content_sync";
    }

    /**
     * Perform Content Sync on any configured stores, adding new content, updating modified and
     * deleting removed content since last sync.
     */
    public function run(){
  
        // Sync Data only for selected store from config wizard
        $session = $this->_searchModelSession;
        $firstSync = $session->getFirstSync();
        if(!empty($firstSync)){
            $onestore = $this->_storeModelStoreManagerInterface->getStore($firstSync);
            $this->reset();
            if (!$this->_contentHelperData->isCmsSyncEnabled($onestore->getId())) {
                return;
            }
            if (!$this->setupSession($onestore)) {
                return;
            }
            $this->syncCmsData($onestore);
            return;
        }
        
        if ($this->isRunning(2)) {
            // Stop if another copy is already running
            $this->log(\Zend\Log\Logger::INFO, "Stopping because another copy is already running.");
            return;
        }
        
        // Sync all store cms Data 
        $stores = $this->_storeModelStoreManagerInterface->getStores();
        foreach($stores as $store) {
            /** @var \Magento\Framework\Model\Store $store */
            $this->reset();
            if (!$this->_contentHelperData->isCmsSyncEnabled($store->getId())) {
                continue;
            }
            if (!$this->setupSession($store)) {
                continue;
            }
            $this->syncCmsData($store);
        }

    }
    
    public function syncCmsData($store){
    
            if ($this->rescheduleIfOutOfMemory()) {
                return;
            }

            $cPgaes = $this->_contentHelperData->getExcludedPages($store);

            if(count($cPgaes) > 0) {
                foreach($cPgaes as $key => $cvalue){
                    $pageids[]  = intval($cvalue['cmspages']);
                }
            } else {
                $pageids = "";
            }
            
            if(!empty($pageids)){
                $eids = implode("','",$pageids);
            } else {
                 $eids = $pageids;
            }
			
            $this->log(\Zend\Log\Logger::INFO, sprintf("Starting Cms sync for %s (%s).", $store->getWebsite()->getName() , $store->getName()));
            $actions = array(
                    'delete' => $this->_frameworkModelResource->getConnection("core_write")
                        ->select()
                        /*
                         * Select synced cms in the current store/mode that 
                         * are no longer enabled
                         */
                        ->from(
                                    array('k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")),
                                    array('page_id' => "k.product_id")
                                   
                        )
                        ->joinLeft(
                            array('c' => $this->_frameworkModelResource->getTableName("cms_page")),
                            "k.product_id = c.".$this->_page_value,
                            ""
                        )
                        ->joinLeft(
                            array('v' => $this->_frameworkModelResource->getTableName("cms_page_store")),
                            "v.".$this->_page_value." = c.".$this->_page_value,
                            ""
                        )
                        ->where("((k.store_id = :store_id AND v.store_id != 0) AND (k.type = :type) AND (k.product_id NOT IN ?)) OR ( (k.product_id IN ('".$eids."') OR (c.".$this->_page_value." IS NULL) OR (c.is_active = 0)) AND (k.type = :type) AND k.store_id = :store_id)",
                            $this->_frameworkModelResource->getConnection("core_write")
                                ->select()
                                ->from(
                                    array('i' => $this->_frameworkModelResource->getTableName("cms_page_store")),
                                    array('page_id' => "i.".$this->_page_value)
                                )
                                ->where('i.'.$this->_page_value.' NOT IN (?)', $pageids)
                               // ->where("i.store_id = :store_id")
                        )
                        ->group(array('k.product_id'))
                        ->bind(array(
                            'store_id'=> $store->getId(),
                            'type' => "pages",
                        )),

                    'update' => 
                            $this->_frameworkModelResource->getConnection("core_write")
                                ->select()
                                /*
                                 * Select pages for the current store/mode
                                 * have been updated since last sync.
                                 */
                                 ->from(
                                    array('k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")),
                                    array('page_id' => "k.product_id")
                                   
                                )
                                ->join(
                                    array('c' => $this->_frameworkModelResource->getTableName("cms_page")),
                                    "c.".$this->_page_value." = k.product_id",
                                    ""
                                )
                                ->joinLeft(
                                    array('v' => $this->_frameworkModelResource->getTableName("cms_page_store")),
                                    "v.".$this->_page_value." = c.".$this->_page_value." AND v.store_id = :store_id",
                                    ""
                                )
                                ->where("(c.is_active = 1) AND (k.type = :type) AND (k.store_id = :store_id) AND (c.update_time > k.last_synced_at)")
                                ->where('c.'.$this->_page_value.' NOT IN (?)', $pageids)
                        ->bind(array(
                            'store_id' => $store->getId(),
                            'type'=> "pages",
                        )),

                    'add' =>  $this->_frameworkModelResource->getConnection("core_write")
                                ->select()
                                ->union(array(
                                $this->_frameworkModelResource->getConnection("core_write")
                                ->select()
                                /*
                                 * Select pages for the current store/mode
                                 * have been updated since last sync.
                                 */
                                ->from(
                                    array('p' => $this->_frameworkModelResource->getTableName("cms_page")),
                                    array('page_id' => "p.".$this->_page_value)
                                )
                                ->where('p.'.$this->_page_value.' NOT IN (?)', $pageids)
                                ->joinLeft(
                                    array('v' => $this->_frameworkModelResource->getTableName("cms_page_store")),
                                    "p.".$this->_page_value." = v.".$this->_page_value,
                                    ""
                                )
                                ->joinLeft(
                                    array('k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")),
                                    "p.".$this->_page_value." = k.product_id AND k.store_id = :store_id AND k.test_mode = :test_mode AND k.type = :type",
                                    ""
                                )
                                ->where("p.is_active = 1 AND k.product_id IS NULL AND v.store_id =0"),
                                $this->_frameworkModelResource->getConnection("core_write")
                                ->select()
                                /*
                                 * Select pages for the current store/mode
                                 * have been updated since last sync.
                                 */
                                ->from(
                                    array('p' => $this->_frameworkModelResource->getTableName("cms_page")),
                                    array('page_id' => "p.".$this->_page_value)
                                )
                                ->where('p.'.$this->_page_value.' NOT IN (?)', $pageids)
                                ->join(
                                    array('v' => $this->_frameworkModelResource->getTableName("cms_page_store")),
                                    "p.".$this->_page_value." = v.".$this->_page_value." AND v.store_id = :store_id",
                                    ""
                                )
                                ->joinLeft(
                                    array('k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")),
                                    "v.".$this->_page_value." = k.product_id AND k.store_id = :store_id AND k.test_mode = :test_mode AND k.type = :type",
                                    ""
                                )
                                ->where("p.is_active = 1 AND k.product_id IS NULL")
                            ))    
                        ->bind(array(
                            'type' => "pages",
                            'store_id' => $store->getId(),
                            'test_mode' => $this->isTestModeEnabled(),
                        )),
                );
            $errors = 0;
            foreach($actions as $action => $statement) {
                if ($this->rescheduleIfOutOfMemory()) {
                    return;
                }
                $method = $action . "cms";
                $cms_pages = $this->_frameworkModelResource->getConnection("core_write")->fetchAll($statement, $statement->getBind());
                $total = count($cms_pages);
                $this->log(\Zend\Log\Logger::INFO, sprintf("Found %d Cms Pages to %s.", $total, $action));
                $pages = ceil($total / static ::RECORDS_PER_PAGE);
                for ($page = 1; $page <= $pages; $page++) {
                    if ($this->rescheduleIfOutOfMemory()) {
                        return;
                    }
                    $offset = ($page - 1) * static ::RECORDS_PER_PAGE;
                    $result = $this->$method(array_slice($cms_pages, $offset, static ::RECORDS_PER_PAGE));
                    if ($result !== true) {
                        $errors++;
                        $this->log(\Zend\Log\Logger::ERR, sprintf("Errors occurred while attempting to %s cms pages %d - %d: %s", $action, $offset + 1, ($offset + static ::RECORDS_PER_PAGE <= $total) ? $offset + static ::RECORDS_PER_PAGE : $total, $result));
                    }
                }
            }
            $this->log(\Zend\Log\Logger::INFO, sprintf("Finished cms page sync for %s (%s).", $store->getWebsite()->getName() , $store->getName()));
    }
	
    /**
     * Delete the given pages from Klevu Search. Returns true if the operation was
     * successful, or the error message if the operation failed.
     *
     * @param array $data List of pages to delete. Each element should be an array
     *                    containing an element with "page_id" as the key and page id as
     *                    the value.
     *
     * @return bool|string
     */
    protected function deletecms(array $data){
        $total = count($data);
        $response = $this->_apiActionDeleterecords->setStore($this->getStore())->execute(array(
            'sessionId' => $this->getSessionId() ,
            'records' => array_map(function ($v)
            {
                return array(
                    'id' => "pageid_" . $v['page_id']
                );
            }
            , $data)
        ));
        if ($response->isSuccess()) {
            $connection = $this->_frameworkModelResource->getConnection("core_write");
            $select = $connection->select()->from(array(
                'k' => $this->_frameworkModelResource->getTableName("klevu_product_sync")
            ))->where("k.store_id = ?", $this->getStore()->getId())->where("k.type = ?", "pages")->where("k.test_mode = ?", $this->isTestModeEnabled());
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }
            $or_where = array();
            for ($i = 0; $i < count($data); $i++) {
                if (isset($skipped_record_ids[$i])) {
                    continue;
                }
                $or_where[] = sprintf("(%s)", $connection->quoteInto("k.product_id = ?", $data[$i]['page_id']));
            }
            $select->where(implode(" OR ", $or_where));
            $connection->query($select->deleteFromSelect("k"));
            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d cms%s failed (%s)", $skipped_count, ($skipped_count > 1) ? "s" : "", implode(", ", $skipped_records["messages"]));
            }
            else {
                return true;
            }
        }
        else {
            return sprintf("%d cms%s failed (%s)", $total, ($total > 1) ? "s" : "", $response->getMessage());
        }
    }
	
    /**
     * Add the given pages to Klevu Search. Returns true if the operation was successful,
     * or the error message if it failed.
     *
     * @param array $data List of pages to add. Each element should be an array
     *                    containing an element with "page_id" as the key and page id as
     *                    the value.
     *
     * @return bool|string
     */
    protected function addCms(array $data){
        $total = count($data);
        $data = $this->addCmsData($data);
        $response = $this->_apiActionAddrecords->setStore($this->getStore())->execute(array(
            'sessionId' => $this->getSessionId() ,
            'records' => $data
        ));
        if ($response->isSuccess()) {
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }
            $sync_time = $this->_searchHelperCompat->now();
            foreach($data as $i => & $record) {
                if (isset($skipped_record_ids[$i])) {
                    unset($data[$i]);
                    continue;
                }
                $ids[$i] = explode("_", $data[$i]['id']);
                $record = array(
                    $ids[$i][1],
                    0,
                    $this->getStore()->getId() ,
                    $this->isTestModeEnabled() ,
                    $sync_time,
                    "pages"
                );
            }
            $this->_frameworkModelResource->getConnection("core_write")->insertArray($this->_frameworkModelResource->getTableName('klevu_product_sync') , array(
                "product_id",
                "parent_id",
                "store_id",
                "test_mode",
                "last_synced_at",
                "type"
            ) , $data);
            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d cms%s failed (%s)", $skipped_count, ($skipped_count > 1) ? "s" : "", implode(", ", $skipped_records["messages"]));
            }
            else {
                return true;
            }
        }
        else {
            return sprintf("%d cms%s failed (%s)", $total, ($total > 1) ? "s" : "", $response->getMessage());
        }
    }
	
    /**
     * Add the page Sync data to each page in the given list. Updates the given
     * list directly to save memory.
     *
     * @param array $pages An array of pages. Each element should be an array with
     *                        containing an element with "id" as the key and the Page
     *                        ID as the value.
     *
     * @return $this
     */
    protected function addcmsData(&$pages){
        $page_ids = array();
        foreach($pages as $key => $value) {
            $page_ids[] = $value["page_id"];
        }
        if ($this->getStore()->isFrontUrlSecure()) {
            $base_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK,true);
        }
        else {
            $base_url = $this->_storeModelStoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        }
        $data = $this->_cmsModelPage->getCollection()->addFieldToSelect("*")->addFieldToFilter('page_id', array(
            'in' => $page_ids
        ));
        $cms_data = $data->load()->getData();
        foreach($cms_data as $key => $value) {
            $value["name"] = $value["title"];
            $value["desc"] = $value["content"];
            $value["id"] = "pageid_" . $value["page_id"];
            $value["url"] = $base_url . $value["identifier"];
			$value["desc"] = preg_replace('#\{{.*?\}}#s','',strip_tags($this->_contentHelperData->ripTags($value["content"])));
            $value["metaDesc"] = $value["meta_description"] . $value["meta_keywords"];
			$value["shortDesc"] = substr(preg_replace('#\{{.*?\}}#s','',strip_tags($this->_contentHelperData->ripTags($value["content"]))),0,200);
            $value["listCategory"] = "KLEVU_CMS";
            $value["category"] = "pages";
            $value["salePrice"] = 0;
            $value["currency"] = "USD";
            $value["inStock"] = "yes";
            $cms_data_new[] = $value;
        }
        return $cms_data_new;
    }
	
    /**
     * Update the given pages on Klevu Search. Returns true if the operation was successful,
     * or the error message if it failed.
     *
     * @param array $data List of Pages to update. Each element should be an array
     *                    containing an element with "page_id" as the key and page id as
     *                    the value
     *
     * @return bool|string
     */
    protected function updateCms(array $data){
        $total = count($data);
        $data = $this->addCmsData($data);
        $response = $this->_apiActionUpdaterecords->setStore($this->getStore())->execute(array(
            'sessionId' => $this->getSessionId() ,
            'records' => $data
        ));
        if ($response->isSuccess()) {
            $helper = $this->_searchHelperData;
            $connection = $this->_frameworkModelResource->getConnection("core_write");
            $skipped_record_ids = array();
            if ($skipped_records = $response->getSkippedRecords()) {
                $skipped_record_ids = array_flip($skipped_records["index"]);
            }
            $where = array();
            for ($i = 0; $i < count($data); $i++) {
                if (isset($skipped_record_ids[$i])) {
                    continue;
                }
                $ids[$i] = explode("_", $data[$i]['id']);
                $where[] = sprintf("(%s AND %s AND %s)", $connection->quoteInto("product_id = ?", $ids[$i][1]) , $connection->quoteInto("parent_id = ?", 0) , $connection->quoteInto("type = ?", "pages"));
            }
            $where = sprintf("(%s) AND (%s) AND (%s)", $connection->quoteInto("store_id = ?", $this->getStore()->getId()) , $connection->quoteInto("test_mode = ?", $this->isTestModeEnabled()) , implode(" OR ", $where));
            $connection->update($this->_frameworkModelResource->getTableName('klevu_product_sync') , array(
                'last_synced_at' => $this->_searchHelperCompat->now()
            ) , $where);
            $skipped_count = count($skipped_record_ids);
            if ($skipped_count > 0) {
                return sprintf("%d cms%s failed (%s)", $skipped_count, ($skipped_count > 1) ? "s" : "", implode(", ", $skipped_records["messages"]));
            }
            else {
                return true;
            }
        }
        else {
            return sprintf("%d cms%s failed (%s)", $total, ($total > 1) ? "s" : "", $response->getMessage());
        }
    }

}
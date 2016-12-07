<?php /**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */ 
 namespace Klevu\Search\Adapter\Mysql\Query\Builder; 
 
 use Magento\Framework\DB\Helper\Mysql\Fulltext; 
 use Magento\Framework\DB\Select; 
 use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface; 
 use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface; 
 use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder; 
 use Magento\Framework\Search\Request\Query\BoolExpression; 
 use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface; 
 
class Match extends \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match {

    /**
     * Klevu Search API Parameters
     * @var array
     */
    protected $_klevu_parameters;
    protected $_klevu_tracking_parameters;
    protected $_klevu_type_of_records = 'KLEVU_PRODUCT';
    /**
     * Klevu Search API Product IDs
     * @var array
     */
    protected $_klevu_product_ids = array();
    protected $_klevu_parent_child_ids = array();
    /**
     * Klevu Search API Response
     * @var \Klevu\Search\Model\Api\Response
     */
    protected $_klevu_response;
    /**
     * Search query
     * @var string
     */
    protected $_query;
    /**
     * Total number of results found
     * @var int
     */
    protected $_klevu_size;
    /**
     * The XML Response from Klevu
     * @var SimpleXMLElement
     */
    protected $_klevu_response_xml;
    /**
     * @var \Klevu\Search\Model\Api\Action\Idsearch
     */
    protected $_apiActionIdsearch;
    /**
     * @param ResolverInterface $resolver
     * @param Fulltext $fulltextHelper
     * @param string $fulltextSearchMode
     */
	
	/**
     * @var PreprocessorInterface[]
     */
    protected $preprocessors;
	
    public function __construct(
	ResolverInterface $resolver, 
	Fulltext $fulltextHelper, 
	\Magento\Framework\Session\Generic $session, 
	\Klevu\Search\Helper\Config $searchHelperConfig, 
	\Klevu\Search\Helper\Data $searchHelperData, 
	\Klevu\Search\Model\Api\Action\Idsearch $apiActionIdsearch, 
	\Klevu\Search\Model\Api\Action\Searchtermtracking $apiActionSearchtermtracking, 
	$fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN,
	 array $preprocessors = [])
    {
        $this->resolver = $resolver;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        $this->fulltextHelper = $fulltextHelper;
        $this->fulltextSearchMode = $fulltextSearchMode;
		$this->preprocessors = $preprocessors;
        $this->_session = $session;
        $this->_searchHelperConfig = $searchHelperConfig;
        $this->_searchHelperData = $searchHelperData;
        $this->_apiActionIdsearch = $apiActionIdsearch;
        $this->_apiActionSearchtermtracking = $apiActionSearchtermtracking;
		
    }
	

    /**
     * {@inheritdoc}

     */
    public function build(ScoreBuilder $scoreBuilder, Select $select, RequestQueryInterface $query, $conditionType)
    {
	    $config = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Helper\Config');
		if($config->isLandingEnabled()==1 && $config->isExtensionConfigured()) {
			$q = $query->getValue();
			$queryterm = $this->_session->getData('queryterm');
			$currentstorecode = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getCode();
			$storecode      = $this->_session->getData('store_code');
			$storecode_sess = isset($storecode) ? $storecode : '';
			$sess = isset($queryterm) ? $queryterm : '';
			if ($q != $sess || $currentstorecode != $storecode_sess) {
				$this->_session->setData('ids', $this->_getProductIds($q));
				$this->_session->setData('queryterm', $q);
			}
			if(!empty($this->_session->getData('ids'))) {
				$matchQuery = sprintf('(search_index.entity_id IN (%s))', implode(',', $this->_session->getData('ids')));
				$select->where($matchQuery);
			}
			return $select;
		} else {
			/** @var $query \Magento\Framework\Search\Request\Query\Match */
			$queryValue = $this->prepareQuery($query->getValue(), $conditionType);

			$fieldList = [];
			foreach ($query->getMatches() as $match) {
				$fieldList[] = $match['field'];
			}
			$resolvedFieldList = $this->resolver->resolve($fieldList);

			$fieldIds = [];
			$columns = [];
			foreach ($resolvedFieldList as $field) {
				if ($field->getType() === FieldInterface::TYPE_FULLTEXT && $field->getAttributeId()) {
					$fieldIds[] = $field->getAttributeId();
				}
				$column = $field->getColumn();
				$columns[$column] = $column;
			}

			$matchQuery = $this->fulltextHelper->getMatchQuery(
				$columns,
				$queryValue,
				$this->fulltextSearchMode
			);
			$scoreBuilder->addCondition($matchQuery, true);

			if ($fieldIds) {
				$matchQuery = sprintf('(%s AND search_index.attribute_id IN (%s))', $matchQuery, implode(',', $fieldIds));
			}

			$select->where($matchQuery);

			return $select;
		}
		
		
		
		
    }

    /**
     * Return the Klevu api search filters
     * @return array
     */
    public function getSearchFilters($query)
    {  
        if (empty($this->_klevu_parameters)) {
            $this->_klevu_parameters = array(
                'ticket' => $this->_searchHelperConfig->getJsApiKey() ,
                'noOfResults' => 1000,
                'term' => $query,
                'paginationStartsFrom' => 0,
                'enableFilters' => 'false',
                'klevuShowOutOfStockProducts' => 'true',
                'category' => $this->_klevu_type_of_records
            );
        }

        return $this->_klevu_parameters;
    }

    /**
     * Send the API Request and return the API Response.
     * @return \Klevu\Search\Model\Api\Response
     */
    public function getKlevuResponse($query)
    {
        if (!$this->_klevu_response) {
            $this->_klevu_response = $this->_apiActionIdsearch->execute($this->getSearchFilters($query));
        }

        return $this->_klevu_response;
    }

    /**
     * This method executes the the Klevu API request if it has not already been called, and takes the result
     * with the result we get all the item IDs, pass into our helper which returns the child and parent id's.
     * We then add all these values to our class variable $_klevu_product_ids.
     *
     * @return array
     */
    protected function _getProductIds($query)
    {

        if (empty($this->_klevu_product_ids)) {
						
            // If no results, return an empty array

            if (!$this->getKlevuResponse($query)->hasData('result')) {
                return array();
            }

            foreach($this->getKlevuResponse($query)->getData('result') as $result) {
				if(isset($result['id'])){
					$item_id = $this->_searchHelperData->getMagentoProductId((string)$result['id']);
					$this->_klevu_parent_child_ids[] = $item_id;
					if ($item_id['parent_id'] != 0) {
						$this->_klevu_product_ids[$item_id['parent_id']] = $item_id['parent_id'];
					}

					$this->_klevu_product_ids[$item_id['product_id']] = $item_id['product_id'];
				}else {
					$item_id = $this->_searchHelperData->getMagentoProductId((string)$result);
					$this->_klevu_parent_child_ids[] = $item_id;
					if ($item_id['parent_id'] != 0) {
						$this->_klevu_product_ids[$item_id['parent_id']] = $item_id['parent_id'];
					}

					$this->_klevu_product_ids[$item_id['product_id']] = $item_id['product_id'];
				}
                
            }

            $this->_klevu_product_ids = array_unique($this->_klevu_product_ids);
            $this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Products count returned: %s", count($this->_klevu_product_ids)));
            /*$response_meta = $this->getKlevuResponse($query)->getData('meta');
            $this->_apiActionSearchtermtracking->execute($this->getSearchTracking(count($this->_klevu_product_ids),$response_meta['typeOfQuery']));
            */
        }

        return $this->_klevu_product_ids;
    }
}

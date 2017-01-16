<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Klevu\Search\Model\Search;

use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\Stock;

/**
 * Build base Query for Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder extends \Magento\CatalogSearch\Model\Search\IndexBuilder
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var TableMapper
     */
    private $tableMapper;
	
	/**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param ConditionManager $conditionManager
     * @param IndexScopeResolver $scopeResolver
     * @param TableMapper $tableMapper
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        ConditionManager $conditionManager,
        IndexScopeResolver $scopeResolver,
        TableMapper $tableMapper
    ) {
        $this->resource = $resource;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
        $this->tableMapper = $tableMapper;
    }

    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     */
    
    public function build(RequestInterface $request)
    {
		$config = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Helper\Config');
		if($config->isLandingEnabled()==1 && $config->isExtensionConfigured()) {
			$searchIndexTable = $this->scopeResolver->resolve($request->getIndex(), $request->getDimensions());
				$select = $this->resource->getConnection()->select()
				->from(
					['search_index' => 'catalog_product_entity'],
					['entity_id' => 'entity_id']
				);
				
			$select = $this->tableMapper->addTables($select, $request);

			$select = $this->processDimensions($request, $select);

			$isShowOutOfStock = $this->config->isSetFlag(
				'cataloginventory/options/show_out_of_stock',
				ScopeInterface::SCOPE_STORE
			);
			/*if ($isShowOutOfStock === false) {
				$select->joinLeft(
					['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
					'search_index.entity_id = stock_index.product_id'
					. $this->resource->getConnection()->quoteInto(
						' AND stock_index.website_id = ?',
						$this->getStockConfiguration()->getDefaultScopeId()
					),
					[]
				);
				$select->where('stock_index.stock_status = ?', Stock::DEFAULT_STOCK_ID);
			}*/
			return $select;
		} else {
				$searchIndexTable = $this->scopeResolver->resolve($request->getIndex(), $request->getDimensions());
			    $select = $this->resource->getConnection()->select()
				->from(
					['search_index' => $searchIndexTable],
					['entity_id' => 'entity_id']
				)
				->joinLeft(
					['cea' => $this->resource->getTableName('catalog_eav_attribute')],
					'search_index.attribute_id = cea.attribute_id',
					[]
				);

			$select = $this->tableMapper->addTables($select, $request);

			$select = $this->processDimensions($request, $select);

			$isShowOutOfStock = $this->config->isSetFlag(
				'cataloginventory/options/show_out_of_stock',
				ScopeInterface::SCOPE_STORE
			);
			if ($isShowOutOfStock === false) {
				$select->joinLeft(
					['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
					'search_index.entity_id = stock_index.product_id'
					. $this->resource->getConnection()->quoteInto(
						' AND stock_index.website_id = ?',
						$this->getStockConfiguration()->getDefaultScopeId()
					),
					[]
				);
				$select->where('stock_index.stock_status = ?', Stock::DEFAULT_STOCK_ID);
			}

			return $select;
		}
    }
	
	/**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    public function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Api\StockConfigurationInterface');
        }
        return $this->stockConfiguration;
    }
  


    /**
     * Add filtering by dimensions
     *
     * @param RequestInterface $request
     * @param Select $select
     * @return \Magento\Framework\DB\Select
     */
    private function processDimensions(RequestInterface $request, Select $select)
    {
        $dimensions = $this->prepareDimensions($request->getDimensions());

        $query = $this->conditionManager->combineQueries($dimensions, Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string[]
     */
    private function prepareDimensions(array $dimensions)
    {
        $preparedDimensions = [];
        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }
            $preparedDimensions[] = $this->conditionManager->generateCondition(
                $dimension->getName(),
                '=',
                $dimension->getValue()
            );
        }

        return $preparedDimensions;
    }
}

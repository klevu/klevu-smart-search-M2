<?php

namespace Klevu\Search\Test\Controller;

class CatalogSearch extends Ecom\Dev\PHPUnit\Test\Case\Controller {
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_magentoFrameworkRegistry;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_resourceProductCollection;

    /**
     * @var \Klevu\Search\Model\Api\Response\Search
     */
    protected $_apiResponseSearch;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_modelEntityAttribute;

    public function __construct(\Magento\Framework\Registry $magentoFrameworkRegistry, 
        \Magento\Catalog\Model\Resource\Product\Collection $resourceProductCollection, 
        \Klevu\Search\Model\Api\Response\Search $apiResponseSearch, 
        \Magento\Eav\Model\Entity\Attribute $modelEntityAttribute)
    {
        $this->_magentoFrameworkRegistry = $magentoFrameworkRegistry;
        $this->_resourceProductCollection = $resourceProductCollection;
        $this->_apiResponseSearch = $apiResponseSearch;
        $this->_modelEntityAttribute = $modelEntityAttribute;

        parent::__construct();
    }


    /** @var  \Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection */
    protected $collection;

    protected function tearDown() {
        $this->getLayout()->reset();
        $this->collection = null;
        $this->_magentoFrameworkRegistry->unregister('_singleton/catalogsearch/layer');
        $this->_magentoFrameworkRegistry->unregister('current_layer');
        $this->_magentoFrameworkRegistry->unregister('current_category_filter');

        // Delete all products
        $this->_resourceProductCollection->delete();

        parent::tearDown();
    }

    protected function setUp() {
        $this->getLayout()->reset();
        // Make sure the 'price' attribute is set to is_filterable_in_search
        $price = $this->getPriceAttribute();
        $price->setData('is_filterable_in_search', 1);
        $price->save();

        parent::setUp();
    }

    /**
     * Run search and make sure the page returns HTTP Code 200
     * @test
     * @loadFixture search_results
     */
    public function testSearchResultsPageLoads() {
        $this->mockAndDispatchSearchResults();
        // Assert the request was successful.
        $this->assertResponseHttpCode(200);
    }

    /**
     * Run search and make sure the layered navigation and search result list block appears
     * @test
     * @loadFixture search_results
     */
    public function testExpectedLayoutBlocksAreRendered() {
        $this->mockAndDispatchSearchResults();
        // Assert that the left nav (layered navigation) and the search results have been rendered.
        $this->assertLayoutBlockRendered('catalogsearch.leftnav');
        $this->assertLayoutBlockRendered('search_result_list');

    }

    /**
     * Run search and check that category and price filters exist
     * @test
     * @loadFixture search_results
     */
    public function testLayeredNavigationHasFilters() {
        $this->mockAndDispatchSearchResults();

        // Load the layered navigation block
        /** @var \Klevu\Search\Block\CatalogSearch\Layer $layer_nav */
        $layer_nav = $this->app()->getLayout()->getBlock('catalogsearch.leftnav');// Get the category filter block and assert it isn't null.
        $category_filters_block = $layer_nav->getChild('category_filter');
        $this->assertNotNull($category_filters_block);

        // Get the category filter items and assert that there was one filter item returned, and it's the category "Shirts"
        $category_filters = $category_filters_block->getItems();
        $this->assertCount(1, $category_filters);
        $this->assertEquals('Test Category', $category_filters[0]->getData('label'));

        $price_filters_block = $layer_nav->getChild('price_filter');
        $this->assertNotFalse($price_filters_block);

        // Check the price filters are as we expect
        $price_filters = $price_filters_block->getItems();
        $this->assertCount(2, $price_filters);
        $this->assertEquals('0-49', $price_filters[0]->getData('value'));
    }

    /**
     * Run search and check the results are correct, and in the expected order
     * @test
     * @loadFixture search_results
     */
    public function testSearchResultsAreCorrect() {
        $this->mockApiAndCollection();
        $collection = $this->collection;
        // Assert that the number of results is correct.
        $this->assertEquals(3, $collection->getSize());

        // Assert that the results are in the expected order.
        $expected_product_ids = array(3,2,1);
        $actual_product_ids   = array();
        /** @var \Magento\Catalog\Model\Product $product */
        foreach($collection as $product) {
            $actual_product_ids[] = $product->getId();
        }

        $this->assertEquals($expected_product_ids, $actual_product_ids);
    }

    /**
     * Test that a category filter can be applied
     * @test
     * @loadFixture search_results
     */
    public function testSearchCategoryFilterIsApplied() {
        $this->app()->getRequest()->setQuery('cat', '2');
        $this->mockAndDispatchSearchResults();

        $this->assertCount(1, $this->getAppliedFilters()); // We expect only 1 applied filter.
        $this->assertEquals("Test Category", $this->getFirstAppliedFilter()->getData('label'));
    }

    /**
     * Test that a price filter can be applied
     * @test
     * @loadFixture search_results
     */
    public function testSearchPriceFilterIsApplied() {
        $this->app()->getRequest()->setQuery('price', '0-49');
        $this->mockAndDispatchSearchResults();

        $this->assertCount(1, $this->getAppliedFilters()); // We expect only 1 applied filter.
        $this->assertEquals(array('0', '49'), $this->getFirstAppliedFilter()->getData('value'));
    }

    /**
     * Test that both the price and category filter can be applied at the same time
     * @test
     * @loadFixture search_results
     */
    public function testMultipleFiltersCanBeApplied() {
        $this->app()->getRequest()->setQuery(array('price' => '0-49', 'cat' => '23'));
        $this->mockAndDispatchSearchResults();

        $this->assertCount(2, $this->getAppliedFilters()); // We expect 2 applied filters.
    }

    /**
     * Test that we can change the sort order so results are in descending order of Price.
     * @test
     * @loadFixture search_results
     */
    public function testChangingSortOrderToPriceDesc() {
        $this->app()->getRequest()->setQuery(array('dir' => 'desc', 'order' => 'price'));
        $this->mockAndDispatchSearchResults('example', 'sorted');
        $result_block = $this->getSearchResultsBlock();
        // Set the updated mock collection
        $result_block->setCollection($this->collection);

        /** @var \Klevu\Search\Model\CatalogSearch\Resource\Fulltext\Collection $product_collection */
        $product_collection = $result_block->getLoadedProductCollection();

        // Expected order of results
        $expected_product_ids = array(2, 1, 3);
        $actual_product_ids = array();
        /** @var \Magento\Catalog\Model\Product $product */
        foreach($product_collection as $product) {
            $actual_product_ids[] = $product->getId();
        }
        $this->assertEquals($expected_product_ids, $actual_product_ids, 'Products are in the wrong order');
    }

    /**
     * Test that we can access the 2nd page of results and that the expected products show.
     * @test
     * @loadFixture search_results
     */
    public function testSecondPageOfResultsExists() {
        // Default: page size = 9, set the page to page 2, meaning we expect to see results from 10+
        $this->getRequest()->setQuery('p', '2');
        // Our response will return the 10th product, our response contains a total result size of 10.
        $this->mockAndDispatchSearchResults('example', 'paged', 9);

        $result_block = $this->getSearchResultsBlock();
        $result_block->setCollection($this->collection);
        $toolbar = $result_block->getToolbarBlock();

        $toolbar->setCollection($this->collection);

        //Assert that we can see the 10th item, out of 10 results.
        $pager_text = version_compare(Mage::getVersion(), "1.9", ">=") ? '10-10 of 10' : 'Items 10 to 10 of 10 total';
        $this->assertContains($pager_text, $toolbar->toHtml());

    }

    /**
     * Test that when we perform a search which does not return results, the no results message is displayed.
     * @test
     * @loadFixture search_results
     */
    public function testNoResults() {
        $this->mockAndDispatchSearchResults('returnsnothing', 'empty');

        $result_block = $this->getSearchResultsBlock();
        $result_block->setCollection($this->collection);
        $result_html = $result_block->toHtml();

        $this->assertContains('There are no products matching the selection.', $result_html);
    }

    /**
     * Return the search result list block
     * @return \Magento\Catalog\Block\Product\List
     */
    protected function getSearchResultsBlock() {
        return $this->app()->getLayout()->getBlock('search_result_list');
    }

    /**
     * Get all applied filters
     * @return array
     */
    protected function getAppliedFilters() {

        /** @var \Klevu\Search\Block\CatalogSearch\Layer $layer_nav */
        $layer_nav = $this->app()->getLayout()->getBlock('catalogsearch.leftnav');
        return $layer_nav->getLayer()->getState()->getFilters();
    }

    /**
     * Fetch the first applied filter
     * @return \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected function getFirstAppliedFilter() {
        $filters = $this->getAppliedFilters();
        return $filters[0];
    }

    /**
     * @param string $query
     * @return $this
     * @throws \Zend\Controller\Exception
     */
    protected function mockAndDispatchSearchResults($query = 'example', $response_type = 'successful', $pagination = 0) {
        $this->mockApiAndCollection($query, $response_type, $pagination);
        // Set the search query
        $this->getRequest()->setQuery('q', $query);

        // Load the search results page
        return $this->dispatch('catalogsearch/result/index');
    }

    protected function mockApiAndCollection($query = 'example', $response_type = 'successful', $pagination = 0) {
        // Mock the API Action
        switch($response_type) {
            default:
            case 'successful':
                $response = $this->getSearchResponse('search_response_success.xml');
                break;
            case 'empty':
                $response = $this->getSearchResponse('search_response_empty.xml');
                break;
            case 'sorted':
                $response = $this->getSearchResponse('search_response_sorted.xml');
                break;
            case 'paged':
                $response = $this->getSearchResponse('search_response_paged.xml');
                break;
        }

        $this->replaceApiActionByMock("klevu_search/api_action_idsearch", $response);

        $return_value = array(
            'ticket' => 'klevu-14255510895641069',
            'noOfResults' => 9,
            'term' => $query,
            'paginationStartsFrom' => $pagination,
            'klevuSort' => 'rel',
            'enableFilters' => 'true',
            'filterResults' => '',
        );

        // Mock the klevu search resource collection model, and force the search filters data
        $this->collection = $this->getResourceModelMock('catalogsearch/fulltext_collection', array('getSearchFilters'));
        $this->collection->expects($this->any())
            ->method('getSearchFilters')
            ->will($this->returnValue($return_value));
    }

    /**
     * Create a mock class of the given API action model which will expect to be executed
     * once and will return the given response. Then replace that model in Magento with
     * the created mock.
     *
     * @param string $alias A grouped class name of the API action model to mock
     * @param \Klevu\Search\Model\Api\Response $response
     *
     * @return $this
     */
    protected function replaceApiActionByMock($alias, $response) {
        $mock = $this->getModelMock($alias, array("execute"));
        $mock
            ->expects($this->any())
            ->method("execute")
            ->will($this->returnValue($response));

        $this->replaceByMock("model", $alias, $mock);



        return $this;
    }

    /**
     * Return a klevu_search/api_response_message model with a successful response from
     * a startSession API call.
     *
     * @return \Klevu\Search\Model\Api\Response\Message
     */
    protected function getSearchResponse($data_file) {
        $model = $this->_apiResponseSearch->setRawResponse(
            new \Zend\Http\Response(200, array(), $this->getDataFileContents($data_file))
        );

        return $model;

    }

    protected function getDataFileContents($file) {
        $directory_tree = array(
            Mage::getModuleDir('', 'Klevu\Search'),
            'Test',
            'Model',
            'Api',
            'data',
            $file
        );

        $file_path = join(DS, $directory_tree);

        return file_get_contents($file_path);
    }

    protected function getPriceAttribute() {
        return $this->_modelEntityAttribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'price');
    }
}

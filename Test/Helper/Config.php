<?php

namespace Klevu\Search\Test\Helper;

class Config extends Ecom\Dev\PHPUnit\Test\Case {
    /**
     * @var \Klevu\Search\Helper\Config
     */
    protected $_searchHelperConfig;

    public function __construct(\Klevu\Search\Helper\Config $searchHelperConfig)
    {
        $this->_searchHelperConfig = $searchHelperConfig;

        parent::__construct();
    }


    /** @var \Klevu\Search\Helper\Config $helper */
    protected $helper;

    protected function setUp() {
        parent::setUp();

        $this->helper = $this->_searchHelperConfig;
    }

    protected function tearDown() {
        $this->getConfig()->deleteConfig("klevu_search/general/enabled");
        $this->getConfig()->deleteConfig("klevu_search/general/js_api_key");
        $this->getConfig()->deleteConfig("klevu_search/general/rest_api_key");
        $this->getConfig()->deleteConfig("klevu_search/product_sync/enabled");
        $this->getConfig()->deleteConfig("klevu_search/product_sync/frequency");
        $this->getConfig()->deleteConfig("klevu_search/attributes/additional");
        $this->getConfig()->deleteConfig("klevu_search/order_sync/enabled");
        $this->getConfig()->deleteConfig("klevu_search/order_sync/frequency");
        $this->getConfig()->deleteConfig("klevu_search/developer/force_log");
        $this->getConfig()->deleteConfig("klevu_search/developer/log_level");

        parent::tearDown();
    }

    /**
     * @test
     * @loadFixture
     */
    public function testIsExtensionEnabledEnabled() {
        $this->assertEquals(true, $this->helper->isExtensionEnabled());
    }

    /**
     * @test
     * @loadFixture
     */
    public function testIsExtensionEnabledDisabled() {
        $this->assertEquals(false, $this->helper->isExtensionEnabled());
    }

    
    /**
     * @test
     */
    public function testGetJsApiKeyProduction() {
        $api_key = 'klevu-14255510895641069';

        $this->getConfig()
            ->saveConfig("klevu_search/general/js_api_key", $api_key)
            ->cleanCache();

        $this->clearConfigCache();

        $this->assertEquals($api_key, $this->helper->getJsApiKey());
    }
   
    /**
     * @test
     */
    public function testGetRestApiKeyProduction() {
        $api_key = 'a2xldnUtMTQyNTU1MTA4OTU2NDEwNjk6S2xldnUtZmo4NzQ3cHUxMg==';

        $this->getConfig()
            ->saveConfig("klevu_search/general/rest_api_key", $api_key)
            ->cleanCache();

        $this->clearConfigCache();

        $this->assertEquals($api_key, $this->helper->getRestApiKey());
    }
    
    /**
     * @test
     */
    public function testGetProductSyncEnabledFlagDefault() {
        $this->assertEquals(1, $this->helper->getProductSyncEnabledFlag());
    }

    /**
     * @test
     * @loadFixture
     */
    public function testGetProductSyncEnabledFlag() {
        $this->assertEquals(1, $this->helper->getProductSyncEnabledFlag());
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsProductSyncEnabled($config_flag, $result) {
        

        $this->clearConfigCache();

        $this->getConfig()
            ->saveConfig("klevu_search/product_sync/enabled", $config_flag)
            ->cleanCache();

        $this->assertEquals($result, $this->helper->isProductSyncEnabled());
    }

    /**
     * @test
     */
    public function testGetProductSyncFrequencyDefault() {
        $this->assertEquals("0 * * * *", $this->helper->getProductSyncFrequency());
    }

    /**
     * @test
     * @loadFixture
     */
    public function testGetProductSyncFrequency() {
        $this->assertEquals("0 */5 * * *", $this->helper->getProductSyncFrequency());
    }

    /**
     * @test
     */
    public function testGetAdditionalAttributesMap() {
        $map = array(
            "_1" => array("klevu_attribute" => "k_test", "magento_attribute" => "m_test"),
            "_2" => array("klevu_attribute" => "k_other", "magento_attribute" => "m_something")
        );

        // Test the default value
        $this->assertEquals(array(), $this->helper->getAdditionalAttributesMap(), "getAdditionalAttributesMap() did not default to an empty array.");

        $this->getConfig()
            ->saveConfig("klevu_search/attributes/additional", serialize($map))
            ->reinit();

        $this->assertEquals($map, $this->helper->getAdditionalAttributesMap(), "getAdditionalAttributesMap() failed to return the map set.");
    }

    /**
     * @test
     */
    public function testGetOrderSyncEnabledFlagDefault() {
        $this->assertEquals(1, $this->helper->getOrderSyncEnabledFlag());
    }

    /**
     * @test
     * @loadFixture
     */
    public function testGetOrderSyncEnabledFlag() {
        $this->assertEquals(1, $this->helper->getOrderSyncEnabledFlag());
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsOrderSyncEnabled($config_flag, $result) {
        $this->getConfig()
            ->saveConfig("klevu_search/order_sync/enabled", $config_flag)
            ->cleanCache();

        $this->assertEquals($result, $this->helper->isOrderSyncEnabled());
    }

    /**
     * @test
     */
    public function testGetOrderSyncFrequencyDefault() {
        $this->assertEquals("0 * * * *", $this->helper->getOrderSyncFrequency());
    }

    /**
     * @test
     * @loadFixture
     */
    public function testGetOrderSyncFrequency() {
        $this->assertEquals("0 */3 * * *", $this->helper->getOrderSyncFrequency());
    }

    /**
     * @test
     */
    public function testIsLoggingForced() {
        // Test the default value
        $this->assertEquals(true, $this->helper->isLoggingForced());

        $this->clearConfigCache();

        // Test a set value
        $this->getConfig()
            ->saveConfig('klevu_search/developer/force_log', false)
            ->cleanCache();

        $this->assertEquals(false, $this->helper->isLoggingForced());
    }

    public function testGetLogLevel() {
        // Test the default value
        $this->assertEquals(\Zend\Log\Logger::INFO, $this->helper->getLogLevel(), "getLogLevel() returned an incorrect default value.");

        $this->clearConfigCache();

        // Test a set value
        $this->getConfig()
            ->saveConfig('klevu_search/developer/log_level', \Zend\Log\Logger::WARN)
            ->cleanCache();

        $this->assertEquals(\Zend\Log\Logger::WARN, $this->helper->getLogLevel(), "getLogLevel() failed to return the value set.");
    }

    /**
     * Mock the helper method that checks whether the current domain is a production domain.
     *
     * @param bool $result The result to be returned by the method
     */
    protected function mockIsProductionDomain($result = false) {
        $data_helper = $this->getHelperMock('klevu_search', array("isProductionDomain"));
        $data_helper
            ->expects($this->any())
            ->method("isProductionDomain")
            ->will($this->returnValue($result));
        $this->replaceByMock("helper", "klevu_search", $data_helper);
    }

    protected function getConfig() {
        return Mage::app()->getConfig();
    }

    /**
     * Get around Magento's aggressive caching strategy and actually clear the configuration cache.
     */
    protected function clearConfigCache() {
        // Flush website and store configuration caches
        foreach (Mage::app()->getWebsites(true) as $website) {
            Ecom\Dev\Utils\Reflection::setRestrictedPropertyValue(
                $website, '_configCache', array()
            );
        }
        foreach (Mage::app()->getStores(true) as $store) {
            Ecom\Dev\Utils\Reflection::setRestrictedPropertyValue(
                $store, '_configCache', array()
            );
        }
    }
}

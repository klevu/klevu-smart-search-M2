<?php

namespace Klevu\Search\Test\Helper;

class Data extends Ecom\Dev\PHPUnit\Test\Case {
    /**
     * @var \Klevu\Search\Helper\Data
     */
    protected $_searchHelperData;

    public function __construct(\Klevu\Search\Helper\Data $searchHelperData)
    {
        $this->_searchHelperData = $searchHelperData;

        parent::__construct();
    }


    /** @var \Klevu\Search\Helper\Data $helper */
    protected $helper;

    protected function setUp() {
        parent::setUp();

        $this->helper = $this->_searchHelperData;
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testGetLanguageFromLocale($input, $output) {
        $this->assertEquals($output, $this->helper->getLanguageFromLocale($output));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsProductionDomain($domain, $result) {
        $this->assertEquals(
            $result,
            $this->helper->isProductionDomain($domain),
            sprintf("Domain %s should %s a production domain.", $domain, ($result ? "be" : "NOT be"))
        );
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testBytesToHumanReadable($input, $output) {
        $this->assertEquals($output, $this->helper->bytesToHumanReadable($input));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testHumanReadableToBytes($input, $output) {
        $this->assertEquals($output, $this->helper->humanReadableToBytes($input));
    }
}

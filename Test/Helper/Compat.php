<?php

namespace Klevu\Search\Test\Helper;

class Compat extends Ecom\Dev\PHPUnit\Test\Case {
    /**
     * @var \Klevu\Search\Helper\Compat
     */
    protected $_searchHelperCompat;

    public function __construct(\Klevu\Search\Helper\Compat $searchHelperCompat)
    {
        $this->_searchHelperCompat = $searchHelperCompat;

        parent::__construct();
    }


    /** @var \Klevu\Search\Helper\Compat $helper */
    protected $helper;

    protected function setUp() {
        parent::setUp();

        $this->helper = $this->_searchHelperCompat;
    }

    /**
     * @test
     */
    public function testGetProductUrlRewriteSelect() {
        $this->assertInstanceOf("Magento\Framework\Db\Select", $this->helper->getProductUrlRewriteSelect(array(1), 0, 1));
    }
}

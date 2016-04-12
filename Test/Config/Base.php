<?php

namespace Klevu\Search\Test\Config;

class Base extends Ecom\Dev\PHPUnit\Test\Case\Config {

    /**
     * @test
     */
    public function testClassAlias() {
        $this->assertBlockAlias("klevu_search/test", "Klevu\Search\Block\Test");
        $this->assertHelperAlias("klevu_search/test", "Klevu\Search\Helper\Test");
        $this->assertModelAlias("klevu_search/test", "Klevu\Search\Model\Test");
    }
}

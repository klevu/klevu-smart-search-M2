<?php
namespace Klevu\Search\Test\Helper;

class Api extends Ecom\Dev\PHPUnit\Test\Case {

    const VERSION_NUMBER = '1.1.12';

    public function testGetVersion() {
        $version = Mage::getConfig()->getModuleConfig('Klevu\Search')->version;
        $this->assertEquals(self::VERSION_NUMBER, $version);
    }
}

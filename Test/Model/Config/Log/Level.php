<?php

namespace Klevu\Search\Test\Model\Config\Log;

class Level extends Ecom\Dev\PHPUnit\Test\Case {
    /**
     * @var \Klevu\Search\Model\Config\Log\Level
     */
    protected $_configLogLevel;

    public function __construct(\Klevu\Search\Model\Config\Log\Level $configLogLevel)
    {
        $this->_configLogLevel = $configLogLevel;

        parent::__construct();
    }


    /**
     * @test
     */
    public function testGetValue() {
        $model = $this->_configLogLevel;

        // Test the default value
        $this->assertEquals(\Zend\Log\Logger::WARN, $model->getValue(), "getValue() returned an incorrect default value.");

        // Test a set value
        $model->setValue(\Zend\Log\Logger::INFO);

        $this->assertEquals(\Zend\Log\Logger::INFO, $model->getValue(), "getValue() didn't return the value set.");
    }
}

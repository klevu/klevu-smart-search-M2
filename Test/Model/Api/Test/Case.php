<?php

namespace Klevu\Search\Test\Model\Api\Test;

abstract abstractclass Case extends Ecom\Dev\PHPUnit\Test\Case {

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
            ->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($response));

        $this->replaceByMock("model", $alias, $mock);

        return $this;
    }

    /**
     * Create a mock class of the given session model, disabling session initialisation, and replace
     * that model in Magento with the created mock.
     *
     * @param string $alias A grouped class name of the session model to mock.
     *
     * @return $this
     */
    protected function replaceSessionByMock($alias) {
        $session_mock = $this->getModelMockBuilder($alias)
            ->disableOriginalConstructor()
            ->setMethods(array("init"))
            ->getMock();

        $session_mock
            ->expects($this->any())
            ->method("init")
            ->will($this->returnSelf());

        $this->replaceByMock("model", $alias, $session_mock);
        $this->replaceByMock("singleton", $alias, $session_mock);
    }
}

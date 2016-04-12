<?php

namespace Klevu\Search\Test\Model\System\Config\Source;

class Test  extends Ecom\Dev\PHPUnit\Test\Case {

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testIsValidSourceModel($alias) {
        $model = Mage::getModel($alias);

        $this->assertTrue($model !== false, sprintf("Model with alias %s not found.", $alias));
        $this->assertTrue(method_exists($model, "toOptionArray"), "toOptionArray() method doesn't exist.");

        $options = $model->toOptionArray();

        $this->assertTrue(is_array($options), "toOptionArray() did not return an array.");

        foreach ($options as $option) {
            $this->assertTrue(is_array($option), sprintf("Each option must be an array, instead got: %s", print_r($option, true)));

            $this->assertArrayHasKey("label", $option, sprintf("Each option must have a label, instead got: %s", print_r($option, true)));
            $this->assertArrayHasKey("value", $option, sprintf("Each option must have a value, instead got: %s", print_r($option, true)));
        }
    }
}

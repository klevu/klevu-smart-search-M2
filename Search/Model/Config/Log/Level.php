<?php

namespace Klevu\Search\Model\Config\Log;

class Level extends \Magento\Framework\Config\Data {

    /**
     * Return the log level value. Return \Zend\Log\Logger::WARN as default, if none set.
     *
     * @return int
     */
    public function getValue() {
        $value = $this->getData('value');

        return ($value != null) ? intval($value) : \Zend\Log\Logger::WARN;
    }
}

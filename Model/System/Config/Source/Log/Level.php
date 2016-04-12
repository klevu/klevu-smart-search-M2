<?php

namespace Klevu\Search\Model\System\Config\Source\Log;

class Level {

    public function toOptionArray() {
        return [
            ['value' => \Zend\Log\Logger::EMERG,  'label' => __("Emergency")],
            ['value' => \Zend\Log\Logger::ALERT,  'label' => __("Alert")],
            ['value' => \Zend\Log\Logger::CRIT,   'label' => __("Critical")],
            ['value' => \Zend\Log\Logger::ERR,    'label' => __("Error")],
            ['value' => \Zend\Log\Logger::WARN,   'label' => __("Warning")],
            ['value' => \Zend\Log\Logger::NOTICE, 'label' => __("Notice")],
            ['value' => \Zend\Log\Logger::INFO,   'label' => __("Information")],
            ['value' => \Zend\Log\Logger::DEBUG,  'label' => __("Debug")]
        ];
    }
}

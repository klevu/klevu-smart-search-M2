<?php

namespace Klevu\Search\Model\System\Config\Source;

class Yesnoforced {

    const YES    = 1;
    const NO     = 0;
    const FORCED = 2;
    
    public function toOptionArray()
    {
       return [
           ['value' => static::YES, 'label' => __('Yes')],
           ['value' => static::NO, 'label' => __('No')],
       ];
    }
}

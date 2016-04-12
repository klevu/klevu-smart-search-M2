<?php

namespace Klevu\Search\Model\System\Config\Source;

class Landingoptions {

    const YES    = 1;
    const NO     = 0;
    const KlEVULAND = 2;

    public function toOptionArray()
    {
       return [
           //['value' => static::NO, 'label' => __('Disable')],
           ['value' => static::KlEVULAND, 'label' => __('Based on Klevu Template (Recommended)')]
           //['value' => static::YES, 'label' => __('Preserves Your Theme Layout**')],

       ];
    }
    
    
    
}

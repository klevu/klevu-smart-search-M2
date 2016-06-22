<?php

namespace Klevu\Search\Model\System\Config\Source;

class Syncoptions {

    const SYNC_PARTIALLY = 1;
    const SYNC_ALL = 2;

    public function toOptionArray()
    {
       return [
           ['value' => static::SYNC_PARTIALLY, 'label' => __('Updates only (syncs data immediately)')],
           ['value' => static::SYNC_ALL, 'label' => __('All data (syncs data on CRON execution)')],
       ];
    }
}

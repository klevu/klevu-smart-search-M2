<?php

namespace Klevu\Search\Model\System\Config\Source;

class Frequency {

    const CRON_HOURLY = "0 * * * *";
    const CRON_EVERY_3_HOURS = "0 */3 * * *";
    const CRON_EVERY_6_HOURS = "0 */6 * * *";
    const CRON_EVERY_12_HOURS = "0 */12 * * *";
    const CRON_DAILY = "0 3 * * *";
    
    public function toOptionArray()
    {
       return [
           ['value' => static::CRON_HOURLY, 'label' => __('Hourly')],
           ['value' => static::CRON_EVERY_3_HOURS, 'label' => __('Every 3 hours')],
           ['value' => static::CRON_EVERY_6_HOURS, 'label' => __('Every 6 hours')],
           ['value' => static::CRON_EVERY_12_HOURS, 'label' => __('Every 12 hours')],
           ['value' => static::CRON_DAILY, 'label' => __('Daily')]

       ];
    }
    
}

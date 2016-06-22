<?php

namespace Klevu\Search\Model;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Collection;
abstract class Sync extends AbstractModel {

    /**
     * Limit the memory usage of the sync to 80% of the memory
     * limit. Considering that the minimum memory requirement
     * for Magento at the time of writing is 256MB, this seems
     * like a sensible default.
     */
    const MEMORY_LIMIT = 0.8;

    /**
     * Return the cron job code used for the sync model.
     *
     * @return string
     */
    abstract function getJobCode();

    /**
     * Perform the sync.
     */
    abstract function run();

    /**
     * Run a sync from cron at the specified time. Checks that a cron is not already
     * scheduled to run in the 15 minute interval before or after the given time first.
     *
     * @param DateTime|string $time The scheduled time as a DateTime object or a string
     *                              that is going to be passed into DateTime. Default is "now".
     *
     * @return $this
     */
    public function schedule($time = "now") {
        if (! $time instanceof \DateTime) {
            $time = new \DateTime($time);
        } else {
            // Don't modify the original parameter
            $time = clone $time;
        }
        $time_str = $time->format("Y-m-d H:i:00");
        $before_str = $time->modify("15 minutes ago")->format("Y-m-d H:i:00");
        $after_str = $time->modify("30 minutes")->format("Y-m-d H:i:00"); // Modifying the same DateTime object, so it's -15 + 30 = +15 minutes
        $collection_obj = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Cron\Model\ResourceModel\Schedule\Collection'); 
        $collection = $collection_obj
            ->addFieldToFilter("job_code", $this->getJobCode())
            ->addFieldToFilter("status", \Magento\Cron\Model\Schedule::STATUS_PENDING)
            ->addFieldToFilter("scheduled_at", array(
                "from" => $before_str,
                "to" => $after_str
            ));

        if ($collection->getSize() == 0) {
            $schedule = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Cron\Model\Schedule');
            $schedule
                ->setJobCode($this->getJobCode())
                ->setCreatedAt($time_str)
                ->setScheduledAt($time_str)
                ->setStatus(\Magento\Cron\Model\Schedule::STATUS_PENDING)
                ->save();
        }

        return $this;
    }

    /**
     * Check if a sync is currently running from cron. A number of running copies to
     * check for can be specified, which is useful if checking if another copy of sync
     * is running from sync itself.
     *
     * Ignores processes that have been running for more than an hour as they are likely
     * to have crashed.
     *
     * @param int $copies
     *
     * @return bool
     */
    public function isRunning($copies = 1) {
        $time = new \Datetime("1 hour ago");
        $time = $time->format("Y-m-d H:i:00");
        $collection_obj = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Cron\Model\ResourceModel\Schedule\Collection'); 
        $collection = $collection_obj
            ->addFieldToFilter("job_code", $this->getJobCode())
            ->addFieldToFilter("status", \Magento\Cron\Model\Schedule::STATUS_RUNNING)
            ->addFieldToFilter("executed_at", array("gteq" => $time));

        return $collection->getSize() >= $copies;
    }

    /**
     * Check if the current memory usage is below the limit.
     *
     * @return bool
     */
    protected function isBelowMemoryLimit() {
        $helper = $this->_searchHelperData;
        $php_memory_limit = ini_get('memory_limit');
        $usage = memory_get_usage(true);

        if($php_memory_limit < 0){
            $this->log(\Zend\Log\Logger::DEBUG, sprintf(
            "Memory usage: %s of %s.",
            $helper->bytesToHumanReadable($usage),
            $php_memory_limit));
            return true;
        }
        $limit = $helper->humanReadableToBytes($php_memory_limit);

        $this->log(\Zend\Log\Logger::DEBUG, sprintf(
            "Memory usage: %s of %s.",
            $helper->bytesToHumanReadable($usage),
            $helper->bytesToHumanReadable($limit)
        ));

        if ($usage / $limit > static::MEMORY_LIMIT) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if the memory limit has been reached and reschedule to run
     * again immediately if so.
     *
     * @return bool true if a new process was scheduled, false otherwise.
     */
    protected function rescheduleIfOutOfMemory() {
        if (!$this->isBelowMemoryLimit()) {
            $this->log(\Zend\Log\Logger::INFO, "Memory limit reached. Stopped and rescheduled.");
            $this->schedule();

            return true;
        }

        return false;
    }

    /**
     * Return the table name for the given model entity.
     *
     * @param string $entity
     *
     * @return string
     */
    protected function getTableName($entity) {
        return $this->_frameworkModelResource->getTableName($entity);
    }

    /**
     * Write a message to the log file.
     *
     * @param int    $level
     * @param string $message
     *
     * @return $this
     */
    protected function log($level, $message) {
        $this->_searchHelperData->log($level, sprintf("[%s] %s", $this->getJobCode(), $message));

        return $this;
    }
}

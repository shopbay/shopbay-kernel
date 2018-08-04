<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of NotificationBatchJob
 * 
 * @author kwlok
 */
trait NotificationBatchJob 
{
    public static $frequencyDaily = 'daily';
    /**
     * If subscription notification batch job can be run
     * @see NotificationTrait::subscriptionsConfig() 
     */
    public function getCanStart()
    {
        $params = $this->getParams();
        if (isset($params['frequency']) && isset($params['start_time'])){
            if ($params['frequency']==static::$frequencyDaily){
                //format hour and mins (24 hours format)
                return date('Hi',time()) >= $params['start_time'];
            }
        }
        return false;
    }
    /**
     * Finder method for new daily batch
     * Condition as new batch:
     * (1) batch_status is null OR
     * (2) For frequency as "daily" (stored in params), batch status will be a datestamp YYYYMMDD of today date after notification is sent or put into send queue.
     * (3) (MUST) params field contains "batch" equals to true
     * @see NotificationTrait::subscriptionsConfig() 
     * @return type
     */
    public function newDailyBatch() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'(batch_status IS NULL OR DATE_FORMAT(NOW(),\'%Y%m%d\') > batch_status) AND params LIKE \'%"batch":true%\'',
        ]);
        return $this;
    }
    /**
     * Update daily batch as processed
     * @see newDailyBatch() for its batch status format
     * @see NotificationTrait::subscriptionsConfig() 
     */
    public function setDailyBatchAsRun() 
    {
        $this->batch_status = date('Ymd',time());//set to format YYYYMMDD
        $this->update();
    }

}
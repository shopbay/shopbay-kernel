<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ChatbotSupport
 * 
 * @author kwlok
 */
class ChatbotSupport 
{
    /**
     * Return full working days 
     * @return array
     */
    public static function getWorkingDaysArray()
    {
        return [
            0=>Sii::t('sii','Sunday'),
            1=>Sii::t('sii','Monday'),
            2=>Sii::t('sii','Tuesday'),
            3=>Sii::t('sii','Wednesday'),
            4=>Sii::t('sii','Thursday'),
            5=>Sii::t('sii','Friday'),
            6=>Sii::t('sii','Saturday'),
        ];
    }
    /**
     * Check if target day is a working day
     * @param int $day The target day to check
     * @param array $workingDays The array containing which day is a working day. Value 1 means is a working day
     * [
     *   0 => '0',
     *   1 => '1',
     *   2 => '1',
     *   3 => '1',
     *   4 => '1',
     *   5 => '1',
     *   6 => '0',
     * ]
     * @return boolean
     */
    public static function isWorkingDay($day,$workingDays)
    {
        logInfo(__METHOD__.' Checking working day '.$day,$workingDays);
        return isset($workingDays[$day]) && $workingDays[$day];
    }  
    /**
     * Check if current time is a working time
     * 
     * @param type $openTime
     * @param type $closeTime
     * @return boolean False means outside working hours
     */    
    public static function isWorkingHour($openTime, $closeTime)
    {
        $time = (int)date('Hi',time());//format HHmm 24hours format
        logInfo(__METHOD__.' Checking work time '.$time.' for support open time '.$openTime.', close time '.$closeTime);
        return $time >= (int)$openTime && $time <= (int)$closeTime;
    }     
    /**
     * Prepare agent data for registration
     * @see OptInController::registerAgent()
     * 
     * @param type $agentId
     * @param type $agentAccountId
     * @return array
     */
    public static function prepareAgentData($agentId,$agentAccountId)
    {
        return [
            'agentId'=>$agentId,
            'agentAccountId'=>$agentAccountId,
        ];
    }    
}

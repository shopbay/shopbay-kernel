<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MessengerModelTrait
 *
 * @author kwlok
 */
trait MessengerModelTrait 
{
    protected $autoTrim = true;
    /**
     * Verify field length is not exceeding limit
     * @param type $field
     * @param type $limit
     * @throws CException
     */
    protected function checkLengthLimit($field,$limit)
    {
        if (strlen($this->$field) > $limit){
            if ($this->autoTrim){
                $trailing = '';//no trailing chars
                $this->$field = Helper::rightTrim($this->$field, $limit,$trailing);
                logWarning(__METHOD__.' Auto trimmed; '.get_class($this)." $field exceed $limit chars.");
            }
            else
                throw new CException(get_class($this)." $field must not exceed $limit chars.");
        }
    }
    /**
     * Verify field maximum count is not exceeding limit
     * @param type $field
     * @param type $limit
     * @throws CException
     */
    protected function checkCountLimit($field,$limit)
    {
        if (count($this->$field) > $limit){
            if ($this->autoTrim){
                $this->$field = array_slice($this->$field, 0, $limit);
                logWarning(__METHOD__.' Auto trimmed; '.get_class($this)." $field count exceed $limit.");
            }
            else
                throw new CException(get_class($this)." $field count must not exceed $limit.");
        }
    }
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TimestampBehavior
 *
 * @author kwlok
 */
class TimestampBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of the attribute to store the create timestamp. Defaults to 'create_time'
    */
    public $createAttribute = 'create_time';
    /**
    * @var string The name of the attribute to store the update timestamp. Defaults to 'update_time'
    */
    public $updateAttribute = 'update_time';
    /**
    * @var string The name of the attribute to enable update timestamp. Defaults to 'true'
    */
    public $updateEnable = true;
    /**
     * beforeSave
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    public function beforeSave($event) 
    {
        if($this->getOwner()->isNewRecord) {
            $this->getOwner()->{$this->createAttribute}=time();
            if ($this->updateEnable)
                $this->getOwner()->{$this->updateAttribute}=time();
        }
        else{
            if ($this->updateEnable)
                $this->getOwner()->{$this->updateAttribute}=time();
        }
    }
}
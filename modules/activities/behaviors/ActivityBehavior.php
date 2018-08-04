<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.customers.models.CustomerAccount');
/**
 * Description of ActivityBehavior
 *
 * @author kwlok
 */
class ActivityBehavior extends CActiveRecordBehavior 
{
    /**
     * @var string The name of description attribute of the model . Defaults to 'name'
     */
    public $descriptionAttribute = 'name';
    /**
     * @var string The name of object url attribute of the model . Defaults to 'viewUrl'
     */
    public $objectUrlAttribute = 'viewUrl';
    /**
     * @var string The name of icon url. Defaults to null
     */
    public $iconUrlSource;
    /**
     * @var boolean If to force create icon thumbnail. Defaults to true
     */
    public $createIconThumbnail = true;
    /**
     * @var string The app name that the activity record to be located. Defaults to null: follows app()->id
     */
    public $location;
    /**
     * Data format:
     * [
     *    'enable'=><true or false>,
     *    'iconAttribute'=><attribute name>,//current default rule check if attribute is not full
     *    'condition'=><to be done..>,//more to be done, maybe via callback to getOwner()->callback()...
     * ]
     * @var mixed To indicate if use button icon; Either by above format or a direct boolean 
     */
    public $buttonIcon;
    /**
     * Record activity 
     * 
     * @param mixed $details Event name or array data structure as below:
     * [
     *     'event' => 'xxx',       //mandatory
     *     'account' => 'xxx',     //optional (if absent, follow default setting)
     *     'description' => 'xxx', //optional (if absent, follow default setting)
     *     'object_url' => 'xxx',  //optional (if absent, follow default setting)
     *     'icon_url' => 'xxx',    //optional (if absent, follow default setting)
     * ]
     * 
     */
    public function recordActivity($details)
    {
        if (is_array($details)){
            //validate mandatory fields
            if (!array_key_exists('event', $details))
                throw new CException(__CLASS__.' must specify "event"');   
            $event = $details['event'];
        }
        else {
            if (!isset($details))
                throw new CException(__CLASS__.' must specify "event"');   
            $event = $details;
        }
        //validate iconUrlSource
        if (isset($this->iconUrlSource)){
            if ($this->getOwner()->{$this->iconUrlSource} === null){
                logError(__METHOD__.' '.$this->iconUrlSource.' is not found',$details,false);
                throw new CException(__CLASS__.' "iconUrlSource" is not found');   
            }
        }
        
        try {
            $a = new Activity();
            $a->obj_type = $this->getOwner()->tableName();
            $a->obj_id = $this->getOwner()->id;
            $a->location = $this->getOwner()->getDefaultLocation();//default rule to store location is app name
            $a->event = $event;
            $a->description = is_array($details)&&array_key_exists('description', $details)?$details['description']:$this->getOwner()->{$this->descriptionAttribute};
            //detect which account id to store
            if (is_array($details)&&array_key_exists('account', $details)){
                $a->account_id = $details['account'];
            }
            else {
                $a->account_id = $this->getOwner()->getAccountOwner()->{$this->getOwner()->getAccountOwner()->getAccountAttribute()};
            }
            
            $a->obj_url = is_array($details)&&array_key_exists('obj_url', $details)?$details['obj_url']:$this->getOwner()->{$this->objectUrlAttribute};
            $a->icon_url = is_array($details)&&array_key_exists('icon_url', $details)?$details['icon_url']:$this->getOwner()->getActivityIconUrl();

            if ($a->save())
                logTrace(__METHOD__.' ok',$a->getAttributes());
            else {
                logError(__METHOD__.' error',$a->getErrors(),false);
                throw new CException(Sii::t('sii','Activity Validation Error'));
            }
            
        } catch (CException $e) {
            logError($e->getMessage().' >> '.$e->getTraceAsString(),[],false);
            throw new CException(Sii::t('sii','Record Activity Error - {message}',['{message}'=>$e->getMessage()]));
        }

    }
    /**
     * The description of activity
     * @return string url
     */
    public function getActivityDescription()
    {
        return $this->getOwner()->{$this->descriptionAttribute};
    }
    /**
     * Icon url to represent the activity 
     * 
     * @param type $source
     * @return string Icon url
     */
    public function getActivityIconUrl($source=null)
    {
        $finder = $this->getOwner();
        if ($source!=null)
            return $finder->{$source}->getImageUrl(Image::VERSION_SMEDIUM,$this->createIconThumbnail);
        else {
            
            if (isset($this->iconUrlSource)){
                 if ($this->iconUrlSource == 'account')
                    return $finder->account->profile->getImageUrl(Image::VERSION_SMEDIUM,$this->createIconThumbnail);
                else{
                    logTrace(__METHOD__.' iconUrlSource '.$this->iconUrlSource,get_class($this->getOwner()));
                    return $finder->{$this->iconUrlSource}->getImageUrl(Image::VERSION_SMEDIUM,$this->createIconThumbnail);
                }
            }
            else {
                if ($this->_buttonIconEnabled() && $this->_getIconAttribute()==null)
                    return SButtonColumn::getButtonIcon(strtolower(get_class($finder)));
                else if ($this->_getIconAttribute()!=null && $this->getOwner()->{$this->_getIconAttribute()}===null)
                    return SButtonColumn::getButtonIcon(strtolower(get_class($finder)));
                else {
                    return $finder->getImageUrl(Image::VERSION_SMEDIUM,$this->createIconThumbnail);
                }
            }
        }
    }  
    /**
     * Return default location; Default to "app name"
     * @return type
     */
    public function getDefaultLocation()
    {
        if (isset($this->location))
            return $this->location;
        else 
            return Yii::app()->id;
    }
    
    private function _buttonIconEnabled()
    {
        if (isset($this->buttonIcon)){
            if (is_array($this->buttonIcon))
                return isset($this->buttonIcon['enable']) && $this->buttonIcon['enable'];
            else if (is_bool($this->buttonIcon))
                return $this->buttonIcon;
            else
                return false;
        }
        else
            return false;
    }
    
    private function _getIconAttribute()
    {
        if ($this->_buttonIconEnabled() && isset($this->buttonIcon['iconAttribute']))
            return $this->buttonIcon['iconAttribute'];
        
        return null;
    }

    public function searchActivities($accountId,$objType=null,$objId=null)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 't.account_id='.$accountId;
        if (isset($objType))
            $criteria->condition .= ' AND t.obj_type=\''.$objType.'\'';
        if (isset($objId))
            $criteria->condition .= ' AND t.obj_id=\''.$objId.'\'';
        $criteria->order = 'create_time DESC';
        return new CActiveDataProvider('Activity',[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);
    }

}

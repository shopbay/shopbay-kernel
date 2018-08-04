<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SActiveReord
 *
 * @author kwlok
 */
abstract class SActiveRecord extends CActiveRecord
{
    /**
     * Indicate if strict insertable fields check should be turn on;
     * Class need to set insertable whitelist specify which fields are allowed to be specified for insertion.
     * @var boolean 
     */
    public $enableInsertableCheck = false;
    /**
     * Indicate if strict updatable fields check should be turn on;
     * Class need to set updatable whitelist specify which fields are allowed to be updated.
     * @var boolean 
     */
    public $enableUpdatableCheck = false;
    /**
     * @var array Indicate which attribute are allowed to be updated; 
     *      Default to empty [] <- meaning all fields are not allowed to be updated
     */
    private $_insertables = [];
    /**
     * Check if there are fields update that are not to allowed
     * This expects client input fields are set in $this->_updatables
     * If not, conflict check is skipped
     * @var array Indicate which attribute are allowed to be updated; 
     *      Default to empty [] <- meaning all fields are not allowed to be updated
     */
    private $_updatables = [];
    /*
     * Stores the old attribute values
     */
    private $_oldAttributes = [];
    /**
     * This stores the values before update / save
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->setOldAttributes($this->attributes);
    } 
    /**
     * Additional checks before validate
     * @return type
     */
    public function beforeValidate() 
    {
        if ($this->enableInsertableCheck && $this->isNewRecord)
            $this->_checkConflicts($this->_insertables, 'insertable');
        
        if ($this->enableUpdatableCheck && !$this->isNewRecord)
            $this->_checkConflicts($this->_updatables, 'updatable');
        
        return parent::beforeValidate();
    }
    /**
     * Set insertable fields whitelist
     * @param array $fields
     */
    public function setInsertables($fields,$append=true)
    {
        $this->_setWhitelist('_insertables', $fields, $append);
    }   
    /**
     * Set updatable fields whitelist
     * Two keys:
     * "allow" = array of fields allowed to be updated.
     * "input" = array of fields client had input for update
     * @param array $fields
     */
    public function setUpdatables($fields,$append=true)
    {
        $this->_setWhitelist('_updatables', $fields, $append);
    }
    /**
     * Insertable and updateable validation checks
     * @param array $whitelist
     * @param string $type
     * @throws CException
     */
    private function _checkConflicts($whitelist,$type)
    {
        if (empty($whitelist)){
            throw new CException(Sii::t('sii','{class} is not {type}.',array('{class}'=>$this->displayName(),'{type}'=>$type)));
        }
        else {
            if (isset($whitelist['input']) && isset($whitelist['allow'])){
                $conflicts = [];
                foreach ( $whitelist['input'] as $field) {
                    if (!in_array($field,$whitelist['allow'])){
                        $conflicts[] = $field;
                    }
                }
                if (!empty($conflicts)){
                    logError(__METHOD__.' Invalid fields: '.implode(', ', $conflicts));
                    throw new CException(Sii::t('sii','Invalid fields: {fields}',array('{fields}'=>implode(', ', $conflicts))));
                } 
            }
        }
    }
    /**
     * Set whitelist
     * @param type $placeholder
     * @param type $fields
     * @param type $append
     */
    private function _setWhitelist($placeholder,$fields,$append=true)
    {
        if (!$append)
            $this->$placeholder = $fields;
        else
            $this->$placeholder = array_merge($this->$placeholder,$fields);
        
        logTrace(__METHOD__.' '.get_class($this).' '.$placeholder, $this->$placeholder);        
    }        
    /**
     * @return array
     */
    public function getHasOldAttributes()
    {
        return !empty($this->_oldAttributes);
    }
    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }
    /**
     * Set old attributes
     */
    public function setOldAttributes($attrbutes)
    {
        $this->_oldAttributes = $attrbutes;
    }
    /**
     * Url to view this model
     * @return string url
     */
    abstract public function getViewUrl();
    /**
     * Returns a value indicating whether there is any behavior by name.
     * @param string $behavior behavior name. Use null to check all behaviors.
     * @return boolean whether there is any behavior.
     */
    public function hasBehaviors($behavior=null)
    {
        if ($behavior===null)
            return $this->behaviors()!==array();
        else{
            $behaviors = $this->behaviors();
            return isset($behaviors[$behavior]);
        }
    }  
    /**
     * Returns the behavior by name.
     * @param string $behavior behavior name. Use null to check all behaviors.
     * @return array behavior setting
     */
    public function getBehavior($behavior)
    {
        $behaviors = $this->behaviors();
        return isset($behaviors[$behavior])?$behaviors[$behavior]:null;
    }     
    /**
     * A wrapper method to return all records of this model
     * @return \SActiveRecord
     */
    public function all() 
    {
        return $this;
    }
    /**
     * Retrieve record based on pk
     * @param type $id
     * @return \SActiveRecord
     */
    public function retrieve($id) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'id = '.$id,
        ));
        return $this;
    } 
    /**
     * Retrieve record created in current month
     * @param type $timestamp
     * @return \SActiveRecord
     */
    public function createdAfter($timestamp)
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'create_time >= '.$timestamp,
        ));
        return $this;
    }       
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return [];
    }  
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function getToolTip($attribute)
    {
        $tooltips = $this->attributeToolTips();
        return isset($tooltips[$attribute])?$tooltips[$attribute]:null;
    }     
    /*
     * Return the scenario name that bypasses auto sluggable behavior when slug value is presented
     */
    public function getSkipSlugScenario()
    {
        $behavior = $this->getBehavior('sluggable');
        return isset($behavior['skipScenario'])?$behavior['skipScenario']:null;
    } 
    /*
     * @return if in skipslug scenario
     */
    public function getIsSkipSlugScenario()
    {
        return $this->getScenario()==$this->getSkipSlugScenario();
    }    
    /**
     * Slug only has value when scenario is "create" - object creation only
     * Object with unregconized scenario has no effect on slug value
     * 
     * @return mixed String if there is value, else false
     */
    public function getSlugValue($scenario='')
    {
        if ($this->getScenario()==$this->getCreateScenario() || 
            $this->getScenario()==$scenario){
            return $this->getLanguageValue('name');
        }
        else
            return false;
    }   
    /*
     * Get create scenario
     */
    public function getCreateScenario()
    {
        return 'create';
    }
    /**
     * Verify attribute uniqueness
     */
    public function ruleUnique($attribute,$params)
    {
        $uniqueCheck = function($modelClass,$attribute) {
            if ($modelClass::model()->exists($attribute.'=\''.$this->$attribute.'\''))
                $this->addError($attribute,Sii::t('sii','{attribute} is already taken.',array('{attribute}'=>ucfirst($attribute))));
        };
        if ($this->isNewRecord || //new record
            ($this->hasOldAttributes && strcasecmp($this->oldAttributes[$attribute],$this->$attribute)!=0) //update record  
           ){
            $uniqueCheck(get_class($this),$attribute);
        }
    }    
    /**
     * Verify url slug whitelist method
     * @param type $attribute
     * @param type $params
     * @return type
     */
    public function ruleSlugWhitelist($attribute,$params)
    {
        if (empty($this->$attribute))
            return;//empty content, skip validate
        
        if (!preg_match('/^[\p{L}0-9-.~_]+$/u', $this->$attribute))
            $this->addError($attribute,Sii::t('sii','URL accepts only letters, digits, hypen, dot, underscore and tilde.'));
    }   
    /**
     * Below method is main for logic processing and not for internalization
     * @param type $type
     * @return string
     */
    public static function plural($type)
    {
        switch (strtolower($type)) {
            case 'news':
                return $type;
            case 'inventory':
                return 'Inventories';
            case 'category':
                return 'Categories';
            case 'activity':
                return 'Activities';
            case 'process':
                return 'Processes';
            default:
                return $type.'s';
        }
    }    
    /**
     * Resolve table name to get it corresponding model class
     * @param type $tablename
     * @return type
     */
    public static function resolveTablename($tablename) 
    { 
        $result = '';
        $tablename = substr($tablename, 2);//remove prefix "s_"
        $array = explode('_', $tablename);//check if table name has "_" in between, e.g. s_inventory_history
        foreach ($array as $value)
            $result .= ucfirst($value);
        return $result;
    }
    /**
     * Restore table name based on model class
     * @param type $objType
     * @return type
     */
    public static function restoreTablename($objType) 
    { 
        if (strtolower($objType)=='tutorialseries')
            $objType = 'tutorial_series';
        if (strtolower($objType)=='shippingorder')
            $objType = 'shipping_order';
        if (strtolower($objType)=='campaignbga')
            $objType = 'campaign_bga';
        if (strtolower($objType)=='campaignsale')
            $objType = 'campaign_sale';
        if (strtolower($objType)=='customeraccount')
            $objType = 'customer_account';
        if (strtolower($objType)=='merchantaccount')
            $objType = 'merchant_account';
        return 's_'.strtolower($objType);//prefix "s_"
    }    
}

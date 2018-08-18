<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PlanTypeTrait
 *
 * @author kwlok
 */
trait PlanTypeTrait 
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }       
    /**
     * Return account profile
     * @return type
     */
    public function getAccountProfile()
    {
        return $this->account->profile;
    }    
    /**
     * Type validation rule
     */
    public function ruleType($attribute, $params)
    {
        if ($this->$attribute!=null && !in_array($this->$attribute,array_keys(static::getTypes())))
            $this->addError($attribute,Sii::t('sii','Invalid type: {type}',['{type}'=>$this->$attribute]));
    }  
    
    public function getIsOneTimeCharge()
    {
        return $this->type==Plan::FIXED;
    }

    public function getIsRecurringCharge()
    {
        return $this->type==Plan::RECURRING;
    }    

    public function getIsTrial()
    {
        return $this->type==Plan::TRIAL;
    }    
    /**
     * Internal plan / package will not be seen by external users (through filer published()
     * Internal plan / package is used to control admin user behaviors via subscription merchanism
     * @return boolean
     */
    public function getIsInternal()
    {
        return $this->type==Plan::INTERNAL;
    }    
    /**
     * A scope wrapper method to return non-internal plans / packages
     * @return \Plan
     */
    public function nonInternal() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'type != \''.Plan::INTERNAL.'\'',
        ]);
        return $this;
    }
        
    public function getTypeDesc()
    {
        return static::getTypes()[$this->type];
    }
    
    public static function getTypes()
    {
        return [
            Plan::TRIAL => Sii::t('sii','Trial'),
            Plan::FIXED => Sii::t('sii','Fixed'), 
            Plan::RECURRING => Sii::t('sii','Recurring'),
            Plan::CONTRACT => Sii::t('sii','Contract'),
            Plan::INTERNAL => Sii::t('sii','Internal'),
        ];
    }
    
    public static function getTypesDesc($value)
    {
        $types = static::getTypes();
        if (isset($types[$value]))
            return $types[$value];
        else 
            return Sii::t('sii','unset');
    }  

}

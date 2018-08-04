<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of LanguageBehavior
 *
 * @author kwlok
 */
class LanguageBehavior extends CActiveRecordBehavior 
{
    public $formType;
    /**
     * Format attribute value with locales
     * @param type $value
     * @return array 
     */
    public function serializeLanguageValue($value,$params=array())
    {
        $names = new CMap;
        foreach ($this->getOwner()->getLanguageKeys() as $language) {
            $names->add($language,Sii::tp('sii', $value, $params, $language));
        }
        return json_encode($names->toArray());            
    }     
    /**
     * Parse a language locale value
     * @param $value the value to be parsed
     * @param $locale the locale
     * @return type
     */
    public function parseLanguageValue($value,$locale=null)
    {
        if (!isset($locale))
            $locale = $this->getOwner()->getLanguageDefaultLocale();
        
        if (is_object($value)){
            return $value->$locale;
        }             
        else if (is_array($value)){
            return $value[$locale];
        }        
        else {
            $valueObj = $this->getOwner()->getLanguageValueObject($value);
            if (is_object($valueObj))
                return $this->_parseValue($valueObj->$locale);
            else
                return $value;
        }
    }    
    /**
     * Get locale attribute value object
     * @param type $attribute If no owner, attribute itself will be the encoded value
     * @param type $owner Optional
     * @return type
     */
    public function getLanguageValueObject($attribute,$owner=null)
    {
        if (isset($owner))
            return json_decode($owner->$attribute);
        else{
            return json_decode($attribute);
        }
    }
    /**
     * Default locale meaning the at least locale to be used;
     * Use for fall back also if attribute of other locale has no data 
     * 
     * If shop locale not set, will return user locale
     * 
     * @see LocaleBehavior
     * @return default locale (follows shop locale)
     */
    public function getLanguageDefaultLocale()
    {
        try {
           return $this->getOwner()->getLocale();
        } catch (CException $ex) {
            logWarning(__METHOD__.' Locale not found; use user locale instead >> '.$ex->getMessage());
            return user()->getLocale();
        }
    }
    /**
     * Return attribute based on its locale;
     * 
     * @param type $attribute
     * @param type $locale
     * @param type $readonly
     * @return type
     */
    public function getLanguageValue($attribute,$locale=null,$readonly=true)
    {
        if (!isset($locale))
            $locale = $this->getLanguageDefaultLocale();
        
        $obj = $this->getLanguageValueObject($attribute,$this->getOwner());
        if (is_object($obj)){
            return $this->_parseValue($obj->$locale,$readonly);
        }
        else {//for backward compatibility purpose; non-json data 
            return $this->_parseValue($this->getOwner()->$attribute,$readonly);
        }
    }
    /**
     * A helper (shortcut) method to get model language name
     * @return string $locale
     */
    public function localeName($locale=null,$attribute='name')
    {
        return $this->displayLanguageValue($attribute, $locale);
    }
    /**
     * Display attribute based on its locale;
     * If locale value not found, it will fall back to default locale
     * If not again, it will loop through shop level languages until one value is found (first value to be found)
     * 
     * @param type $attribute
     * @param type $locale
     * @param boolean $purify
     * @return type
     */
    public function displayLanguageValue($attribute,$locale=null,$purify=false)
    {
        $value = $this->getLanguageValue($attribute, $locale);
        if ($value==Sii::t('sii','unset')){
            //Fallback scenario 1: take default locale 
            $obj = $this->getLanguageValueObject($attribute,$this->getOwner());
            if (is_object($obj)){
                $fallbackLocale = $this->getLanguageDefaultLocale();
                $value = $this->_parseValue($obj->$fallbackLocale);
            }
            else
                $value = $this->getOwner()->$attribute;
            
            //Fallback scenario 2: when default locale returning no value, 
            //loop through other supported locales of shop
            if ($value==Sii::t('sii','unset')){
                $shop = $this->getOwner() ;
                if (!($shop instanceof Shop) && isset($this->getOwner()->shop)){
                    $shop = $this->getOwner()->shop;
                }
                if ($shop instanceof Shop){
                    foreach ($shop->getLanguageKeys() as $language) {
                        $value = $obj->$language;
                        if ($value!=Sii::t('sii','unset')&&!empty($value)){
                            break;
                        }
                    }
                }
            }
        }
        
        return $purify?Helper::purify($value,is_array($purify)?$purify:[]):$value;
    }    
    /**
     * Convert a model into LanguageForm, and assigning attributes
     * @return \formType
     */
    public function getLanguageForm()
    {
        if (!isset($this->formType))
            $this->formType = get_class($this->getOwner()).'Form';
        $form = new $this->formType;
        foreach ($form->getLocaleAttributeKeys() as $attribute) {
            $form->$attribute = $this->getOwner()->$attribute;
        }
        return $form;
    }    
    /**
     * Parse multi-lang value
     * (1) If non-empty, return as it is
     * (2) If empty and readonly, return "unset"
     * (3) If empty but not readonly, return null - for editable field, leave blank if it it blank
     *  
     * @param type $value
     * @param type $readonly
     * @return type
     */
    private function _parseValue($value,$readonly=true)
    {
        if (!empty($value) ){
            return $value;
        }
        else {
            if ($readonly){
                return Sii::t('sii','unset');
            }
            else
                return null;
        }
    }
    /**
     * Find model by language name (either language)
     * @param type $name
     * @param type $keyColumnCondition
     * @param type $modelClass
     * @return mixed Return CActiveRecord if found; Return false if not found
     */
    public function findModelByLanguageName($name,$keyColumnCondition=array(),$modelClass=null)
    {
        if (!isset($modelClass))
            $modelClass = get_class($this->getOwner());
        
        $criteria = new CDbCriteria();
        if (!empty($keyColumnCondition))
            $criteria->addColumnCondition($keyColumnCondition);
        $criteria->compare('name',json_encode($name),true);
        $model = $modelClass::model()->find($criteria);
        if ($model===null){
            logError(__METHOD__." $modelClass not found",$criteria);
            return false;
        }
        else{
            logTrace(__METHOD__." $modelClass found",$criteria);
            return $model;
        }
    }
    
}

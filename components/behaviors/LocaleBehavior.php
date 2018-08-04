<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of LocaleBehavior
 *
 * @author kwlok
 */
class LocaleBehavior extends CActiveRecordBehavior 
{
    /**
     * @var string The name of the owner parent, refer to the 'relation' key. Defaults to 'shop'
     * Set to 'self' for Shop itself
     */
    public $ownerParent = 'shop';   
    /**
     * @var string The name of the locale attribute of merchant owner. Defaults to 'language'
     */
    public $localeAttribute = 'language';   
    /**
     * @var string The name of the currency attribute of merchant owner. Defaults to 'currency'
     */
    public $currencyAttribute = 'currency';   
    /**
     * @var string The name of the weight unit attribute of merchant owner. Defaults to 'weight_unit'
     */
    public $weightAttribute = 'weight_unit';   
    
    public function getLocale() 
    {
        if ($this->ownerParent=='self'){
            if (isset($this->getOwner()->{$this->localeAttribute}))
                return $this->getOwner()->{$this->localeAttribute};
            else
                return param('LOCALE_DEFAULT');
        }
        else
            return $this->getOwner()->{$this->ownerParent}->{$this->localeAttribute};
    } 

    public function getCurrency() 
    {
        if ($this->ownerParent=='self'){
            if (isset($this->getOwner()->{$this->currencyAttribute}))
                return $this->getOwner()->{$this->currencyAttribute};
            else 
                return null;
        }
        else
            return $this->getOwner()->{$this->ownerParent}->{$this->currencyAttribute};          
    }        

    public function getWeightUnit() 
    {
        if ($this->ownerParent=='self')
            return $this->getOwner()->{$this->weightAttribute};
        else
            return $this->getOwner()->{$this->ownerParent}->{$this->weightAttribute};
    } 
    /**
     * Follows ISO 4217
     * @param real $decimal
     * @param type $currency
     * @return type
     */
    public function formatCurrency($decimal,$currency=null)  
    {
        if ($decimal===null) 
            $decimal = 0.0;
        if ($currency===null) 
            $currency = $this->getOwner()->getCurrency();
        return CLocale::getInstance($this->getOwner()->getLocale())->numberFormatter->formatCurrency($decimal,$currency);
    }
    /**
     * Format weight based on weight unit
     * @param int $value
     * @return type
     */
    public function formatWeight($value)  
    {
        if ($value===null) $value = 0;
        return $value.$this->getOwner()->getWeightUnit();
    }     
    /**
     * Shortcut to format date (by locale)
     * @param string $timestamp
     * @return boolean $showTime
     */
    public function formatDatetime($timestamp, $showTime=true,$format=null)
    {
        if ($timestamp===null)
            return Sii::t('sii','not set');
        
        if ($showTime==true)
            return CLocale::getInstance($this->getOwner()->getLocale())->dateFormatter->formatDateTime($timestamp,'medium','short') ;
        else
            return CLocale::getInstance($this->getOwner()->getLocale())->dateFormatter->format(isset($format)?$format:param('DATE_FORMAT'),$timestamp) ;
    }
    /**
     * Format percentage
     * @param int $value
     * @return type
     */
    public function formatPercentage($value)  
    {
        if ($value===null) $value = 0;
        return ($value*100).'%';
    }     
        
}
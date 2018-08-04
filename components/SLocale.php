<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SLocale
 *
 * @author kwlok
 */
class SLocale 
{
    /**
     * Supported weight units
     * @param type $code
     * @return type
     */
    public static function getWeightUnits($code=null) 
    { 
        $array =  [
            'g' => Sii::t('sii','Gram (g)'),
            'lb' => Sii::t('sii','Pound (lb)'),
            //rest other weight units to be supported...
        ];
        return self::_getArrayValues($array, $code);                
    }
    /**
     * Get supported languages
     * @param type $code
     * @return type
     */
    public static function getLanguages($code=null) 
    { 
        return self::_getArrayValues(self::_getDatasource('languages'), $code);     
    }
    /**
     * Supported timezones
     * @see Fullist at todo.md
     * @param type $code
     * @return type
     */
    public static function getTimeZones($code=null) 
    { 
        return self::_getArrayValues(self::_getDatasource('timezones'), $code);
    }
    /**
     * @see ISO 4217 Currency Codes, full list at todo.md
     * @param type $code
     * @return type
     */
    public static function getCurrencies($code=null) 
    { 
        return self::_getArrayValues(self::_getDatasource('currencies'), $code);          
    }
    /**
     * Supported countries list 
     * @param type $code
     * @return type
     */
    public static function getCountries($code=null) 
    { 
        return self::_getArrayValues(self::_getDatasource('countries'), $code);                
    }
    /**
     * Supported state list by country
     * @param type $countryCode
     * @return type
     */
    public static function getStates($countryCode,$state=null) 
    { 
        $array = self::_getDatasource('states');
        if ($state!=null){
            return self::_getArrayValues(self::getStates($countryCode), $state);                
        }
        else {
            if (array_key_exists($countryCode, $array))
                return $array[$countryCode];  
            else
                return [];//empty
        }
    }
    
    private static function _getArrayValues($array,$key)
    {
        if ($key===null)
            return $array;
        else {
            if (array_key_exists($key, $array))
                return $array[$key];  
            else
                return Sii::t('sii','unset');
        }           
    }
    
    private static function _getDatasource($filename)
    {
        $basepath = Yii::getPathOfAlias('common');
        return include  $basepath.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$filename.'.php';        
    }
}
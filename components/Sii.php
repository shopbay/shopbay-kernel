<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * A translation helper class for message translation
 * 
 * Sii is the code name of sub project for "Internationlization" - multi-language support
 * 
 * @see SiiCommand at console app
 * @author kwlok
 */
class Sii 
{
    /**
     * Translates a message to the specified language.
     * Wrapper class of Yii::t for setting the category correctly.
     * 
     * The sequence of messages sources look up is:
     * [1] Modules 
     * [2] Web app (merged with common messages sources)
     * 
     * @param string $category message category.
     * @param string $message the original message.
     * @param array $params parameters to be applied to the message using <code>strtr</code>.
     * @param string $source which message source application component to use.
     * @param string $language the target language.
     * @return string the translated message.
     */
    public static function t($category, $message, $params=[], $source=null,$language=null)
    {
        if (controller()!=null){
            if (controller()->getModule()!=null)
                $category = ucfirst(controller()->getModule()->id).'Module.'.$category;
        }
        
        if (isset($language)){
            $before = Yii::app()->language;
            //change current language
            Yii::app()->language = $language;
            $message = self::_t($category, $message, $params, $source, $language);
            //restore back before language
            Yii::app()->language = $before;
            return $message;
        }
        else{
            return self::_t($category, $message, $params, $source, $language);
        }
    }
    /**
     * A shortcut method to translate static message with a particular language 
     * @param type $category
     * @param type $message
     * @param type $language
     * @return type
     */
    public static function tl($category,$message,$language=null)
    {
        return self::t($category, $message, [], null, $language);
    }
    /**
     * A shortcut method to translate static message with a particular language 
     * @param type $category
     * @param type $message
     * @param array $params parameters to be applied to the message using <code>strtr</code>.
     * @param type $language
     * @return type
     */
    public static function tp($category,$message, $params=[], $language=null)
    {
        return self::t($category, $message, $params, null, $language);
    }  
    /*
     * Internal t() - handles different Yii versions
     */
    private static function _t($category, $message, $params=[], $source=null,$language=null)
    {
        if (substr(Yii::getVersion(),0,1)=='2'){//calling from yii2, used in "api" app
            //Equivalent to Yii1::t(), but with a different method name due to incompatilibity with Yii 2.
            return Yii::t1($category, $message, $params, $source, isset($language)?$language:Yii::app()->language);
        }
        else
            return Yii::t($category, $message, $params, $source, isset($language)?$language:Yii::app()->id=='console'?Yii::app()->language:user()->getLocale());
    }
    /**
     * Restore the field messages back to array (contains all locale message)
     * @param type $locales
     * @param type $message
     * @return type
     */
    public static function toArray($locales, $message)
    {
        $data = [];
        foreach ($locales as $locale) {
            $data[$locale] = Sii::tl('sii',$message,$locale);
        }
        return $data;
    }    
    /**
     * Search module Sii messages
     * 
     * @param type $appPath
     * @param type $moduleName
     * @param type $language
     * @return array Sii messages
     */
    public static function findMessages($appPath,$moduleName,$language) 
    {
        $siiPath = $appPath.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.'sii.php';
        $messages = file_exists($siiPath) ? require($siiPath) : [];
        return $messages;
    }    
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SFormModel
 *
 * @author kwlok
 */
abstract class SFormModel extends CFormModel
{
    abstract public function displayName();
    
    public function rulePurify($attribute,$params)
    {
        if (empty($this->$attribute))
            return;//empty content, skip purify
        
        $text = Helper::purify($this->$attribute);
        if (empty($text))
            $this->addError($attribute,Sii::t('sii','{object} contains suspicious code. This is not allowed.',array('{object}'=>$this->getAttributeLabel($attribute))));
        else
            $this->$attribute = $text;
    }   
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array();
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
     * Get create scenario
     */
    public function getCreateScenario()
    {
        return 'create';
    }     
    
    public function ruleSlugWhitelist($attribute,$params)
    {
        if (empty($this->$attribute))
            return;//empty content, skip validate
        
        if (!preg_match('/^[\p{L}0-9-.~_]+$/u', $this->$attribute))
            $this->addError($attribute,Sii::t('sii','URL accepts only letters, digits, hypen, dot, underscore and tilde.'));
    }   

}
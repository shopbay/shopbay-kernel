<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ContentBehavior
 *
 * @author kwlok
 */
class ContentBehavior extends CActiveRecordBehavior 
{
    /**
     * This rule perform purify content
     * This is to prevent malicious code; e.g without this, 
     * content contains script can get executed: <script>alert("test");</script>
     * 
     * @param type $attribute
     */
    public function validatePurifyContent($attribute,$content)
    {
        if (empty($content))
            return;//empty content, skip purify

        $text = Helper::purify($content);
        if (empty($text))
            $this->getOwner()->addError($attribute,Sii::t('sii','{object} contains suspicious code. This is not allowed.',array('{object}'=>$this->getOwner()->getAttributeLabel($attribute))));
        else
            $this->getOwner()->$attribute = $text;  
    }    
    /**
     * This rule perform purify content
     * This is to prevent malicious code; e.g without this, 
     * content contains script can get executed: <script>alert("test");</script>
     * 
     * @param type $attribute
     */
    public function validatePurify($attribute)
    {
        $this->validatePurifyContent($attribute, $this->getOwner()->$attribute);
    }
    /**
     * Insert encoded content into DB
     * @param type $attribute
     */
    public function insertEncodedContent($attribute)
    {
        $this->parseEncodedContent($attribute);
        $this->getOwner()->insert();
    }
    /**
     * Update encoded content at DB
     * @param type $attribute
     */
    public function updateEncodedContent($attribute)
    {
        $this->parseEncodedContent($attribute);
        $this->getOwner()->update();
    }   
    /**
     * Parse content to support both json / string message
     * @param type $attribute
     */
    protected function parseEncodedContent($attribute)
    {
        $dataArr = json_decode($this->getOwner()->$attribute,true);
        if (is_array($dataArr)){
            $dataMap = new CMap();
            foreach ($dataArr as $key => $value) {
                $dataMap->add($key, $this->getOwner()->htmlnl2br($value));
            }
            $this->getOwner()->$attribute = json_encode($dataMap->toArray());
        }
        else {
            $this->getOwner()->nl2brContent($attribute);
        }        
    }
    /**
     * Convert content from new line to <br>, including encoding.
     */
    public function nl2brContent($attribute)
    {
        $this->getOwner()->$attribute = $this->getOwner()->htmlnl2br($this->getOwner()->$attribute);
    }
    /**
     * Convert content to html encoded plus new line to <br />
     * @param type $content
     */
    public function htmlnl2br($content)
    {
        return nl2br(CHtml::encode($content));
    }
    /**
     * Remove <br /> from html encoded content
     * Not exactly the reversed of self::htmlnl2br above
     * 
     * @param type $content
     */
    public function htmlbr2nl($content)
    {
        return str_replace('<br />','',$content);
    }
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotShipping
 *
 * @author kwlok
 */
class ChatbotShipping extends ChatbotModel
{
    /**
     * Shipping surcharge (For product level shipping surcharge if any)
     * @var float
     */
    protected $surcharge = 0.0;
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'Shipping';
    }
    /**
     * Set shipping surcharge
     * @param type $value
     */
    public function setSurcharge($value)
    {
        $this->surcharge = $value;
    }
    /**
     * Get shipping surcharge 
     * @return boolean $currency If to include currency
     */
    public function getSurcharge($currency=true)
    {
        return $this->getChargeValue($this->surcharge, $currency);
    }
    /**
     * Get shipping text
     * @return string
     */
    public function getText($productName=false)
    {
        $text = '';
        if ($productName)
            $text = Sii::t('sii','{shipping} for {product}',['{product}'=>$productName,'{shipping}'=>$this->name])."\n\n";

        foreach ($this->model->getShippingRemarks() as $remark) {
            $text .= $remark.'. ';
        }
        if ($this->getSurcharge(false) > 0)
            $text .= Sii::t('sii','There is a shipping surcharge of {surcharge}.',['{surcharge}'=>$this->getSurcharge()]);

        return strip_tags($text);
    }    
}

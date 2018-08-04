<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TaxableBehavior
 * Parsing json encoded tax data
 * For example: {"7":"{\"zh_cn\":\"\\u6d88\\u8d39\\u7a0e\",\"en_sg\":\"GST\"}|2.7|S$2.70|0.06|6%"}
 * 
 * Data elements:
 * [1] tax_id 
 * [2] raw name (multi-lang) 
 * [3] amount 
 * [4] amount_text (formatted) 
 * [5] rate 
 * [6] rate (percentage formatted) 
 * 
 * @see CartBase::getCheckoutTotal()
 * @see CartBase::getTaxes($shop)
 * @see TaxManager::parseTaxData()
 * 
 * @author kwlok
 */
class TaxableBehavior extends CActiveRecordBehavior 
{
    /**
     * @var string The name of tax data attribute that stores json encoded tax data. Defaults to "tax"
     */
    public $taxDataAttribute = 'tax';
    /**
     * json encoded data example:
     * {"1":"GST 7%|0.21|$0.21","4":"VAT 10%|0.3|$0.30"}
     * 
     * @return array
     */
    private $_d;
    public function getTaxData($id=null) 
    {
        if (isset($id)){
            return array($id=>$this->getTaxValue($id));
        }
        
        if ($this->_d==null)
            $this->_d = json_decode($this->getOwner()->{$this->taxDataAttribute},true);//return associative array
        return $this->_d;
    }
    /**
     * Check if has tax
     * @return array
     */
    public function hasTax() 
    {
        return $this->getTaxData()!=null;
    }
    /**
     * Return tax ids in array
     * @return array
     */
    public function getTaxKeys() 
    {
        return $this->hasTax()?array_keys($this->getTaxData()):[];
    }
    /**
     * Return tax all values in $taxData
     * @return array
     */
    public function getTaxValue($id) 
    {
        return $this->hasTax()?$this->_d[$id]:[];
    }
    /**
     * Return parsed tax value
     * @return array
     */
    public function parseTaxValue($value) 
    {
        return Yii::app()->serviceManager->getTaxManager()->parseTaxData($value);
    }
    /**
     * Return tax name
     * @param integer $id tax id
     * @return string
     */
    public function getTaxName($id) 
    {
        return $this->hasTax()?$this->parseTaxValue($this->getTaxValue($id))->name:null;
    }
    /**
     * Return tax amount
     * @param integer $id tax id
     * @return decimal
     */
    public function getTaxAmount($id) 
    {
        return $this->hasTax()?$this->parseTaxValue($this->getTaxValue($id))->amount:null;
    }
    /**
     * Return tax amount text (formatted)
     * @param integer $id tax id
     * @return string
     */
    public function getTaxAmountText($id) 
    {
        return $this->hasTax()?$this->parseTaxValue($this->getTaxValue($id))->amount_text:null;
    }    
    /**
     * Return tax rate
     * @param integer $id tax id
     * @return decimal
     */
    public function getTaxRate($id) 
    {
        return $this->hasTax()?$this->parseTaxValue($this->getTaxValue($id))->rate:null;
    }
    /**
     * Return tax rate text
     * @param integer $id tax id
     * @return decimal
     */
    public function getTaxRateText($id) 
    {
        return $this->hasTax()?$this->parseTaxValue($this->getTaxValue($id))->rate_text:null;
    }
    /**
     * Return tax total (derived from tax data) 
     * @param integer $id tax id
     * @return string
     */
    public function getTaxTotal() 
    {
        $total = 0.0;
        foreach ($this->getTaxKeys() as $id) {
            $total += $this->getTaxAmount($id);
        }
        return $total;
    }
    /**
     * Return tax display set (derived from tax data) 
     * @param integer $id Shipping id
     * @return array
     */
    public function getTaxDisplaySet($locale=null) 
    {
        $set = new CMap();
        foreach ($this->getTaxKeys() as $id) {
            $name = $this->getOwner()->parseLanguageValue($this->getTaxName($id),$locale);
            $rateText = $this->getTaxRateText($id);
            $set->add($name.' '.$rateText,$this->getTaxAmount($id));
        }
        return $set->toArray();
    }
    /**
     * Return tax display text (derived from tax data) 
     * @param integer $id Shipping id
     * @return array
     */
    public function getTaxDisplayText($locale=null) 
    {
        $set = new CList();
        foreach ($this->getTaxKeys() as $id) {
            $name = $this->getOwner()->parseLanguageValue($this->getTaxName($id),$locale);
            $set->add($name.' '.$this->getTaxAmountText($id));
        }
        return $set;
    }
    /**
     * Return tax display string (derived from tax data) 
     * @param integer $id Shipping id
     * @return array
     */
    public function getTaxDisplayString($locale=null) 
    {
        $str = '';
        foreach ($this->getTaxDisplayText($locale) as $value) {
            $str .= $value.', ';
        }
        if (strlen($str)>0)
            return substr($str,0,-2);//removing the last entry ', '
        else 
            return Sii::t('sii','not set');
    }    
}

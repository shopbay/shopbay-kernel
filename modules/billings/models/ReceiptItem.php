<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ReceiptItem
 *
 * @author kwlok
 */
class ReceiptItem extends CFormModel
{
    /**
     * Mandatory fields
     */
    public $item;//item description
    public $amount;
    public $currency;
    public $transaction_id;//below is used for credit card payment
    public $transaction_date;
    public $last4;
    public $card_type;
    /**
     * Optional fields
     */
    public $remarks= [];//other field info (in array key=>value format)
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        $keyFields = implode(',', static::mandatoryFields());
        return [
            [$keyFields, 'required'],
        ];
    }        
    /**
     * Convert to array
     * @return type
     */
    public function toArray()
    {
        return array_merge([
           'item'=>$this->item, 
           'amount'=>$this->amount, 
           'currency'=>$this->currency, 
           'transaction_id'=>$this->transaction_id, 
           'transaction_date'=>$this->transaction_date, 
           'last4'=>$this->last4, 
           'card_type'=>$this->card_type, 
           'charged_to'=>Receipt::formatChargedTo($this->card_type,$this->last4), //auto included
        ],$this->remarks);
    }
    
    public static function mandatoryFields()
    {
        return [
           'item','amount', 'currency','transaction_id','transaction_date','last4','card_type',
        ];
    }
}

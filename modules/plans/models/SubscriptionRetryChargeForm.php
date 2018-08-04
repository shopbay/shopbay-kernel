<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SubscriptionRetryChargeForm
 *
 * @author kwlok
 */
class SubscriptionRetryChargeForm extends CFormModel
{
    public $subscription_no;
    public $amount;
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('subscription_no, amount', 'required'),
            array('amount', 'length', 'max'=>10),
            array('amount', 'type', 'type'=>'float'),
            array('amount', 'numerical', 'min'=>1.0),
        );
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'amount'=>Sii::t('sii','Amount'),
        );
    }
}

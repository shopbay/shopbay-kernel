<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of PaymentManager
 *
 * @author kwlok
 */
class PaymentManager extends ServiceManager 
{   
    public function init() 
    {
        parent::init();   
        $this->model = 'Payment';
    }
    /**
     * Prepare payment data, can use the result to call pay() or refund()
     * @param stdClass all fields must be match with PaymentForm fields
     * @return PaymentForm model
     */    
    public function preparePaymentData($paymentStdClass)
    {
        $paymentForm = new PaymentForm();
        foreach ($paymentStdClass as $key => $value)
            $paymentForm->$key = $value;        
        if (!$paymentForm->validate(array('id, shop_id, payment_method, payer, reference_no, type, status, amount','currency')))
            throw new CException(Sii::t('sii','Not valid payment data'));
        return $paymentForm;
    }
    
    /**
     * Process payment
     * @param PaymentForm form
     */
    public function pay($form)
    {
        if (!($form instanceof PaymentForm))
            throw new CException(Sii::t('sii','invalid argument - non-PaymentForm object'));

        if ($form->status == Process::PAID || $form->status == Process::COMPLETED)
            throw new CException(Sii::t('sii','Payment already made'));

        if ($form->status != Process::UNPAID)
            throw new CException(Sii::t('sii','Payable must be in UNPAID status'));

        if ($form->amount <= 0)
            throw new CException(Sii::t('sii','Payment amount must be greater than zero'));

        switch ($form->method) {
            default:
                $this->_createPaymentRecord($form->payer,$form);
                return Process::OK;
        }         
    }       
    /**
     * Refund (rollback) full or partial payment
     * 
     * @param type $initiator The person who do the refund
     * @param PaymentForm $form
     * @return type
     * @throws CException
     */
    public function refund($form)
    {
        if (!($form instanceof PaymentForm))
            throw new CException(Sii::t('sii','invalid argument - non-PaymentForm object'));

        if ($form->status != Process::REFUND)
            throw new CException(Sii::t('sii','Payable must be in REFUND status'));

        if ($form->amount <= 0)
            throw new CException(Sii::t('sii','Refund amount should be greater than zero'));

        if (!isset($form->payer))
            throw new CException(Sii::t('sii','Payer not found'));

        if (!isset($form->recipient))
            throw new CException(Sii::t('sii','Recipient not found'));

        switch ($form->method) {
            default:
                throw new CException(Sii::t('sii','Please select other payment method.'));
        }                 
    }     
    /**
     * Void a payment record 
     * 
     * @param type $user
     * @param type $referenceNo
     * @return type
     * @throws CException
     */
    public function void($form)
    {
        if (!($form instanceof PaymentForm))
            throw new CException(Sii::t('sii','invalid argument - non-PaymentForm object'));

        if (!isset($form->payer))
            throw new CException(Sii::t('sii','Payer not found'));

        if (!isset($form->reference_no))
            throw new CException(Sii::t('sii','Reference no not set'));

        $model = Payment::model()->mine($form->payer)->paymentType(Payment::SALE)->referenceNo($form->reference_no)->find();
        if ($model==null)
            throw new CException(Sii::t('sii','Payer not found'));

        $model->type = Payment::VOID;
        $this->validate($form->payer, $model, false);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
        ));
    }        
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $paymentForm mmodel to update
     * @return CModel $model
     * @throws CException
     */
    private function _createPaymentRecord($user,$paymentForm)
    {        
        $model = new Payment();
        $model->attributes = $paymentForm->getAttributes(array('type','reference_no','trace_no','currency'));
        $model->payment_no = $this->_generatePaymentNo();
        $model->payment_method = $paymentForm->getPaymentMethodData();
        $model->amount = $paymentForm->type==Payment::REFUND?-$paymentForm->amount:$paymentForm->amount;//refund set to negative
        
        $this->validate($user, $model, false);
        $model->account_id = $user;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
        ));
    }
        
    private function _generatePaymentNo()
    {
        return base_convert(time()+rand(1,99),10,9);
    }
    
}

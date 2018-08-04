<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.exceptions.*");
Yii::import("common.modules.billings.models.Receipt");
Yii::import("common.modules.billings.models.ReceiptItem");
Yii::import("common.modules.billings.components.ReceiptNumberGenerator");
/**
 * Description of ReceiptManager
 * @author kwlok
 */
class ReceiptManager extends ServiceManager 
{
    /**
     * Create receipt
     * @param array $orderData Generic receipt data format
     * array (
     *   'currency' =>
     *   'reference' =>
     *   'items' => [
     *      [..item 1..],
     *      [..item 2..]
     *      ...
     *   ]
     * )
     * @return type
     * @throws CException
     */
    public function create($user,$orderData=[],$send=true)
    {
        if (empty($orderData))
            throw new CException(Sii::t('sii','Receipt order data cannot be empty'));

        $orderKeyFields = ['type','currency','reference','items'];
        $fieldExists = function($array,$keyFields) {
            $found = true;
            foreach ($keyFields as $field) {
                if (!array_key_exists($field, $array)){
                    $found = false;            
                    break;
                }
            }
            return $found;
        };
        //Check order data
        if (!$fieldExists($orderData,$orderKeyFields))
            throw new CException(Sii::t('sii','Missing order key information'));
        
        //Check order item data
        $itemKeyFields = ReceiptItem::mandatoryFields();
        $items = [];
        foreach ($orderData['items'] as $item) {
            
            if (!is_array($item))
                throw new CException(Sii::t('sii','Invalid item data format'));
            
            if (!$fieldExists($item,$itemKeyFields))
                throw new CException(Sii::t('sii','Missing item key information'));
            
            $i = new ReceiptItem();
            foreach ($itemKeyFields as $field) {
                $i->$field = $item[$field];//populate key fields
            }
            foreach ($item as $key => $value) {
                if (!in_array($key, $itemKeyFields))
                    $i->remarks[$key] = $value;//store other fields in remarks
            }
            if (!$i->validate())
                throw new CException(Helper::htmlErrors($i->errors));
            
            $items[] = $i->toArray();
        }
        //[1]create receipt object
        $receipt = new Receipt();
        $receipt->setType($orderData['type']);//IMPORTANT: MUST SET to pick up the correct receipt template
        $receipt->account_id = $user;//account_id is required for receipt number generator
        $receipt->receipt_no = (new ReceiptNumberGenerator($receipt))->generate();
        //[2]Locate transaction data of the subscription
        $receipt->items = json_encode($items);
        $receipt->amount = $receipt->getItemsTotalAmount();
        $receipt->currency = $orderData['currency'];
        $receipt->reference = $orderData['reference'];
        if (!$receipt->validate())
            $this->throwValidationErrors ($receipt->errors);
        
        $receipt = $this->execute($receipt, [
            'insert'=>self::EMPTY_PARAMS,
        ],$receipt->getScenario());
        
        logInfo(__METHOD__.' ok', $receipt->attributes);
        //save file run after insert so that create_time is generated and required to be used in receipt
        $receipt->receipt_file = $receipt->saveFile();
        $receipt->update();
        logInfo(__METHOD__.' receipt file created', $receipt->receipt_file);
        
        if ($send)
            $this->send($receipt);
        
        return $receipt;
    }  
    /**
     * Send out receipt by email
     * @param Receipt $receipt
     */
    public function send(Receipt $receipt)
    {
        //send receipt email
        $this->notificationManager->send($receipt);
    }
    /**
     * This updates the previously created payment reference no to receipt no
     * 
     */
    public function updatePaymentReference($referenceNo,$receiptNo)
    {
        //Search back payment record, and change it payment reference no to receipt no
        $payment = Payment::model()->referenceNo($referenceNo)->find();
        if ($payment!=null){
            $payment->reference_no = $receiptNo;
            $payment->update();
            logInfo(__METHOD__." Payment $payment->id reference_no changed from '$referenceNo' to '$receiptNo'");
        }
    }
}

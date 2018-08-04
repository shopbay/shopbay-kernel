<?php
/**
 * For Transitional object, this view file contains the meta data required to send Messenger message
 * 
 * [1] Set the notificaiton scope
 * @see NotificationScope
 * 
 * [2] Containts the MessengerPayload params
 * @see ReceiptView
 */
echo json_encode([
    'scope'=>[
        'class'=>get_class($model->shop),
        'id'=>$model->shop->id,
    ],
    'payload'=>[
        'class'=>'MessengerPayload',
        'type'=>'receipt',//refer to RECEIPT::RECEIPT
        'view'=>'ReceiptView'
    ],
    'params'=>[
        'order_id'=>$model->id,
    ],
]);        

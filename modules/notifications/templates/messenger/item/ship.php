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
        'class'=>'ShippingPayload',
        'type'=>'notice_item',//refer to ShippingPayload::NOTICE_ITEM
        'view'=>'ShippingNoticeItemView'
    ],
    'params'=>[
        'item_id'=>$model->id,
    ],
]);    
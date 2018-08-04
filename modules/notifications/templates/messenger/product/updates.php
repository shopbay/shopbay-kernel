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
    'payload'=>[
        'class'=>'ShopPayload',
        'type'=>'product_updates',//refer to ShopPayload::PRODUCT_UPDATES
        'view'=>'ShopProductUpdatesView'
    ],
    'params'=>[],
]);    
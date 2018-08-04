<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerTemplate');
/**
 * Description of ReceiptTemplate
 *
 * @author kwlok
 */
class ReceiptTemplate extends MessengerTemplate
{
    public static $elementsLimit = 100;
    /**
     * The purchase order
     * 
     * @var Order 
     */
    protected $order;
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient,Order $order)
    {
        $this->order = $order;
        $payload = [
            'template_type' => 'receipt',
            'recipient_name'=> $this->order->getBuyerName(),
            'order_number'=> $this->order->order_no,
            'currency'=> $this->order->currency,
            'payment_method'=> $this->order->getPaymentMethodName(),        
            'timestamp'=> $this->order->create_time, 
            'order_url'=> Order::getAccessUrl($this->order->order_no, $this->order->byGuestCustomer()),
            'elements' => $this->elements,
            'address' => $this->address,
            'summary'=> $this->summary,
            'adjustments' => $this->adjustments,            
        ];
        parent::__construct($recipient,$payload);
    }    

    protected function getAddress()
    {
        if (isset($this->order->address)){
            return [
                'street_1' => $this->order->address->address1,
                'street_2' => $this->order->address->address2,
                'city' => $this->order->address->city,
                'postal_code' => $this->order->address->postcode,
                'state' => !empty($this->order->address->state)?$this->order->address->state:'State',//its mandatory by Facebook, so must return something
                'country' => $this->order->address->country,
            ];
        }
        else
            return [];
    }
    
    protected function getElements()
    {
        $elements = [];
        foreach ($this->order->items as $item) {
            $elements[] = [
                'title'=> $item->displayLanguageValue('name'),//todo to support locale
                'subtitle'=> $item->getInfo(),//todo to support locale
                'quantity'=> $item->quantity,
                'price'=> $item->total_price,
                'currency'=> $item->currency,
                'image_url'=> $item->productImageUrl,
            ];
        }
        return $elements;
    }
    
    protected function getSummary()
    {
        if ($this->order->hasDiscountFreeShipping()){
            $this->order->item_total -= $this->order->shipping_total;
        }

        return [
            'subtotal'=> $this->order->item_total,
            'shipping_cost'=> $this->order->shipping_total,
            'total_tax'=> round($this->order->getTaxTotal(),2),//todo keep 2 decimals, else facebook will return error
            'total_cost'=> $this->order->grand_total
        ];
    }
    
    protected function getAdjustments()
    {
        $adjustments = [];
        //display sale campaign discount if any
        if ($this->order->hasCampaignSale()){
            $adjustments[] = [
                'name'=> $this->order->getCampaignSaleTip(),
                'amount'=> round($this->order->getCampaignSaleDiscount(),2),
            ];
        }
        
        //display promocode campaign discount if any
        if ($this->order->hasCampaignPromocode()){
            $adjustments[] = [
                'name'=> $this->order->getCampaignPromocodeTip(),
                'amount'=> round($this->order->getCampaignPromocodeDiscount(),2),
            ];
        }        
        
        //display free shipping by campaign or promocode if any
        if ($this->order->hasDiscountFreeShipping()){
            $adjustments[] = [
                'name'=> $this->order->getDiscountFreeShippingTip(),
                'amount'=> -$this->order->shipping_total,
            ];
        }        

        return $adjustments;
    }
}
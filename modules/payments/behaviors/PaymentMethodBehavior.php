<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.behaviors.ShopParentBehavior');
/**
 * Description of PaymentMethodBehavior
 *
 * @author kwlok
 */
class PaymentMethodBehavior extends ShopParentBehavior 
{
    public function isOfflineMethod()
    {
        return $this->getOwner()->method>=PaymentMethod::OFFLINE_PAYMENT;
    }
    
    public function getMethodName($locale=null)
    {
        return $this->getOwner()->displayLanguageValue('name',$locale);
    }
    
    public function getMethodParam($attribute,$locale=null)
    {
        $params = $this->getOwner()->getMethodParams();
        if (is_array($params[$attribute]))
            $params[$attribute] = json_encode($params[$attribute]);
        return $this->getOwner()->parseLanguageValue($params[$attribute],$locale);
    }
    
    public function getMethodParams()
    {
        return json_decode($this->getOwner()->params,true);
    }
    
    public function getMethodParamsText($locale=null)
    {
        $text = new CMap();
        foreach ($this->getMethodParams() as $key => $value){
            if ($this->getOwner()->method>=PaymentMethod::OFFLINE_PAYMENT){
                $text->add(PaymentMethodForm::createSubFormInstance($this->getOwner()->method,$this->getOwner()->shop_id)->getAttributeLabel('instructions'),nl2br(Helper::purify($this->getMethodParam('instructions',$locale))));
            }
            else if ($this->getOwner()->method==PaymentMethod::PAYPAL_EXPRESS_CHECKOUT && $key=='apiMode'){
                $text->add(PaymentMethodForm::createSubFormInstance($this->getOwner()->method,$this->getOwner()->shop_id)->getAttributeLabel($key), PaypalExpressCheckoutForm::getModes($value));
            }
            else {
                $text->add(PaymentMethodForm::createSubFormInstance($this->getOwner()->method,$this->getOwner()->shop_id)->getAttributeLabel($key),nl2br(Helper::purify($this->getMethodParam($key,$locale))));
            }
        }
        return $text->toArray();
    }    

    public function getParamsAttributeAsString($attribute)
    {
        $params = $this->getOwner()->getMethodParams();
        if (is_array($params[$attribute]))
            return json_encode($params[$attribute]);
        else
            return $params[$attribute];
    }    
    
    public function getDescription($locale=null)
    {
        $md = new CMarkdown;
        if ($this->getOwner()->isOfflineMethod()){
            $message = $this->getOwner()->getMethodParam('instructions',$locale);
        }
        else {
            $message = $this->getOwner()->getMethodParam('description',$locale);
        }
        return $md->transform(Helper::purify($message));
    }
    
}

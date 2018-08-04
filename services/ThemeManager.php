<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.exceptions.*");
Yii::import('common.modules.payments.models.PaymentForm');
Yii::import('common.modules.shops.models.ShopTheme');
Yii::import("common.modules.billings.models.Receipt");
/**
 * Description of ThemeManager
 *
 * @author kwlok
 */
class ThemeManager extends ServiceManager 
{
    private $_billingManager;
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::THEME_OFFLINE;        
        logTrace(__METHOD__.' scenario',$model->getScenario());
        return $this->execute($model, [
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ],$model->getScenario());
    }
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        logTrace(__METHOD__.' scenario',$model->getScenario());
        return $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ],$model->getScenario());
    }
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        if ($model->online())
            throw new ServiceValidationException(Sii::t('sii','Theme cannot be deleted when online'));
        
        return $this->execute($model, [
                'recordActivity'=>[
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ],
                'delete'=>self::EMPTY_PARAMS,
            ],'delete');
    }    
    /**
     * Make one time payment (using BraintreeCreditCardTokenGateway)
     * @param type $user
     * @param PaymentForm $form
     * @return ShopTheme 
     * @throws CException
     */
    public function buy($user, ThemePaymentForm $form)
    {
        if (!$form->validate())
            $this->throwValidationErrors($form->getErrors());
        
        $theme = $this->findTheme($form->theme);
        $shop = $this->findShop($form->shop_id);

        $form->preparePayment($theme, $shop);

        $paymentTrace = $this->billingManager->pay($user,$form);
        
        if ($paymentTrace!=null && is_array($paymentTrace)){//contains trace info
            //Payment is successful, install theme for shop (create shop theme records)
            foreach ($theme->availableStyles as $style) {
                $themeModel = new ShopTheme();
                $themeModel->shop_id = $shop->id;
                $themeModel->theme = $theme->theme;
                $themeModel->style = $style->id;
                $themeModel->status = Process::THEME_OFFLINE;//default OFFLINE 
                $themeModel->save();
                logInfo(__METHOD__.' Install theme '.$style->theme.' style '.$style->id.' for shop ok.',$shop->id);
            }
            //Create and send receipt
            $receipt = $this->receiptManager->create($shop->account_id,[
                'type'=>Receipt::TYPE_CREDITCARD,
                'currency'=>$theme->currency,
                'reference'=>$form->reference_no,
                'items'=>[
                    [
                        'item'=>Sii::t('sii','Theme {name} for shop {shop}',['{name}'=>$theme->displayLanguageValue('name',user()->getLocale()),'{shop}'=>$shop->displayLanguageValue('name',user()->getLocale())]),
                        'amount'=>$paymentTrace['amount'],
                        'currency'=>$paymentTrace['currency'],
                        'transaction_id'=>$paymentTrace['id'],
                        'transaction_date'=>$paymentTrace['createdAt'],
                        'card_type'=>$paymentTrace['cardType'],
                        'last4'=>$paymentTrace['last4'],
                        'theme'=>$theme->theme,
                        'shop'=>$shop->id,
                    ],//one item
                ],
            ]);
            
            //Search back payment record, and change it payment reference no to receipt no
            $this->receiptManager->updatePaymentReference($form->reference_no,$receipt->receipt_no);
            
            return $themeModel;//default the last one created
        }
        else
            throw new CException(Sii::t('sii','Failed to make payment.'));
    }   
    /**
     * Return billing manager
     * @return BillingManager
     */
    protected function getBillingManager()
    {
        if (!isset($this->_billingManager)){
            $this->_billingManager = Yii::app()->getModule('billings')->serviceManager;
        }
        return $this->_billingManager;
    }
    
    protected function findTheme($theme)
    {
        return Theme::model()->locateTheme($theme)->find();
    }

    protected function findShop($shopId)
    {
        return Shop::model()->find('id='.$shopId);
    }
          
}

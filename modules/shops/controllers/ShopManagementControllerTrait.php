<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopManagementControllerTrait
 *
 * @author kwlok
 */
trait ShopManagementControllerTrait 
{
    protected function getShopSaleSummary($shop_id,$modelType)
    {
        $map = new CMap();
        $total = 0;
        $shopModel = null;
        foreach ($modelType::model()->locateShop($shop_id)->all()->findAll() as $key => $model) {
            if ($key==0)
                $shopModel = $model->shop;//only take from first element
            if ($modelType=='Order')
                $total += $model->grand_total;
            else
                $total++;
        }
        $map->add(Helper::htmlColorText(['color'=>'orange','text'=>Sii::t('sii','Total')]), $modelType=='Order'? ($shopModel!=null ? $shopModel->formatCurrency($total) : 0) : $total );
        return Helper::htmlSmartKeyValues($map->toArray(),array('style'=>'margin:0;padding:5px;'));
    }
    
    protected function getShopAssetSummary($shop_id,$modelType)
    {
        $map = new CMap();
        $totalCnt = 0;
        $onlineCnt = 0;
        $offlineCnt = 0;
        foreach ($modelType::model()->locateShop($shop_id)->all()->findAll() as $model) {
            $totalCnt++;
            $map->add(Helper::htmlColorText(['color'=>'orange','text'=>Sii::t('sii','Total')]),$totalCnt);
            if ($model->online()){
                $onlineCnt++;
                $map->add(Helper::htmlColorText($model->getStatusText()),$onlineCnt);
            }
            elseif ($model->offline()){
                $offlineCnt++;
                $map->add(Helper::htmlColorText($model->getStatusText()),$offlineCnt);
            }
        }
        return Helper::htmlSmartKeyValues($map->toArray(),array('style'=>'margin:0;padding:5px;'));
    }
    
    protected function getPaymentMethodList($shop_id,$locale=null,$showLink=true)
    {
        $list = new CList();
        foreach (PaymentMethod::model()->all()->findAllByAttributes(array('shop_id'=>$shop_id)) as $model) {
            $link = $showLink ? $model->viewUrl: false;
            $list->add(Helper::htmlColorText($model->getStatusText()).' '.
                       $this->_parseLink($model->displayLanguageValue('name',$locale), $link));
        }
        return $list->count()==0?Sii::t('sii','Not Associated'):Helper::htmlList($list,array('style'=>'margin:0;padding:5px;'));
    }

    protected function getShippingList($shop_id,$locale=null,$showLink=true)
    {
        $list = new CList();
        foreach (Shipping::model()->all()->findAllByAttributes(array('shop_id'=>$shop_id)) as $model) {
            $link = $showLink ? $model->viewUrl: false;
            $list->add( 
                Helper::htmlColorText($model->getStatusText()).' '.
                $this->_parseLink($model->displayLanguageValue('name',$locale), $link)
            );
        }
        return $list->count()==0?Sii::t('sii','Not Associated'):Helper::htmlList($list,array('style'=>'margin:0;padding:5px;'));
    }

    protected function getTaxList($shop_id,$locale=null,$showLink=true)
    {
        $list = new CList();
        foreach (Tax::model()->all()->findAllByAttributes(array('shop_id'=>$shop_id)) as $model) {
            $link = $showLink ? $model->viewUrl: false;
            $list->add( 
                Helper::htmlColorText($model->getStatusText()).' '.
                $this->_parseLink($model->getTaxText($locale), $link)
            );
        }
        return $list->count()==0?Sii::t('sii','Not Associated'):Helper::htmlList($list,array('style'=>'margin:0;padding:5px;'));
    }
    
    protected function getCampaignList($shop_id,$locale=null,$showLink=true)
    {
        $list = new CList();
        $campaignModels = array('CampaignSale'=>'name','CampaignPromocode'=>'code','CampaignBga'=>'name');
        foreach ($campaignModels as $model => $attribute) {
            foreach ($model::model()->all()->findAllByAttributes(array('shop_id'=>$shop_id)) as $model) {
                $link = $showLink ? $model->viewUrl: false;
                $list->add( 
                    Helper::htmlColorText($model->getStatusText()).' '.
                    Helper::htmlColorTag($model->getType(),$model->getTypeColor()).' '.
                    ($model->hasExpired()?$model->getExpiredTag():'').' '.
                    $this->_parseLink($model->displayLanguageValue($attribute,$locale), $link)
                );
            }
        }
        return $list->count()==0?Sii::t('sii','Not Associated'):Helper::htmlList($list,array('style'=>'margin:0;padding:5px;'));
    }
    
    public function getSectionsData($model,$form=false) 
    {
        $sections = new CList();
        if (!$form){
            //section 1: Contact
            $sections->add(array('id'=>'contact',
                                 'name'=>Sii::t('sii','Contact Information'),
                                 'heading'=>true,'top'=>true,
                                 'html'=>$this->widget('common.widgets.SDetailView', array(
                                    'data'=>$model,
                                    'columns'=>array(
                                        array(
                                            array('name'=>'contact_person','value'=>$model->prototype()?Sii::t('sii','unset'):$model->contact_person),
                                            array('name'=>'contact_no','value'=>$model->prototype()?Sii::t('sii','unset'):$model->contact_no),
                                            array('name'=>'email','value'=>$model->prototype()?Sii::t('sii','unset'):$model->email),
                                        ),
                                    ),
                                ),true)));
            //section 2: Business Address
            $sections->add(array('id'=>'address',
                                 'name'=>Sii::t('sii','Business Address'),
                                 'heading'=>true,
                                 'html'=>!$model->hasAddress()?CHtml::tag('div',array('class'=>'detail-view rounded'),Sii::t('sii','unset')):$this->widget('common.widgets.SDetailView', array(
                                    'data'=>$model->address,
                                    'columns'=>array(
                                        $model->address->toArray(),
                                        array(
                                            'image-column'=>array(
                                                'image'=>$this->renderPartial($this->module->getView('shops.shopmap'),
                                                            array('longAddress'=>$model->address->longAddress,'shopName'=>$model->name),true),
                                                'cssClass'=>SPageLayout::WIDTH_50PERCENT,
                                            ),
                                        ),
                                    ),
                                ),true)));
            //section 3: Locale
            $sections->add(array('id'=>'locale',
                                 'name'=>Sii::t('sii','Locale Information'),
                                 'heading'=>true,
                                 'html'=>$this->widget('common.widgets.SDetailView', array(
                                    'data'=>$model,
                                    'columns'=>array(
                                        array(
                                            array('name'=>'timezone','value'=>$model->prototype()?Sii::t('sii','unset'):SLocale::getTimeZones($model->timezone)),
                                            array('name'=>'language','value'=>$model->prototype()?Sii::t('sii','unset'):SLocale::getLanguages($model->language)),
                                            array('name'=>'currency','value'=>$model->prototype()?Sii::t('sii','unset'):SLocale::getCurrencies($model->currency)),
                                            array('name'=>'weight_unit','value'=>$model->prototype()?Sii::t('sii','unset'):SLocale::getWeightUnits($model->weight_unit)),
                                        ),
                                    ),
                                ),true)));
        }
        else {
            //section 1: Contact
            $sections->add(array('id'=>'contact',
                                 'name'=>Sii::t('sii','Contact Information'),
                                 'heading'=>true,'top'=>true,
                                 'viewFile'=>$this->module->getView('shops.shopcontactform'),'viewData'=>array('model'=>$model)));
            //section 2: Address
            $sections->add(array('id'=>'address',
                                 'name'=>Sii::t('sii','Business Address'),
                                 'heading'=>true,
                                 'viewFile'=>$this->module->getView('shops.shopaddressform'),'viewData'=>array('model'=>!$model->hasAddress()?$model->loadAddressForm():$model->loadAddressFormAttributes())));
            //section 3: Locale
            $sections->add(array('id'=>'locale',
                                 'name'=>Sii::t('sii','Locale Information'),
                                 'heading'=>true,
                                 'viewFile'=>$this->module->getView('shops.shoplocaleform'),'viewData'=>array('model'=>$model)));
        }
        return $sections->toArray();
    }  
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return CDbCriteria
     */
    public function getSearchCriteria($model)
    {
        $criteria=new CDbCriteria;
        $criteria = QueryHelper::parseLocaleNameSearch($criteria, 'name', $model->name);
        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $model->create_time);
        $criteria->compare('timezone',$model->timezone,true);
        $criteria->compare('currency',$model->currency,true);
        $criteria->compare('status', QueryHelper::parseStatusSearch($model->status,ShopFilterForm::STATUS_FLAG));
        if (!empty($model->id))//attribute "id" is used as proxy to search into shipping
            $criteria->addCondition($model->constructShippingInCondition($model->id));
        if (!empty($model->slug))//attribute "slug" is used as proxy to search into payment method
            $criteria->addCondition($model->constructPaymentMethodInCondition($model->slug));

        return $criteria;
    }    

    private function _parseLink($value,$link=null)
    {
        return is_string($link)? CHtml::link(CHtml::encode($value), $link): $value;
    }
    
}

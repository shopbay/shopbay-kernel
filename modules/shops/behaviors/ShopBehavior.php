<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.themes.models.Theme');
/**
 * Description of ShopBehavior
 *
 * @author kwlok
 */
class ShopBehavior extends CActiveRecordBehavior 
{  
    public function getDefaultTheme()
    {
        return Tii::defaultTheme();
    }
    
    public function getDefaultThemeStyle()
    {
        return Tii::defaultStyle();
    }
    /**
     * A generic implementation to get all supported locale languages for this shop;
     * This can be extended to let individual shop have own supported languages
     * by having own implementation
     * @return array
     */    
    public function getLanguages()
    {
        return SLocale::getLanguages();
    }    
    /**
     * A generic implementation to get all supported locale languages for this shop;
     * This can be extended to let individual shop have own supported languages
     * by having own implementation
     * @return array
     */    
    public function getLanguageKeys()
    {
        return array_keys($this->getOwner()->getLanguages());
    }
    
    public function prototype() 
    {
        return $this->getOwner()->status==Process::SHOP_PROTOTYPE;
    }         
    
    public function approval() 
    {
        return $this->getOwner()->status==Process::SHOP_APPROVED;
    }         
    
    public function pendingApproval() 
    {
        return $this->getOwner()->status==Process::SHOP_PENDING;
    }         

    public function operational() 
    {
        return ($this->getOwner()->approval() || 
                $this->getOwner()->status==Process::SHOP_ONLINE || 
                $this->getOwner()->status==Process::SHOP_OFFLINE);
    }   
    
    public function hasLogo() 
    {
        return $this->getOwner()->image!=null;
    }   
    
    public function getBaseUrl($secure=false)
    {
        return Yii::app()->urlManager->createShopUrl('/shop',$secure);
    } 
    
    public function getDomain()
    {
        if ($this->getOwner()->getMyDomain()!=null) 
            return $this->getOwner()->getMyDomain();
        elseif ($this->getOwner()->getCustomDomain()!=null) {
            return $this->getOwner()->getCustomDomain().resolveDomain(app()->urlManager->shopDomain);
        }
        else
            return null;
    } 
    
    public function getUrl($secure=false)
    {
        if ($this->getOwner()->getCustomDomain()==null) 
            return $this->getOwner()->getBaseUrl($secure).'/'.$this->getOwner()->slug;
        else
            return request()->getScheme($secure?'https':'http').$this->getOwner()->getDomain();
    }        
    
    public function getReturnUrl()
    {
        return $this->getOwner()->url;
    }       
    
    public function getUrlForSocialMedia()
    {
        return $this->getOwner()->isSocialMediaShareAllowed()?$this->getOwner()->url:null;        
    }
    /**
     * To search into shipping names
     * @param type $shippingName
     * @param type $shopId
     * @return type
     */
    public function constructShippingInCondition($shippingName)
    {
        if (empty($shippingName))
            return null;
        //Search into all shipping name
        $shops = new CList();
        $criteria = new CDbCriteria;
        $criteria->select = 'shop_id';
        $criteria = QueryHelper::parseLocaleNameSearch($criteria, 'name', $shippingName);
        $criteria->mergeWith(Shipping::model()->mine()->getDbCriteria());
        logTrace(__METHOD__.' $criteria',$criteria);
        foreach (Shipping::model()->findAll($criteria) as $s){
            $shops->add($s->shop_id); 
        }
        return QueryHelper::constructInCondition('id',$shops);
    }   
    /**
     * To search into payment method names
     * @param type $shippingName
     * @param type $shopId
     * @return type
     */
    public function constructPaymentMethodInCondition($paymentMethodName)
    {
        if (empty($paymentMethodName))
            return null;
        //Search into all shipping name
        $shops = new CList();
        $criteria = new CDbCriteria;
        $criteria->select = 'shop_id';
        $criteria = QueryHelper::parseLocaleNameSearch($criteria, 'name', $paymentMethodName);
        $criteria->mergeWith(PaymentMethod::model()->mine()->getDbCriteria());
        logTrace(__METHOD__.' $criteria',$criteria);
        foreach (PaymentMethod::model()->findAll($criteria) as $s){
            $shops->add($s->shop_id); 
        }
        return QueryHelper::constructInCondition('id',$shops);
    }    
    /**
     * Create the standard preset of shop pages 
     * @param type $user
     */
    public function createPresetPages($user=null)
    {
        Yii::import('common.modules.shops.components.ShopPage');
        Yii::import('common.modules.pages.models.Page');
        $pages = include Yii::getPathOfAlias('common.modules.shops.data').DIRECTORY_SEPARATOR.'pages.php';
        foreach ($pages as $page => $settings) {
            $model = new Page();
            $model->account_id = isset($user) ? $user : $this->getOwner()->account_id;
            $model->owner_id = $this->getOwner()->id;
            $model->owner_type = 'Shop';
            $title = [];
            foreach ($this->getLanguageKeys() as $locale) {
                $title[$locale] = Sii::tl('sii',$settings['title'],$locale);
            }
            $model->title = json_encode($title);
            $desc = [];
            foreach ($this->getLanguageKeys() as $locale) {
                $desc[$locale] = Sii::tl('sii',$settings['desc'],$locale);
            }
            $model->desc = json_encode($desc);
            $model->slug = $settings['slug'];
            $model->status = $settings['status'];
            $model->params = json_encode($settings['params']);
            $model->save();
            logInfo(__METHOD__.' Preset page '.$page.' created for shop '.$this->getOwner()->id);
        }
    }
    /**
     * Create the standard preset of product categories
     * Default creates one 'Featured Products' category
     * @param type $user
     */
    public function createPresetProductCategories($user=null)
    {
        $model = new Category();
        $model->account_id = isset($user) ? $user : $this->getOwner()->account_id;
        $model->shop_id = $this->getOwner()->id;
        $name = [];
        foreach ($this->getLanguageKeys() as $locale) {
            $name[$locale] = Sii::tl('sii','Featured Products',$locale);
        }
        $model->name = json_encode($name);
        $model->slug = 'featured-products';
        $model->save();
        logInfo(__METHOD__.' Preset product category "featured-products" created for shop '.$this->getOwner()->id);
    }    
}

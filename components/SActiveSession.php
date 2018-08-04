<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SActiveSession
 *
 * @author kwlok
 */
class SActiveSession 
{
    //for Site
    const SITE_VIEW = '_session_siteview';
    //for Account
    const ACCOUNT_IMAGE = '_session_accountimage';
    //for Shop    
    const SHOP_ACTIVE = '_session_shopactive';//shop that is currently managed by merchant
    const SHOP_SKIPPASTDUE = '_session_shopskippastdue';//allows a grace period (24 hours) to access shop without pastdue message display
    const SHOP_IMAGE = '_session_shopimage';
    const SHOP_FAVICON = '_session_shopfavicon';
    const SHOP_BANNER = '_session_shopbanner';
    const SHOP_VISIT = '_session_shopvisit';//This stores the shop which is currently visited by user
    const SHOP_PAGE = '_session_shoppage';//This indicate shop page which is currently visited by user
    const SHOP_PAGEIMAGE = '_session_shoppageimage';//This stores the shop page single image 
    const SHOP_CART = '_session_shopcart';//This stores the shop cart url
    //for Brand    
    const BRAND_IMAGE = '_session_brandimage';
    //for Attribute    
    const ATTRIBUTE = '_session_attribute';
    //for Product    
    const PRODUCT_ACTIVE = '_session_productactive';   
    const PRODUCT_IMAGE = '_session_productimage';
    const PRODUCT_SHIPPING = '_session_productshipping';
    const PRODUCT_ATTRIBUTE = '_session_productattribute';    
    //for Product Category    
    const CATEGORY_IMAGE = '_session_categoryimage';
    const CATEGORY_SUB = '_session_categorysub';
    //for shipping
    const SHIPPING_TIER = '_session_shippingtier';
    //for Inventory    
    const INVENTORY = '_session_inventory';
    //for Campaign    
    const CAMPAIGN_BGA_IMAGE = '_session_campaignbga_image';
    const CAMPAIGN_BGA_SHIPPING = '_session_campaignbga_shipping';
    const CAMPAIGN_SALE_IMAGE = '_session_campaignsale_image';
    //for Message compose (metadata)
    const MESSAGE_COMPOSE = '_session_messagecompose';
    //for Attachment    
    const ATTACHMENT = '_session_attachment';
    //for Media
    const MEDIA = '_session_media';
    //for News    
    const NEWS_IMAGE = '_session_newsimage';
    //for Page layout
    const PAGE_MULTIIMAGE = '_session_pagemultiimage';//This stores the page multi images

    public static function count($stateVariable) 
    {
        $data = self::get($stateVariable);
        //PHP 7.2 compatibilty fix -> count(): Parameter must be an array or an object that implements Countable 
        return is_array($data) ? count(self::get($stateVariable)) : ($data!=null ? 1 : 0);
    }
    public static function exists($stateVariable) 
    {
        $data = self::get($stateVariable);
        //PHP 7.2 compatibilty fix -> count(): Parameter must be an array or an object that implements Countable 
        return is_array($data) ? count(self::get($stateVariable))>0 : $data!=null;
    }
    public static function get($stateVariable,$nullValue=[]) 
    {
        return Yii::app( )->user->getState($stateVariable, $nullValue);
    } 
    public static function set($stateVariable,$value) 
    {
        Yii::app( )->user->setState($stateVariable, $value);
    } 
    public static function add($stateVariable,$object,$field='id') 
    {
        $session = self::get($stateVariable);
        $session[$object->{$field}] = $object;//load from db and add into session 
        self::set($stateVariable, $session);
    } 
    public static function clear($stateVariable) 
    {
        self::set($stateVariable, null);
    } 
    public static function remove($stateVariable,$key) 
    {
        $delete = false;
        $session = self::get($stateVariable);
        if (isset($session[$key])){
            unset($session[$key]);//remove it from session 
            $delete = true;
        }
        self::set($stateVariable, $session);
        return $delete;//always true 
    }
    public static function load($stateVariable,$model=null,$childModel=null) 
    {
        if ($model!=null && $childModel!=null){
            $session = self::_loadFromDB(self::get($stateVariable),$model,$childModel);
            self::set($stateVariable, $session);
        }
        return self::get($stateVariable);
    }
    public static function reload($stateVariable,$newList) 
    {
         self::clear($stateVariable);
         //reload session based on newList
         foreach ($newList->toArray() as $newObject)
              self::add($stateVariable,$newObject);
    }    
    public static function loadAsSelected($stateVariable,$field)
    {
        $selected = new CMap();
        foreach (self::get($stateVariable) as $object)
            $selected->add($object->{$field},array('selected'=>true));
        return $selected->toArray();
    }       
    private static function _loadFromDB($session,$model,$childModel) 
    {
        foreach ($model->{$childModel} as $object)
            $session[$object->id] = $object;//load from db and add into session 
        return $session;
    }
    public static function debug($stateVariable,$model=null,$childModel=null)
    {
        echo CHtml::openTag('div',array('class'=>'session-debug-info'));
        echo CHtml::tag('div',array(),"$stateVariable content");
        echo dump(SActiveSession::load($stateVariable,$model,$childModel));
        echo CHtml::closeTag('div');
    }
}
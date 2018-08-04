<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.plans.models.SubscriptionPermission');
Yii::import('common.modules.payments.models.PaymentMethod');
Yii::import('common.modules.taxes.models.Tax');
Yii::import('common.modules.news.models.News');
Yii::import('common.modules.pages.models.Page');
/**
 * Description of FeatureTrait
 *
 * @author kwlok
 */
trait FeatureTrait
{
    /*
     * List of feature groups
     */
    public static $groupShops         = 'Shops';
    public static $groupProducts      = 'Products';
    public static $groupInventories   = 'Inventories';
    public static $groupShippings     = 'Shippings';
    public static $groupTaxes         = 'Taxes';
    public static $groupQuestions     = 'Questions';
    public static $groupPaymentMethods= 'PaymentMethods';
    public static $groupNews          = 'News';
    public static $groupMarketing     = 'Marketing';
    public static $groupOrders        = 'Orders';
    public static $groupCustomers     = 'Customers';
    public static $groupReports       = 'Reports';
    public static $groupCarts         = 'Carts';
    public static $groupUserAccounts  = 'groupUserAccounts';//e.g. staff management, for enterprise plan
    public static $groupChatbots      = 'groupChatbots';
    public static $groupStorage       = 'groupStorage';
    public static $groupPages         = 'Pages';
    /*
     * List of features
     */
    //$groupProducts
    public static $hasProductLimitTier1          = 'hasProductLimitTier1';
    public static $hasProductLimitTier2          = 'hasProductLimitTier2';
    public static $hasProductLimitTier3          = 'hasProductLimitTier3';
    public static $hasProductLimitTierN          = 'hasProductLimitTierN';//TierN means unlimited
    public static $hasProductLimitTierFree       = 'hasProductLimitTierFree';//for free plan
    public static $hasProductCategoryLimitTier1  = 'hasProductCategoryLimitTier1';
    public static $hasProductCategoryLimitTier2  = 'hasProductCategoryLimitTier2';
    public static $hasProductCategoryLimitTier3  = 'hasProductCategoryLimitTier3';
    public static $hasProductCategoryLimitTierN  = 'hasProductCategoryLimitTierN';
    public static $hasProductSubcategoryLimitTier1  = 'hasProductSubcategoryLimitTier1';
    public static $hasProductSubcategoryLimitTier2  = 'hasProductSubcategoryLimitTier2';
    public static $hasProductSubcategoryLimitTier3  = 'hasProductSubcategoryLimitTier3';
    public static $hasProductSubcategoryLimitTierN  = 'hasProductSubcategoryLimitTierN';
    public static $hasProductBrandLimitTier1     = 'hasProductBrandLimitTier1';
    public static $hasProductBrandLimitTier2     = 'hasProductBrandLimitTier2';
    public static $hasProductBrandLimitTierN     = 'hasProductBrandLimitTierN';
    public static $importProductsByFile          = 'importProductsByFile';//but subject to product limit
    //$groupShops
    public static $hasShopLimitTier1             = 'hasShopLimitTier1';
    public static $hasShopLimitTier2             = 'hasShopLimitTier2';
    public static $hasShopLimitTier3             = 'hasShopLimitTier3';
    public static $hasShopLimitTierN             = 'hasShopLimitTierN';
    public static $hasShopDesignTool             = 'hasShopDesignTool';
    public static $hasCustomDomain               = 'hasCustomDomain';
    public static $hasShopDashboard              = 'hasShopDashboard';
    public static $hasShopThemeLimitTier1        = 'hasShopThemeLimitTier1';
    public static $hasShopThemeLimitTier2        = 'hasShopThemeLimitTier2';
    public static $hasShopThemeLimitTier3        = 'hasShopThemeLimitTier3';
    public static $hasShopThemeLimitTierN        = 'hasShopThemeLimitTierN';
    public static $hasCSSEditing                 = 'hasCSSEditing';
    //$groupInventories
    public static $manageInventory               = 'manageInventory';
    public static $receiveLowStockAlert          = 'receiveLowStockAlert';
    //$groupShippings
    public static $hasShippingLimitTier1         = 'hasShippingLimitTier1';
    public static $hasShippingLimitTier2         = 'hasShippingLimitTier2';
    public static $hasShippingLimitTier3         = 'hasShippingLimitTier3';
    public static $hasShippingLimitTierN         = 'hasShippingLimitTierN';
    //$groupTaxes
    public static $hasTaxLimitTier1              = 'hasTaxLimitTier1';
    public static $hasTaxLimitTier2              = 'hasTaxLimitTier2';
    public static $hasTaxLimitTier3              = 'hasTaxLimitTier3';
    public static $hasTaxLimitTierN              = 'hasTaxLimitTierN';
    //$groupQuestions
    public static $manageQuestions               = 'manageQuestions';
    //$groupPaymentMethods
    public static $hasPaymentMethodLimitTier1    = 'hasPaymentMethodLimitTier1';
    public static $hasPaymentMethodLimitTier2    = 'hasPaymentMethodLimitTier2';
    public static $hasPaymentMethodLimitTier3    = 'hasPaymentMethodLimitTier3';
    public static $hasPaymentMethodLimitTierN    = 'hasPaymentMethodLimitTierN';
    //$groupNews
    public static $hasNewsLimitTier1             = 'hasNewsLimitTier1';
    public static $hasNewsLimitTier2             = 'hasNewsLimitTier2';
    public static $hasNewsLimitTierN             = 'hasNewsLimitTierN';
    public static $broadcastNewsToSubscribers    = 'broadcastNewsToSubscribers';
    //$groupMarketing
    public static $hasSaleCampaignLimitTier1     = 'hasSaleCampaignLimitTier1';
    public static $hasSaleCampaignLimitTier2     = 'hasSaleCampaignLimitTier2';
    public static $hasSaleCampaignLimitTierN     = 'hasSaleCampaignLimitTierN';
    public static $hasBGACampaignLimitTier1      = 'hasBGACampaignLimitTier1';
    public static $hasBGACampaignLimitTier2      = 'hasBGACampaignLimitTier2';
    public static $hasBGACampaignLimitTierN      = 'hasBGACampaignLimitTierN';
    public static $hasPromocodeCampaignLimitTier1= 'hasPromocodeCampaignLimitTier1';
    public static $hasPromocodeCampaignLimitTier2= 'hasPromocodeCampaignLimitTier2';
    public static $hasPromocodeCampaignLimitTierN= 'hasPromocodeCampaignLimitTierN';
    public static $addShopToFacebookPage         = 'addShopToFacebookPage';
    public static $hasSocialMediaShareButton     = 'hasSocialMediaShareButton';
    public static $supportGoogleTagManager       = 'supportGoogleTagManager';
    public static $hasEmailTemplateConfigurator  = 'hasEmailTemplateConfigurator';
    public static $hasSEOConfigurator            = 'hasSEOConfigurator';
    //$groupOrders
    public static $processOrders                 = 'processOrders';
    public static $customizeOrderNumber          = 'customizeOrderNumber';
    //$groupCustomers
    public static $manageCustomers               = 'manageCustomers';
    public static $trackCustomerBehaviors        = 'trackCustomerBehaviors';
    //$groupReports
    public static $hasProfessionalReports        = 'hasProfessionalReports';
    //$groupUserAccounts
    public static $supportMultipleUsers          = 'supportMultipleUsers';
    //$groupCarts
    public static $recoverAbandonedCarts         = 'recoverAbandonedCarts';
    //$groupChatbots
    public static $integrateFacebookMessenger    = 'integrateFacebookMessenger';
    //$groupStorage
    public static $hasStorageLimitTier1          = 'hasStorageLimitTier1';
    public static $hasStorageLimitTier2          = 'hasStorageLimitTier2';
    public static $hasStorageLimitTier3          = 'hasStorageLimitTier3';
    public static $hasStorageLimitTierN          = 'hasStorageLimitTierN';
    public static $hasStorageLimitTierFree       = 'hasStorageLimitTierFree';
    //$groupPages
    public static $hasPageLimitTier1             = 'hasPageLimitTier1';
    public static $hasPageLimitTier2             = 'hasPageLimitTier2';
    public static $hasPageLimitTier3             = 'hasPageLimitTier3';
    public static $hasPageLimitTierN             = 'hasPageLimitTierN';
    /*
     * List of feature params
     */
    public static $modelClass  = 'modelClass';
    public static $upperLimit  = 'upperLimit';
    public static $period      = 'period';
    public static $modelFilter = 'modelFilter';
    public static $mineFilter  = 'mineFilter';
    public static $counterFilter= 'counterFilter';
    public static $unlimited   = 0;
    /**
     * Feature group description
     * @return array
     */
    public static function siiGroup()
    {
        return [
            static::$groupProducts => Sii::t('sii','Products Management'), 
            static::$groupShops => Sii::t('sii','Shops'), 
            static::$groupInventories => Sii::t('sii','Inventory Management'),
            static::$groupShippings => Sii::t('sii','Shippings Management'),
            static::$groupTaxes => Sii::t('sii','Tax Management'),
            static::$groupQuestions => Sii::t('sii','Questions Management'), 
            static::$groupPaymentMethods => Sii::t('sii','Payment Methods Management'), 
            static::$groupNews => Sii::t('sii','News Blog'), 
            static::$groupMarketing => Sii::t('sii','Marketing Tools'), 
            static::$groupCustomers => Sii::t('sii','Customers Management'), 
            static::$groupOrders => Sii::t('sii','Orders Management'), 
            static::$groupReports => Sii::t('sii','Reports'), 
            static::$groupCarts => Sii::t('sii','Shopping Cart'), 
            static::$groupUserAccounts => Sii::t('sii','User Accounts Management'), 
            static::$groupChatbots => Sii::t('sii','Chatbots'), 
            static::$groupStorage => Sii::t('sii','Storage'), 
            static::$groupPages => Sii::t('sii','Pages Management'), 
        ];
    }       
    /**
     * Feature description
     * @return array
     */
    public static function siiName()
    {
        $sii = new CMap();
        foreach (Feature::arrayDataProvider() as $feature) {
            $sii->add($feature['name'], $feature['displayText']);
        }
        return $sii->toArray();
    }       
    /**
     * Feature params
     * @return array
     */
    public static function siiParams($name)
    {
        foreach (Feature::arrayDataProvider() as $feature) {
            if ($feature['name']==$name){
                return json_decode($feature['params'],true);
            }
        }
        return [];
    }     
    /**
     * Feature Map
     * @return type
     */
    public static function getMap()
    {
        return [
            static::$groupProducts => [
                ['id'=>100,'name'=>static::$hasProductLimitTier1,'params'=>json_encode(['upperLimit'=>25,'modelClass'=>'Product','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Product::model()->displayName(Helper::PLURAL)])],
                ['id'=>101,'name'=>static::$hasProductLimitTier2,'params'=>json_encode(['upperLimit'=>250,'modelClass'=>'Product','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Product::model()->displayName(Helper::PLURAL)])],
                ['id'=>102,'name'=>static::$hasProductLimitTier3,'params'=>json_encode(['upperLimit'=>2500,'modelClass'=>'Product','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Product::model()->displayName(Helper::PLURAL)])],
                ['id'=>103,'name'=>static::$hasProductLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Product','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Product::model()->displayName(Helper::PLURAL)])],
                ['id'=>104,'name'=>static::$hasProductCategoryLimitTier1,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'Category','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Category::model()->displayName(Helper::PLURAL)])],
                ['id'=>105,'name'=>static::$hasProductCategoryLimitTier2,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'Category','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Category::model()->displayName(Helper::PLURAL)])],
                ['id'=>106,'name'=>static::$hasProductCategoryLimitTier3,'params'=>json_encode(['upperLimit'=>25,'modelClass'=>'Category','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Category::model()->displayName(Helper::PLURAL)])],
                ['id'=>107,'name'=>static::$hasProductCategoryLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Category','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Category::model()->displayName(Helper::PLURAL)])],
                ['id'=>108,'name'=>static::$hasProductSubcategoryLimitTier1,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'CategorySub','modelFilter'=>'locateCategory','mineFilter'=>false]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CategorySub::model()->displayName(Helper::PLURAL)])],
                ['id'=>109,'name'=>static::$hasProductSubcategoryLimitTier2,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'CategorySub','modelFilter'=>'locateCategory','mineFilter'=>false]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CategorySub::model()->displayName(Helper::PLURAL)])],
                ['id'=>110,'name'=>static::$hasProductSubcategoryLimitTier3,'params'=>json_encode(['upperLimit'=>25,'modelClass'=>'CategorySub','modelFilter'=>'locateCategory','mineFilter'=>false]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CategorySub::model()->displayName(Helper::PLURAL)])],
                ['id'=>111,'name'=>static::$hasProductSubcategoryLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'CategorySub','modelFilter'=>'locateCategory','mineFilter'=>false]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>CategorySub::model()->displayName(Helper::PLURAL)])],
                ['id'=>112,'name'=>static::$hasProductBrandLimitTier1,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'Brand','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Brand::model()->displayName(Helper::PLURAL)])],
                ['id'=>113,'name'=>static::$hasProductBrandLimitTier2,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'Brand','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Brand::model()->displayName(Helper::PLURAL)])],
                ['id'=>114,'name'=>static::$hasProductBrandLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Brand','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Brand::model()->displayName(Helper::PLURAL)])],
                ['id'=>115,'name'=>static::$importProductsByFile,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Bulk Import Products')],
                ['id'=>116,'name'=>static::$hasProductLimitTierFree,'params'=>json_encode(['upperLimit'=>100,'modelClass'=>'Product','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Product::model()->displayName(Helper::PLURAL)])],
            ],
            static::$groupShops => [
                ['id'=>200,'name'=>static::$hasShopLimitTier1,'params'=>json_encode(['upperLimit'=>1,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Shop::model()->displayName()])],
                ['id'=>201,'name'=>static::$hasShopLimitTier2,'params'=>json_encode(['upperLimit'=>2,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Shop::model()->displayName(Helper::PLURAL)])],
                ['id'=>202,'name'=>static::$hasShopLimitTier3,'params'=>json_encode(['upperLimit'=>3,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Shop::model()->displayName(Helper::PLURAL)])],
                ['id'=>203,'name'=>static::$hasShopLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Shop::model()->displayName(Helper::PLURAL)])],
                ['id'=>204,'name'=>static::$hasShopDesignTool,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Shop Design Tool')],
                ['id'=>205,'name'=>static::$hasCustomDomain,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Shop custom domain')],
                ['id'=>206,'name'=>static::$hasShopDashboard,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Shop dashboard')],
                ['id'=>207,'name'=>static::$hasShopThemeLimitTier1,'params'=>json_encode(['upperLimit'=>3,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Free {n} shop themes',['{n}'=>3])],
                ['id'=>208,'name'=>static::$hasShopThemeLimitTier2,'params'=>json_encode(['upperLimit'=>20,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Free {n} shop themes',['{n}'=>20])],
                ['id'=>209,'name'=>static::$hasShopThemeLimitTier3,'params'=>json_encode(['upperLimit'=>100,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Free {n} shop themes',['{n}'=>100])],
                ['id'=>210,'name'=>static::$hasShopThemeLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Shop','modelFilter'=>null]),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited free shop themes')],
                ['id'=>211,'name'=>static::$hasCSSEditing,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','CSS Editing')],
            ],
            static::$groupInventories => [
                ['id'=>300,'name'=>static::$manageInventory,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Manage Inventory')],
                ['id'=>301,'name'=>static::$receiveLowStockAlert,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Receive low inventory alert')],
            ],
            static::$groupShippings => [
                ['id'=>400,'name'=>static::$hasShippingLimitTier1,'params'=>json_encode(['upperLimit'=>3,'modelClass'=>'Shipping','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Shipping::model()->displayName(Helper::PLURAL)])],
                ['id'=>401,'name'=>static::$hasShippingLimitTier2,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'Shipping','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Shipping::model()->displayName(Helper::PLURAL)])],
                ['id'=>402,'name'=>static::$hasShippingLimitTier3,'params'=>json_encode(['upperLimit'=>20,'modelClass'=>'Shipping','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Shipping::model()->displayName(Helper::PLURAL)])],
                ['id'=>403,'name'=>static::$hasShippingLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Shipping','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Shipping::model()->displayName(Helper::PLURAL)])],
            ],
            static::$groupTaxes => [
                ['id'=>500,'name'=>static::$hasTaxLimitTier1,'params'=>json_encode(['upperLimit'=>1,'modelClass'=>'Tax','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Tax::model()->displayName(Helper::PLURAL)])],
                ['id'=>501,'name'=>static::$hasTaxLimitTier2,'params'=>json_encode(['upperLimit'=>2,'modelClass'=>'Tax','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Tax::model()->displayName(Helper::PLURAL)])],
                ['id'=>502,'name'=>static::$hasTaxLimitTier3,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'Tax','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Tax::model()->displayName(Helper::PLURAL)])],
                ['id'=>503,'name'=>static::$hasTaxLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Tax','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Tax::model()->displayName(Helper::PLURAL)])],
            ],
            static::$groupQuestions => [
                ['id'=>600,'name'=>static::$manageQuestions,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Manage Questions')],
            ],
            static::$groupPaymentMethods => [
                ['id'=>700,'name'=>static::$hasPaymentMethodLimitTier1,'params'=>json_encode(['upperLimit'=>2,'modelClass'=>'PaymentMethod','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>PaymentMethod::model()->displayName(Helper::PLURAL)])],
                ['id'=>701,'name'=>static::$hasPaymentMethodLimitTier2,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'PaymentMethod','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>PaymentMethod::model()->displayName(Helper::PLURAL)])],
                ['id'=>702,'name'=>static::$hasPaymentMethodLimitTier3,'params'=>json_encode(['upperLimit'=>20,'modelClass'=>'PaymentMethod','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>PaymentMethod::model()->displayName(Helper::PLURAL)])],
                ['id'=>703,'name'=>static::$hasPaymentMethodLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'PaymentMethod','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>PaymentMethod::model()->displayName(Helper::PLURAL)])],
            ],
            static::$groupNews => [
                ['id'=>800,'name'=>static::$hasNewsLimitTier1,'params'=>json_encode(['upperLimit'=>5,'period'=>'1 month','modelClass'=>'News','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object} per month',['{object}'=>News::model()->displayName(Helper::PLURAL)])],
                ['id'=>801,'name'=>static::$hasNewsLimitTier2,'params'=>json_encode(['upperLimit'=>30,'period'=>'1 month','modelClass'=>'News','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object} per month',['{object}'=>News::model()->displayName(Helper::PLURAL)])],
                ['id'=>802,'name'=>static::$hasNewsLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'period'=>'1 month','modelClass'=>'News','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>News::model()->displayName(Helper::PLURAL)])],
                ['id'=>803,'name'=>static::$broadcastNewsToSubscribers,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Broadcast news to subcribers')],
            ],
            static::$groupMarketing => [
                ['id'=>900,'name'=>static::$hasSaleCampaignLimitTier1,'params'=>json_encode(['upperLimit'=>1,'modelClass'=>'CampaignSale','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CampaignSale::model()->displayName()])],
                ['id'=>901,'name'=>static::$hasSaleCampaignLimitTier2,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'CampaignSale','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CampaignSale::model()->displayName(Helper::PLURAL)])],
                ['id'=>902,'name'=>static::$hasSaleCampaignLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'CampaignSale','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>CampaignSale::model()->displayName(Helper::PLURAL)])],
                ['id'=>903,'name'=>static::$hasBGACampaignLimitTier1,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'CampaignBga','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} Product Campaigns')],
                ['id'=>904,'name'=>static::$hasBGACampaignLimitTier2,'params'=>json_encode(['upperLimit'=>30,'modelClass'=>'CampaignBga','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} Product Campaigns')],
                ['id'=>905,'name'=>static::$hasBGACampaignLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'CampaignBga','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited Product Campaigns')],
                ['id'=>906,'name'=>static::$hasPromocodeCampaignLimitTier1,'params'=>json_encode(['upperLimit'=>1,'modelClass'=>'CampaignPromocode','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CampaignPromocode::model()->displayName()])],
                ['id'=>907,'name'=>static::$hasPromocodeCampaignLimitTier2,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'CampaignPromocode','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>CampaignPromocode::model()->displayName(Helper::PLURAL)])],
                ['id'=>908,'name'=>static::$hasPromocodeCampaignLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'CampaignPromocode','modelFilter'=>'locateShop']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>CampaignPromocode::model()->displayName(Helper::PLURAL)])],
                ['id'=>909,'name'=>static::$addShopToFacebookPage,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Add shop to Facebook Page')],
                ['id'=>910,'name'=>static::$supportGoogleTagManager,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Support Google Tag Manager')],
                ['id'=>911,'name'=>static::$hasSocialMediaShareButton,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Share on Social Media')],
                ['id'=>912,'name'=>static::$hasEmailTemplateConfigurator,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Configure Email Templates')],
                ['id'=>913,'name'=>static::$hasSEOConfigurator,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Configure SEO')],
            ],
            static::$groupOrders => [
                ['id'=>1000,'name'=>static::$processOrders,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Process Orders')],
                ['id'=>1001,'name'=>static::$customizeOrderNumber,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Customized Order Number')],
            ],
            static::$groupCustomers => [
                ['id'=>1100,'name'=>static::$manageCustomers,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Manage Customers')],
                ['id'=>1101,'name'=>static::$trackCustomerBehaviors,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Track Customer Behaviors')],
            ],
            static::$groupReports => [
                ['id'=>1200,'name'=>static::$hasProfessionalReports,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Professional Reports')],
            ],
            static::$groupUserAccounts => [
                ['id'=>1300,'name'=>static::$supportMultipleUsers,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Staff Management')],
            ],
            static::$groupCarts => [
                ['id'=>1400,'name'=>static::$recoverAbandonedCarts,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Recover Abandoned Carts')],
            ],
            static::$groupChatbots => [
                ['id'=>1500,'name'=>static::$integrateFacebookMessenger,'params'=>null,'rule'=>'SubscriptionRule','displayText'=>Sii::t('sii','Chatbot for Facebook Messenger')],
            ],
            static::$groupStorage => [
                //1 Megabyte = 1,048,576 Bytes
                //1 Gigabyte = 1,073,741,824 Bytes
                //200MBytes
                ['id'=>1600,'name'=>static::$hasStorageLimitTier1,'params'=>json_encode(['upperLimit'=>209715200]),'rule'=>'StorageUpperLimitRule','displayText'=>Sii::t('sii','Up to {limit}',['{limit}'=>Helper::formatBytes(209715200)])],
                //1GBytes
                ['id'=>1601,'name'=>static::$hasStorageLimitTier2,'params'=>json_encode(['upperLimit'=>1073741824]),'rule'=>'StorageUpperLimitRule','displayText'=>Sii::t('sii','Up to {limit}',['{limit}'=>Helper::formatBytes(1073741824)])],
                //10GBytes
                ['id'=>1602,'name'=>static::$hasStorageLimitTier3,'params'=>json_encode(['upperLimit'=>10737418240]),'rule'=>'StorageUpperLimitRule','displayText'=>Sii::t('sii','Up to {limit}',['{limit}'=>Helper::formatBytes(10737418240)])],
                ['id'=>1603,'name'=>static::$hasStorageLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited]),'rule'=>'StorageUpperLimitRule','displayText'=>Sii::t('sii','Unlimited storage')],
                //250MBytes
                ['id'=>1604,'name'=>static::$hasStorageLimitTierFree,'params'=>json_encode(['upperLimit'=>262144000]),'rule'=>'StorageUpperLimitRule','displayText'=>Sii::t('sii','Up to {limit}',['{limit}'=>Helper::formatBytes(262144000)])],
            ],
            static::$groupPages => [
                ['id'=>1700,'name'=>static::$hasPageLimitTier1,'params'=>json_encode(['upperLimit'=>5,'modelClass'=>'Page','modelFilter'=>'locateShop','counterFilter'=>'countPage']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Page::model()->displayName(Helper::PLURAL)])],
                ['id'=>1701,'name'=>static::$hasPageLimitTier2,'params'=>json_encode(['upperLimit'=>10,'modelClass'=>'Page','modelFilter'=>'locateShop','counterFilter'=>'countPage']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Page::model()->displayName(Helper::PLURAL)])],
                ['id'=>1702,'name'=>static::$hasPageLimitTier3,'params'=>json_encode(['upperLimit'=>50,'modelClass'=>'Page','modelFilter'=>'locateShop','counterFilter'=>'countPage']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Up to {n} {object}',['{object}'=>Page::model()->displayName(Helper::PLURAL)])],
                ['id'=>1703,'name'=>static::$hasPageLimitTierN,'params'=>json_encode(['upperLimit'=>static::$unlimited,'modelClass'=>'Page','modelFilter'=>'locateShop','counterFilter'=>'countPage']),'rule'=>'SubscriptionUpperLimitRule','displayText'=>Sii::t('sii','Unlimited {object}',['{object}'=>Page::model()->displayName(Helper::PLURAL)])],
            ],
        ];
    }    
    /**
     * Output all features as array
     * @return type
     */
    public static function arrayDataProvider()
    {
        $array = [];
        foreach (Feature::getMap() as $group => $features) {
            foreach ($features as $feature) {
                $feature['group'] = $group;//add group data into feature row
                $array[] = $feature;
            }
        }
        return $array;
    }    
    /**
     * Return name description
     * @param type $field
     * @return type
     */
    public static function getNameDesc($field)
    {
        if (isset(static::siiName()[$field])){
            //if $field is defined
            $desc = static::siiName()[$field];
            if (strpos($desc, '{n}')!==false){
                $feature = Feature::getRecord($field);
                $desc = Sii::t('sii',$desc,['{n}'=>$feature->getParam(Feature::$upperLimit)]);
            }
            return $desc;    
        }
        else
            return $field;
    }    
    /**
     * Return name description
     * @param type $field
     * @return type
     */
    public static function getGroupDesc($field)
    {
        return isset(static::siiGroup()[$field]) ? Feature::siiGroup()[$field] : '';
    }    
    /**
     * Make feaure name conforms to certain pattern (template)
     * @param type $feature
     */
    public static function patternize($feature)
    {
        if (substr($feature, -1)=='N')
            return substr($feature, 0, -1).Feature::LIMIT_PATTERN;
        else
            return $feature;
    }
    /**
     * Check if the permission contains the pattern
     * @param type $permission
     * @return type
     */
    public static function hasPattern($permission,$pattern=Feature::LIMIT_PATTERN)
    {
        return strpos($permission, $pattern)!==false;
    }   
    
    /**
     * Feature rbac rules
     * @return array
     */
    public static function siiRbacRules()
    {
        return [
            'SubscriptionRule' => Sii::t('sii','Subscription Default Rule'), 
            'SubscriptionUpperLimitRule' => Sii::t('sii','Subscription Upper Limit Rule'), 
            'StorageUpperLimitRule' => Sii::t('sii','Storage Upper Limit Rule'), 
        ];
    }      
}

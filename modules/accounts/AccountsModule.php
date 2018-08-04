<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AccountsModule
 *
 * @author kwlok
 */
class AccountsModule extends SModule 
{
    /**
     * @property default home page view to redirect after login.
     */
    public $welcomeView = 'index';
    /**
     * @property default model class to load in welcome view.
     */
    public $welcomeModel = 'undefined';
    /**
     * @property default welcome controller behavior to load in welcome view.
     */
    public $welcomeControllerBehavior;
    /**
     * @property default route (based on the user->returnUrl value) after login. Default to "false".
     * [1] If value is true, will route to Yii::app()->user->returnUrl after succesful login
     * [2] If value is false, will route to $afterLoginRoute after succesful login
     * [3] If $afterLoginRoute is not set, will route to request()->getUrlReferrer() after succesful login
     * Other possible route can be e.g. "/welcome"
     */
    public $useReturnUrl = false;
    /**
     * @property default route after login. Default to "null".
     * If value is null or empty, will route to request()->getUrlReferrer() where user clicks login
     * Other possible route can be e.g. "/welcome"
     */
    public $afterLoginRoute = null;
    /**
     * @property default route after logout. Default to "null".
     * If value is null or empty, will route to request()->getUrlReferrer() where user clicks logout
     * Other possible route can be e.g. "/" (home page)
     */
    public $afterLogoutRoute = null;
    /**
     * @property default route after login from other subdomain. Default to "false".
     * If value is true, system will route to request()->getUrlReferrer() where user clicks login at other subdomain page
     * Use case e.g. login from shop custom domain page: shopName.mydomain.com
     */
    public $redirectSubdomainAfterLoginRoute = false;
    /**
     * @property default route after login from shop storefront. Default to "false".
     * If value is true, system will route to request()->getUrlReferrer() where user clicks login at shop page
     * Use case e.g. login from shop page with url: www.mydomain.com/shop/My-shop-name
     */
    public $redirectShopAfterLoginRoute = false;
    /**
     * @property default login route using api login. Default to "null".
     * If value is not null, it means the login will use api instead of direct authentication 
     * @see app\modules\oauth2server\controllers\LoginController
     */
    public $apiLoginRoute;
    /**
     * @property default logout route using api login. 
     * @see app\modules\oauth2server\controllers\LogoutController
     */
    public $apiLogoutRoute = 'oauth2/logout';
    /**
     * @property default activation route using api login. Default to "null".
     * If value is not null, it means the login will use api instead of direct activation 
     * @see app\modules\oauth2server\controllers\LoginController::actionActivate()
     */
    public $apiActivateRoute;
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'accounts',
                'pathAlias'=>'accounts.assets',
            ],
        ];
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'accounts.models.*',
            'accounts.components.*',
            'accounts.oauth.*',
            'accounts.users.*',
            'common.widgets.simagemanager.SImageManager',
            'common.widgets.simagemanager.models.SingleImageForm',
            'common.widgets.SButtonColumn',
            'common.widgets.spagelayout.SPageLayout',
            'common.widgets.spageindex.controllers.SPageIndexController',
            'common.widgets.SStateDropdown',
        ]);
        // import module dependencies classes
        $this->setDependencies([
            'modules'=> [
                'activities'=>[
                    'common.modules.activities.models.Activity',
                ],             
                //Below are for Activity use
                'questions'=>[
                    'common.modules.questions.models.Question',
                ],             
                'payments'=>[
                    'common.modules.payments.models.PaymentMethod',
                ],             
                'shops'=>[
                    'common.modules.shops.models.ShopTheme',
                    'common.modules.shops.models.ShopSetting',
                ],             
                'news'=>[
                    'common.modules.news.models.News',
                ],             
                'tutorials'=>[
                    'common.modules.tutorials.models.Tutorial',
                ],             
                'customers'=>[
                    'common.modules.customers.models.Customer',
                ],             
                'analytics'=>[
                    'common.modules.analytics.charts.*',
                ],            
                'media'=>[
                    'common.modules.media.models.SessionMedia',
                ],             
            ],
            'views'=>[      
                'profilesidebar'=>'common.modules.accounts.views.profile._sidebar',
                'customermessages'=>'messages.recent',
                'customernews'=>'news.recent',
                'activity'=>'common.modules.activities.views.base._activity',
                'tasklist'=>'tasks.tasklist',
            ],
            'classes'=>[
                'listview'=>'common.widgets.SListView',
            ],    
            'images'=>[
                'datepicker'=>['common.assets.images'=>'datepicker.gif'],
            ],
        ]);  

        $this->defaultController = 'management';

        $this->registerScripts();
    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.AccountManager',
                'model'=>['AccountProfile','Account'],
                'runMode'=>$this->serviceMode,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }    
    /**
     * Check if to do authentication using Api
     * @return boolean
     */
    public function getIsApiAuthMode()
    {
        return $this->apiLoginRoute!=null;
    }
}
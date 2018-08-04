<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spageindex.controllers.SPageIndexController');
Yii::import('common.modules.plans.models.Feature');
Yii::import('common.modules.plans.components.ShopSubscriptionFilter');
/**
 * Description of ShopParentController
 *
 * @author kwlok
 */
class ShopParentController extends SPageIndexController 
{
    /*
     * Url referrers that do not need to check session object    
     */
    protected $shopStateVariable = SActiveSession::SHOP_ACTIVE;
    protected $productStateVariable = SActiveSession::PRODUCT_ACTIVE;
    protected $breadcrumbsModuleName;
    protected $showBreadcrumbsModule = true;
    protected $breadcrumbsControllerName;
    protected $showBreadcrumbsController = true;
    protected $includePageFilter = false;
    private $_parentShop;
    private $_parentProduct;
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();        
        if (!isset($this->breadcrumbsModuleName))
            $this->breadcrumbsModuleName = $this->getModule()->displayName(Helper::PLURAL);
        if (!isset($this->breadcrumbsControllerName))
            $this->breadcrumbsControllerName = ucfirst(SActiveRecord::plural($this->id));
    }  
    /**
     * The filter method for 'shopsubscription' access filter.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     */
    public function filterShopSubscription($filterChain)
    {
        $filter = new ShopSubscriptionFilter();
        $filter->filter($filterChain);
    }
    /**
     * This controller includes 'shopsubscription' filter 'ShopSubscriptionFilter' 
     * to verify all subscription controls and validations 
     * @return array action filters
     */
    public function filters()
    {
        $filters = array_merge(parent::filters(),['shopsubscription']);
        if (isset($filters['subscription']))
            unset($filters['subscription']);//remove parent filter
        /**
         * This controller will not include 'shopsubscription' filter when session shop not found or is null, 
         * the filter is redirecting back to "/shops"; This is to prevent hitting error: ERR_TOO_MANY_REDIRECTS
         */
        foreach ($filters as $key => $value) {
            if ($value=='shopsubscription' && in_array($_SERVER['REQUEST_URI'],['/shops','/shop','/shops/management','/shops/management/index']))
                unset($filters[$key]);
        }
        
        logTrace(__METHOD__,$filters);
        return $filters;        
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),[
            $this->serviceNotAvailableAction =>[
                'class'=>'common.modules.plans.actions.ServiceNotAvailableAction',
                'breadcrumbs'=>$this->getBreadcrumbsData(),
                'pageHeading'=>$this->viewName,
                'flashId'=>$this->modelType,
                'shopModel'=>$this->getParentShop(),
            ],                
        ]);
    }  
    
    public function hasSessionShop()
    {
        return SActiveSession::get($this->shopStateVariable)!=null;
    }
    
    public function getSessionShop()
    {
        return SActiveSession::get($this->shopStateVariable);
    }
   
    public function setSessionShop($shop)
    {
        SActiveSession::set($this->shopStateVariable, $shop->id);
        $this->setParentShop($shop);
    }
    
    public function setSessionProduct($product)
    {
        if (isset($product)){
            SActiveSession::set($this->productStateVariable, $product->id);
            $this->setParentProduct($product);
            $this->setSessionShop($product->shop);
        }
    }
    
    protected function loadSessionParentShop() 
    {
        if (SActiveSession::get($this->shopStateVariable)!=null)
            $this->setParentShop($this->loadModel(SActiveSession::get($this->shopStateVariable), $this->getModule()->parentShopModelClass, false));
    }
    
    protected function unloadSessionParentShop() 
    {
        SActiveSession::set($this->shopStateVariable, null);
        $this->setParentShop(null);
    }
    
    protected function unloadSessionParentProduct() 
    {
        SActiveSession::set($this->productStateVariable, null);
        $this->setParentProduct(null);
    }
    
    protected function loadSessionParentProduct() 
    {
        if (SActiveSession::get($this->productStateVariable)!=null){
            $product = $this->loadModel(SActiveSession::get($this->productStateVariable),$this->getModule()->parentProductModelClass, false);
            $this->setParentProduct($product);
            $this->setParentShop($product->shop);
        }
    }
    
    public function hasParentShop()
    {
        return $this->_parentShop != null;
    }

    public function getParentShop()
    {
        return $this->_parentShop;
    }
    
    protected function setParentShop($model)
    {
        return $this->_parentShop = $model;
    }

    public function hasParentProduct()
    {
        return $this->_parentProduct != null;
    }

    public function getParentProduct()
    {
        return $this->_parentProduct;
    }

    protected function setParentProduct($model)
    {
        return $this->_parentProduct = $model;
    }
    /**
     * Setup up parent shop and product into session
     * @param CActiveRecord $model
     */
    protected function setParents($model)
    {
        if ($model===null){
            if ($this->getParentProduct()!=null && $this->getParentShop()===null){
                $shop = $this->getParentProduct()->{strtolower($this->getModule()->parentShopModelClass)};
                $this->setSessionShop($shop);
            }
        }
        elseif ($model instanceof Shop){
            $this->setSessionShop($model);
        }
        else {
            if ($model instanceof Product)
                $this->setParentProduct($model);
            
            $shop = $model->{strtolower($this->getModule()->parentShopModelClass)};
            $this->setSessionShop($shop);
        }
    }
    
    public function getBreadcrumbsData($action='index',$model=null)
    {
        $this->setParents($model);

        $data = array();
        //level 1: shop name
        if ($this->hasParentShop())
            //Home » Shop » [shop name]
            $data = array(
                Sii::t('sii','Shops') => url(SActiveRecord::plural(strtolower($this->getModule()->parentShopModelClass))),
                $this->getParentShop()->parseName(user()->getLocale()) => $this->getParentShop()->viewUrl,
            );
//        else
//            //Home » [module]
//            $data = array(
//                $this->getModule()->displayName() => url($this->getModule()->id)
//            );

        //Level 2.1 or 2.2 is activated either one
        //level 2.1: product name
        //Home » Shop » [shop name] » [product name]
        if ($this->hasParentProduct()){
            if ($this->id=='attribute' || $this->uniqueId=='inventories/management'){
                //Only Products/AttributeController and Inventories/ManagementController need to show "product name"
                $data = array_merge($data, [
                    $this->getParentProduct()->displayLanguageValue('name',user()->getLocale()) => $this->getParentProduct()->viewUrl,
                ]);
            }
        }
        //level 2.2: product group
        //Home » Shop » [shop name] » product
        if (in_array($this->uniqueId,['brands/management','products/category'])){
            //Only Brands and Categories controllers need to show "product"
            $data = array_merge($data, [
                Sii::t('sii','Products') => url('products'),
            ]);
        }
        
        //level 3: module name
        //(...level 1, level 2...) » [module name]
        if ($this->showBreadcrumbsModule){
            if ($this->uniqueId=='questions/management'){
                $data = array_merge($data,[
                    $this->breadcrumbsModuleName => url($this->uniqueId),
                ]);
            }
            else if (strtolower($action)!='index'){
                if ($this->uniqueId=='products/attribute'){
                    $data = array_merge($data,[
                        $this->breadcrumbsModuleName => url($this->uniqueId),
                    ]);
                }
                else {
                    $data = array_merge($data,[
                        $this->breadcrumbsModuleName => $this->getParentShop()->gotoUrl($this->module->id),
                    ]);
                }
            }
            else {
                if ($this->showBreadcrumbsController)
                    $data = array_merge($data,[
                        $this->breadcrumbsModuleName => url($this->getModule()->id),
                    ]);
                else
                    $data = array_merge($data,[
                        $this->breadcrumbsModuleName,
                    ]);
            }
        }

        //level 4: controller name
        //(...level 1, level 2...) » [module name] » [controller name]
        if ($this->showBreadcrumbsController){
            if (strtolower($action)!='index'){
                $data = array_merge($data,[
                    $this->breadcrumbsControllerName => $this->getParentShop()->gotoUrl($this->module->id.'/'.$this->id),
                ]);
            }
            else {
                if ($this->id!=$this->getModule()->defaultController)
                    $data = array_merge($data,[
                        $this->breadcrumbsControllerName,
                    ]);
            }
        }
        
        //level 5: action name
        //(...level 1, level 2..) » [module name] » [controller name] » action (e.g. view, update etc)
        if (strtolower($action)!='index'){
            $data = array_merge($data,[
                $action,
            ]);
        }
        
        return $data; 
    } 
    /**
     * Return the data provider based on scope and searchModel
     * 
     * @see SPageIndex
     * @return CDbCriteria
     */
    public function getDataProvider($scope,$searchModel=null)
    {
        $type = $this->modelType;
        $type::model()->resetScope();
        if ($this->hasParentShop()){
            $finder = $type::model()->{$this->modelFilter}()->locateShop($this->getParentShop()->id)->all();
            $scope = 'locateShop';//for below logging purpose
        }
        else {
            $finder = $type::model()->{$this->modelFilter}()->{$scope}();
        }
        if ($searchModel!=null)
            $finder->getDbCriteria()->mergeWith($searchModel->getDbCriteria());
        logTrace(__METHOD__.' '.$type.'->'.$this->modelFilter.'()->'.$scope.'()',$finder->getDbCriteria());
        return new CActiveDataProvider($finder, [
                  'criteria'=>['order'=>$this->sortAttribute.' DESC'],
                  'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                  'sort'=>false,
                ]);
    }   
    
    public function getPageMenu($model,$page,$params=[])
    {
        if (!$this->hasSessionShop()){
            $this->setSessionShop($model->shop);
        }
        
        $menu = [
            ['id'=>'view','title'=>Sii::t('sii','View {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','view'),  'url'=>isset($params['viewUrl'])?$params['viewUrl']:$model->viewUrl,'linkOptions'=>['class'=>$page=='view'?'active':'']],
            ['id'=>'update','title'=>Sii::t('sii','Update {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','update'), 'url'=>isset($params['updateUrl'])?$params['updateUrl']:['update', 'id'=>$model->id],'visible'=>isset($params['updateVisible'])?$params['updateVisible']:$model->updatable(),'linkOptions'=>['class'=>$page=='update'?'active':'']],
            ['id'=>'create','title'=>Sii::t('sii','Create {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','create'), 'url'=>isset($params['createUrl'])?$params['createUrl']:['create'],'visible'=>isset($params['createVisible'])?$params['createVisible']:true],
            ['id'=>'delete','title'=>Sii::t('sii','Delete {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(), 
                    'linkOptions'=>[
                        'submit'=>isset($params['deleteUrl'])?$params['deleteUrl']:['delete','id'=>$model->id],
                        'onclick'=>'$(\'.page-loader\').show();',
                        'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',['{object}'=>strtolower($model->displayName())])
                    ]
            ],
        ];
        //load additional menu
        if (isset($params['extraMenu'])){
            $menu = array_merge($menu,$params['extraMenu']);
        }
        if (isset($params['activateUrl'])){
            $menu = array_merge($menu,[
                        ['id'=>'activate','title'=>Sii::t('sii','Activate {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','activate'), 'visible'=>$model->activable(), 
                            'linkOptions'=>[
                                'submit'=>$params['activateUrl'],
                                'onclick'=>'$(\'.page-loader\').show();',
                                'confirm'=>Sii::t('sii','Are you sure you want to activate this {object}?',['{object}'=>strtolower($model->displayName())]),
                                //'class'=>'activate',
                        ]],
                    ]);
        }
        if (isset($params['deactivateUrl'])){
            $menu = array_merge($menu,[
                        ['id'=>'deactivate','title'=>Sii::t('sii','Deactivate {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','deactivate'), 'visible'=>$model->deactivable(), 
                            'linkOptions'=>[
                                'submit'=>$params['deactivateUrl'],
                                'onclick'=>'$(\'.page-loader\').show();',
                                'confirm'=>Sii::t('sii','Are you sure you want to deactivate this {object}?',['{object}'=>strtolower($model->displayName())]),
                                //'class'=>'deactivate',
                        ]],
                    ]);
        }
        if (isset($params['saveOnclick'])&&isset($params['showSave'])){
            $menu = array_merge($menu,[
                            ['id'=>'save','title'=>Sii::t('sii','Save {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','save'), 'linkOptions'=>['onclick'=>$params['saveOnclick'],'class'=>'primary-button']],
                        ]);
        }
        return $menu;
    }
    /**
     * Get page sidebar
     * @return type
     */
    public function getPageSidebar($pageFilter=false,$pageQuickMenu=[])
    {
        if ($this->hasParentShop()){
            $sidebar = ['sidebars' => [
                SPageLayout::COLUMN_LEFT => [
                    'content'=>$this->renderView('shops.sidebar',['model'=>$this->getParentShop()],true),
                    'cssClass'=>  SPageLayout::WIDTH_05PERCENT,
                    ],
                ]
            ];
            if ($pageFilter){
                $this->filterFormQuickMenu = $pageQuickMenu;
                $sidebar['sidebars'] = array_merge($sidebar['sidebars'],$this->getPageFilterSidebarData());
            }
            return $sidebar;
        }
        else
            return [];
    }
    /**
     * Get page 
     * @param array $content main page content
     * @param type $sidebar If to include sidebar
     * @return type
     */
    public function getPage($content,$sidebar=true)
    {
        if (isset($content['flash'])){
            if ($this->hasSessionShop())
                $content['flash'] = $this->getWizards($content['flash']);
        }
        
        if ($sidebar){
            return $this->widget('common.widgets.spage.SPage',array_merge(
                                $content,
                                $this->getPageSidebar()     
                            ));            
        }
        else
            return $this->widget('common.widgets.spage.SPage',$content);      
    }    
    /**
     * Return page index
     */
    public function getPageIndex($content)
    {
        if (isset($content['flash'])){
            $content['flash'] = $this->getWizards($content['flash']);
        }
        return $this->spageindexWidget($content);
    }

    public function getWizards($flash)
    {
        return $this->loadWizards($flash,user(),$this->hasParentShop()?$this->getParentShop():null);
    }
}

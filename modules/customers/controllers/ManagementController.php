<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends SPageIndexController 
{
    protected $sessionActionsExclude = array();

    public function init()
    {
        parent::init();
        // check if module requisites exists
        $missingModules = $this->getModule()->findMissingModules();
        if ($missingModules->getCount()>0)
            user()->setFlash($this->getId(),array('message'=>Helper::htmlList($missingModules),
                                            'type'=>'notice',
                                            'title'=>Sii::t('sii','Missing Module')));  
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Customer';
        $this->modelFilter = 'merchantAccount';
        $this->viewName = Sii::t('sii','Customers Management');
        $this->route = 'customers/management/index';
        //$this->pageViewOption = SPageIndex::VIEW_GRID;
        $this->sortAttribute = 'update_time';
        $this->searchMap = [
            'customer' => 'alias_name',
            'date' => 'create_time',
            'address' => 'address',
            'tags' => 'tags',
            'notes' => 'notes',
        ];
        //-----------------//  
        // SPageFilter Configuration
        // @see SPageFilterControllerTrait
        $this->filterFormModelClass = 'CustomerFilterForm';
        $this->filterFormHomeUrl = url('customers/management');
        $this->filterFormQuickMenu = [
            ['id'=>'create','title'=>Sii::t('sii','Create Customer'),'subscript'=>Sii::t('sii','create'), 'url'=>['create']],           
        ];
        //-----------------
        // Exclude following actions from rights filter 
        //-----------------
        $this->rightsFilterActionsExclude = [
            $this->serviceNotAvailableAction,
        ];
        //-----------------//
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),[
            'view'=>[
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
                'modelFilter'=>null,
                'pageTitleAttribute'=>'alias_name',
                'accountAttribute'=>'account_id',
            ],                    
            'create'=>[
                'class'=>'common.components.actions.CreateAction',
                'model'=>$this->modelType,
                'setAttributesMethod'=>'setModelAttributes',
            ],
            'update'=>[
                'class'=>'common.components.actions.UpdateAction',
                'model'=>$this->modelType,
                'setAttributesMethod'=>'setModelAttributes',
            ], 
            'delete'=>[
                'class'=>'common.components.actions.DeleteAction',
                'model'=>$this->modelType,
            ],
            $this->serviceNotAvailableAction => [
                'class'=>'common.modules.plans.actions.ServiceNotAvailableAction',
                'breadcrumbs'=>[
                    Sii::t('sii','Customers'),
                ],
                'pageHeading'=>$this->viewName,
                'flashId'=>$this->modelType,
            ],                
        ]);
    }      
    /**
     * @inheritdoc
     */
    public function getDataProvider($scope,$searchModel=null)
    {
        $type = $this->modelType;
        $type::model()->resetScope();
        $finder = $type::model()->{$this->modelFilter}(user()->getId())->{$scope}();
        if ($searchModel!=null)
            $finder->getDbCriteria()->mergeWith($searchModel->getDbCriteria());
        logTrace(__METHOD__.' '.$type.'->'.$this->modelFilter.'()->'.$scope.'()',$finder->getDbCriteria());
        return new CActiveDataProvider($finder, [
            'criteria'=>[
                'order'=>$this->sortAttribute.' DESC',
            ],
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
            'sort'=>false,
        ]);
    }        
    
    public function setModelAttributes($model)
    {
        if (isset($_POST[$this->modelType])) {
            $model->attributes = $_POST[$this->modelType];
        }
        if (isset($_POST['CustomerAddressForm'])) {
            $addressForm = $this->getCustomerAddressForm();
            $addressForm->attributes = $_POST['CustomerAddressForm'];
            $addressData = new CustomerAddressData();
            $addressData->fillData($addressForm);
            $model->setAddressData($addressData);
        }
        
        if (isset($model))
            return $model;
        else
            throwError400(Sii::t('sii','Bad Request'));
    } 

    private $_addressForm;
    protected function getCustomerAddressForm($model=null) 
    {
        if (!isset($this->_addressForm))
            $this->_addressForm = new CustomerAddressForm();
        
        if (isset($model))
            $this->_addressForm->fillForm($model->getAddressData());
        
        return $this->_addressForm;
    }
    
    public function getPageMenu($model)
    {
        return array(
            array('id'=>'view','title'=>Sii::t('sii','View {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','view'),  'url'=>$model->viewUrl,'linkOptions'=>array('class'=>$this->action->id=='view'?'active':'')),
            array('id'=>'create','title'=>Sii::t('sii','Create {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','create'), 'url'=>array('create')),
            array('id'=>'update','title'=>Sii::t('sii','Update {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','update'), 'url'=>array('update', 'id'=>$model->id),'visible'=>$model->updatable(user()->getId()),'linkOptions'=>array('class'=>$this->action->id=='update'?'active':'')),
            array('id'=>'delete','title'=>Sii::t('sii','Delete {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(user()->getId()), 
                    'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),
                                         'onclick'=>'$(\'.page-loader\').show();',
                                         'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',array('{object}'=>strtolower($model->displayName()))))),
        );

    }
    
    protected function getSectionsData($model) 
    {
        $sections = new CList();
        //section 1: Shops
        if ($model->customerData->hasShopData()){
            $sections->add(array('id'=>'shops',
                             'name'=>$model->getAttributeLabel('shops').($model->hasShops()?' ('.$model->shopCount.')':''),
                             'heading'=>true,'top'=>true,
                             'html'=>$this->widget($this->module->getClass('listview'), 
                                        array(
                                            'dataProvider'=> new CArrayDataProvider($model->getShopData(),array('keyField'=>false)),
                                            'template'=>'{items}',
                                            'itemView'=>'_shop',
                                        ),true)
                    ));
        }
        //section 2: Last Orders
        $sections->add(array('id'=>'orders',
                             'name'=>$model->getAttributeLabel('orders'),
                             'heading'=>true,
                             'html'=>$this->widget($this->module->getClass('listview'), 
                                        array(
                                            'dataProvider'=>$model->getRecentOrders(),
                                            'template'=>'{items}',
                                            'itemView'=>'_order',
                                        ),true)
                    ));
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
        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $model->create_time);
        $criteria->compare('alias_name',$model->alias_name,true);
        $criteria->compare('address',$model->address,true);
        $criteria->compare('tags',$model->tags,true);
        $criteria->compare('notes',$model->notes,true);

        return $criteria;
    }    
    
}

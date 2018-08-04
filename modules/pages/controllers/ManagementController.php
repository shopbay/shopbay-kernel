<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.components.TransitionControllerActionTrait');
Yii::import('common.modules.pages.controllers.PageManagementControllerTrait');
/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends ShopParentController 
{
    use PageManagementControllerTrait, TransitionControllerActionTrait;
    
    public function init()
    {
        parent::init();
        //-----------------
        // ShopParentController Configuration
        //-----------------
        $this->showBreadcrumbsModule = true;
        $this->breadcrumbsModuleName = Page::model()->displayName(Helper::PLURAL);        
        $this->showBreadcrumbsController = false;
        $this->includePageFilter = true;
        $this->loadSessionParentShop();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Page';
        $this->viewName = Sii::t('sii','Pages Management');
        $this->route = 'pages/management/index';
        $this->sortAttribute = 'update_time';
        $this->searchMap = [
            'page' => 'title',
            'date' => 'create_time',
            'status' => 'status',
        ];   
        //-----------------//  
        // SPageFilter Configuration
        // @see SPageFilterControllerTrait
        $this->filterFormModelClass = 'PageFilterForm';
        $this->filterFormHomeUrl = url('pages/management');
        //-----------------
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),$this->pageActions(),$this->transitionActions(false,true));
    }  
    /**
     * OVERRIDE METHOD
     * @inheritdoc
     */
    public function getPageOwner()
    {
        if ($this->hasParentShop())
            return $this->getParentShop();
        else
            return null;
    }
    /**
     * Construct page menu
     * @param type $model
     * @param type $page
     * @param mixed $params If $model is null, this becomes the customize menu
     * @return type
     */
    public function getPageMenu($model, $page, $params=[]) 
    {
        if ($model!=null){
            return array_merge(parent::getPageMenu($model, $page, $params),[
                ['id'=>'layout','title'=>Sii::t('sii','Edit Content and Layout'),'subscript'=>Sii::t('sii','layout'), 'url'=>$model->layoutUrl],
            ]);
        }
        else {
            return $params;//become menu
        }
    }
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return CDbCriteria
     */
    public function getSearchCriteria($model)
    {
        $criteria = new CDbCriteria;
        $criteria = QueryHelper::parseLocaleNameSearch($criteria, 'title', $model->title);
        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $model->create_time);
        $criteria->compare('status', QueryHelper::parseStatusSearch($model->status,PageFilterForm::STATUS_FLAG));
        return $criteria;
    }     
}

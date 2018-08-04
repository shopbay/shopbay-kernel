<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of EmailTrailController
 *
 * @author kwlok
 */
class EmailTrailController extends SPageIndexController
{
    public function init()
    {
        parent::init();
        $this->pageTitle = Sii::t('sii','System Emails');
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'MessageQueue';
        $this->viewName = Sii::t('sii','System Emails');
        $this->route = 'notifications/email/index';
        $this->pageViewOption = SPageIndex::VIEW_GRID;
        $this->enableViewOptions = false;
        $this->enableSearch = false;
        $this->defaultScope = 'email';
        $this->sortAttribute = 'update_time';
        //-----------------//
        $this->registerCommonFiles();
        $this->registerCssFile('application.assets.css','application.css');

    }
    /**
     * View action
     * @throws CHttpException
     */
    public function actionView()
    {
        $search = current(array_keys($_GET));//take the first key as search attribute
        $type = $this->modelType;
        $model = $type::model()->retrieve($search)->find();
        if($model===null){
            throw new CHttpException(404,Sii::t('sii','Page not found'));
        }

        $this->render('view',['model'=>$model]);
    }
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        $filters = new CMap();
        $filters->add('email',Helper::htmlIndexFilter('All', false));
        return $filters->toArray();
    }
    /**
     * OVERRIDE METHOD
     * Return the data provider based on scope and searchModel
     * @see SPageIndexController::getDataProvider()
     *
     * @return mixed CActiveDataProvider or null
     */
    public function getDataProvider($scope,$searchModel=null)
    {
        $type = $this->modelType;
        $type::model()->resetScope();
        $finder = $type::model()->{$scope}();
        if ($searchModel!=null)
            $finder->getDbCriteria()->mergeWith($searchModel->getDbCriteria());
        logTrace($type.'->'.$scope.'()',$finder->getDbCriteria());
        return new CActiveDataProvider($finder, array(
            'criteria'=>array(
                'order'=>$this->sortAttribute.' DESC'),
            'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
            'sort'=>false,
        ));
    }
}

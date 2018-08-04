<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.SPageFilter');
Yii::import('common.widgets.spagelayout.SPageLayout');
/**
 * Description of SPageFilterControllerTrait
 *
 * @author kwlok
 */
trait SPageFilterControllerTrait 
{
    public $filterFormModelClass;//this model should be initiated by parent controller
    public $filterFormHomeUrl;//this model should be initiated by parent controller
    public $filterFormQuickMenu;//this model should be initiated by parent controller
    
    public function getPageFilterSidebarData($config=[],$columnPostion=SPageLayout::COLUMN_RIGHT,$width=SPageLayout::WIDTH_25PERCENT)
    {
        return [
            $columnPostion => [
                'content'=> $this->spagefilterWidget($config,true),
                'cssClass'=> $width,
            ],
        ];
    }

    public function spagefilterWidget($config=[],$return=false)
    {
        if (!isset($config['formModel']))
            $config['formModel'] = $this->filterFormModel;
        
        if (!empty($this->filterFormQuickMenu))
            $config['quickMenu'] = $this->filterFormQuickMenu;
            
        if ($return)
            return $this->widget('SPageFilter',$config,true);
        else
            $this->widget('SPageFilter',$config); 
    }
    
    private $_m;//form model instance
    protected function getFilterFormModel()
    {
        if (!isset($this->_m)){
            $this->_m = new $this->filterFormModelClass;
            $this->_m->actionUrl =  $this->filterFormHomeUrl.'?';
            if ($this->getScope()!=null)
                $this->_m->actionUrl .=  '&scope='.$this->getScope();
            if ($this->getPageView()!=null)
                $this->_m->actionUrl .=  '&option='.$this->getPageView();
            
            $this->_m = $this->assignFilterFormAttributes($this->_m);
            $this->_m->validateFields();//activate error check
        }
        return $this->_m;
    }
    /**
     * Auto populate form attribut values
     * @see SPageIndexController::searchModel
     * @see SPageIndexController::searchMap
     * @see SPageIndexAction::getSearchModel()
     */
    protected function assignFilterFormAttributes($form)
    {
        if ($this->searchModel!=null){
            foreach ($this->searchMap as $searchField => $modelAttribute) {
                if (!empty($this->searchModel->$modelAttribute))
                    $form->$searchField = $this->searchModel->$modelAttribute;
            }
        }
        logTrace(__METHOD__, $form->fields);
        return $form;
    }
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SeriesController
 *
 * @author kwlok
 */
class SeriesController extends SPageIndexController
{
    protected $formType = 'TutorialSeriesForm';
    
    public function init()
    {
        parent::init();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'TutorialSeries';
        $this->viewName = Sii::t('sii','Tutorial Series');
        $this->route = 'tutorials/series/index';
        //$this->pageViewOption = SPageIndex::VIEW_GRID;
        $this->sortAttribute = 'update_time';
        //-----------------//
        $this->rightsFilterActionsExclude = [
            'ckeditorimageupload',
        ];
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
            ],                    
            'create'=>[
                'class'=>'common.components.actions.LanguageCreateAction',
                'form'=>$this->formType,
                'createModelMethod'=>'prepareForm',
                'setModelAttributesMethod'=>'setModelAttributes',
                'service'=>'createSeries',
            ],
            'update'=>[
                'class'=>'common.components.actions.LanguageUpdateAction',
                'form'=>$this->formType,
                'loadModelMethod'=>'prepareForm',
                'setModelAttributesMethod'=>'setModelAttributes',
                'service'=>'updateSeries',
            ], 
            'delete'=>[
                'class'=>'common.components.actions.DeleteAction',
                'model'=>$this->modelType,
                'service'=>'deleteSeries',
            ],
            'submit'=>[
                'class'=>'TransitionAction',
                'modelType'=>$this->modelType,
                'flashMessageMultilang'=>true,
                'flashTitle'=>Sii::t('sii','Tutorial Series Submission'),
                'flashMessage'=>Sii::t('sii','"{name}" is submitted successfully.'),
            ],
            'ckeditorimageupload'=>[
                'class'=>'common.components.actions.CkeditorImageUploadAction',
            ],
        ]);
    } 
    
    public function prepareForm($id=null)
    {
        if (isset($id)){//update action
            $form = new $this->formType($id, 'update');
            $form->loadLocaleAttributes();
            $form->loadSeoParamsAttributes();
            $form->tags = explode(',', $form->tags);//convert back to array
            $form->serializeTutorialsValue();
        }
        else {
            $form = new $this->formType(Helper::NULL,TutorialSeries::model()->getCreateScenario());
            $form->tutorials = [];//init empty tutorials
        }
        return $form;
    }        

    public function setModelAttributes($form)
    {
        //[1] Format tags value
        if (!isset($_POST[$this->formType]['tags']))
            $form->tags = '';//no tag specified

        if (is_array($form->tags)){
            $form->tags = implode(',', $form->tags);
        }
        //[2]copy form attributes to model attributes
        $form->modelInstance->attributes = $form->getAttributes();
        if (isset($form->tutorials)){
            $form->modelInstance->tutorials = json_encode(explode(',', $form->tutorials));
        }
        //[3]set model scenario to follow $form scenario
        $form->modelInstance->setScenario($form->getScenario());
        //[4]Populate seo param fields, making sure seo params should not overwrite other params
        $currentParams = json_decode($form->modelInstance->params,true);
        foreach ($form->seoParams as $key => $value) {
            $currentParams[$key] = $value;//overwrite existing if any
        }
        $form->modelInstance->params = json_encode($currentParams);
        
        return $form;
    }    
    /**
     * Return section data
     * @param type $model
     * @return type
     */
    public function getSectionsData($model) 
    {
        $sections = new CList();
        //section: Process History
        $sections->add(array('id'=>'history','name'=>Sii::t('sii','Process History'),'heading'=>true,
                             'viewFile'=>$this->getModule()->getView('history'),'viewData'=>array('dataProvider'=>$model->searchTransition($model->id))));
        return $sections->toArray();
    }      
    /**
     * Return page menu (with auto active class)
     * @param type $model
     * @return type
     */
    public function getPageMenu($model)
    {
        return array(
            array('id'=>'view','title'=>Sii::t('sii','View {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','view'),  'url'=>$model->viewUrl,'linkOptions'=>array('class'=>$this->action->id=='view'?'active':'')),
            array('id'=>'create','title'=>Sii::t('sii','Create {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','create'), 'url'=>array('create')),
            array('id'=>'update','title'=>Sii::t('sii','Update {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','update'), 'url'=>array('update', 'id'=>$model->id),'visible'=>$model->updatable(),'linkOptions'=>array('class'=>$this->action->id=='update'?'active':'')),
            array('id'=>'delete','title'=>Sii::t('sii','Delete {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(), 
                    'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),
                                         'onclick'=>'$(\'.page-loader\').show();',
                                         'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',array('{object}'=>strtolower($model->displayName()))))),
            array('id'=>'submit','title'=>Sii::t('sii','Submit Tutorial Series'),'subscript'=>Sii::t('sii','submit'), 'visible'=>$model->submitable(), 
                  'linkOptions'=>array('submit'=>url('tutorials/series/submit',array('TutorialSeries[id]'=>$model->id)),
                                     'onclick'=>'$(\'.page-loader\').show();',
                                     'confirm'=>Sii::t('sii','Are you sure you want to submit this tutorial series?'),
            )),
        );
    }
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        $filters = new CMap();
        $filters->add('all',Helper::htmlIndexFilter('All', false));
        return $filters->toArray();
    }    
        
    protected function getTutorialSortableTags($tutorials)
    {
        $menu = new CMap();
        foreach ($tutorials as $id => $title) {
            $menu->add($id,CHtml::tag('div',array('class'=>'sort-item'),'<i class="fa fa-arrows"></i>'.$title));
        }
        return $menu->toArray();        
    }    
    
}

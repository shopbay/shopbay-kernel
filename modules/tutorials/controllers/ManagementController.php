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
    protected $formType = 'TutorialForm';
    
    public function init()
    {
        parent::init();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Tutorial';
        $this->viewName = Sii::t('sii','Write Tutorials');
        $this->route = 'tutorials/management/index';
        //$this->pageViewOption = SPageIndex::VIEW_GRID;
        $this->sortAttribute = 'update_time';
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
            ],                    
            'write'=>[
                'class'=>'common.components.actions.LanguageCreateAction',
                'form'=>$this->formType,
                'createModelMethod'=>'prepareForm',
                'setModelAttributesMethod'=>'setModelAttributes',
                'service'=>'write',
                'viewFile'=>'write',
            ],
            'edit'=>[
                'class'=>'common.components.actions.LanguageUpdateAction',
                'form'=>$this->formType,
                'loadModelMethod'=>'prepareForm',
                'setModelAttributesMethod'=>'setModelAttributes',
                'service'=>'edit',
                'viewFile'=>'edit',
            ], 
            'delete'=>[
                'class'=>'common.components.actions.DeleteAction',
                'model'=>$this->modelType,
            ],
            'ckeditorimageupload'=>[
                'class'=>'common.components.actions.CkeditorImageUploadAction',
            ],
            'submit'=>[
                'class'=>'TransitionAction',
                'modelType'=>$this->modelType,
                'flashTitle'=>Sii::t('sii','Tutorial Submission'),
                'flashMessage'=>Sii::t('sii','"{name}" is submitted successfully.'),
                'flashMessageMultilang'=>true,
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
        }
        else {
            $form = new $this->formType(Helper::NULL,Tutorial::model()->getCreateScenario());
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
        //section 1: Tutorial Content
        if ($model->publishable()){
            $sections->add([
                'id'=>'content',
                'name'=>Sii::t('sii','Tutorial Details'),
                'heading'=>true,
                'viewFile'=>$this->getModule()->getView('tutorials.tutorialview'),'viewData'=>['model'=>$model],
            ]);
        }
        //section 2: Process History
        $sections->add([
            'id'=>'history',
            'name'=>Sii::t('sii','Process History'),
            'heading'=>true,
            'viewFile'=>$this->getModule()->getView('history'),'viewData'=>['dataProvider'=>$model->searchTransition($model->id)],
        ]);
        return $sections->toArray();
    }      
    /**
     * Return page menu (with auto active class)
     * @param type $model
     * @return type
     */
    public function getPageMenu($model)
    {
        return [
            ['id'=>'view','title'=>Sii::t('sii','View {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','view'),  'url'=>$model->viewUrl,'linkOptions'=>['class'=>$this->action->id=='view'?'active':'']],
            ['id'=>'write','title'=>Sii::t('sii','Write {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','write'), 'url'=>['write']],
            ['id'=>'edit','title'=>Sii::t('sii','Edit {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','edit'), 'url'=>['edit', 'id'=>$model->id],'visible'=>$model->updatable(),'linkOptions'=>['class'=>$this->action->id=='edit'?'active':'']],
            ['id'=>'delete','title'=>Sii::t('sii','Delete {object}',['{object}'=>$model->displayName()]),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(), 
                    'linkOptions'=>[
                        'submit'=>['delete','id'=>$model->id],
                        'onclick'=>'$(\'.page-loader\').show();',
                        'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',['{object}'=>strtolower($model->displayName())]),
                    ]
            ],
            ['id'=>'submit','title'=>Sii::t('sii','Submit Tutorial'),'subscript'=>Sii::t('sii','submit'), 'visible'=>$model->submitable(), 
                  'linkOptions'=>[
                        'submit'=>url('tutorials/management/submit',['Tutorial[id]'=>$model->id]),
                        'onclick'=>'$(\'.page-loader\').show();',
                        'confirm'=>Sii::t('sii','Are you sure you want to submit this tutorial?'),
            ]],
        ];
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
        
}

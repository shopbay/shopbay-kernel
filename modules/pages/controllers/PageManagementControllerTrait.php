<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageManagementControllerTrait
 *
 * @author kwlok
 */
trait PageManagementControllerTrait 
{
    protected $formType = 'PageForm';           
    /**
     * For class using this trait to implement
     * @return type
     */
    public function getPageOwner()
    {
        throw new CException('Please define page owner');
    }
    /**
     * It seems model->owner_type is "auto" set to Owner object (still not know why)
     * Here is to set its owner_type back to class name of Owner at stage "beforeRender" of ReadAction
     * and everything works fine here onward
     * example error: "Object of class Shop could not be converted to string..."
     * @todo Yii BUG??
     * @see Page::beforeSave()
     * @return type
     */
    public function setPageOwner($model)
    {
        $model->refreshOwnerType();
    }
    /**
     * Declares class-based actions.
     */
    public function pageActions()
    {
        return [
            'view'=>[
                'class'=>'common.components.actions.LanguageReadAction',
                'model'=>$this->modelType,
                'pageTitleAttribute'=>'title',
                'viewFile'=>'common.modules.pages.views.management.view',
                'serviceInvokeParam'=>$this->modelType,
                'loadModelMethod'=>'findPage',
                'beforeRender'=>'setPageOwner'
            ],                    
            'create'=>[
                'class'=>'common.components.actions.LanguageCreateAction',
                'form'=>$this->formType,
                'createModelMethod'=>'prepareForm',
                'setModelAttributesMethod'=>'setModelAttributes',
                'viewFile'=>'common.modules.pages.views.management.create',
                'serviceInvokeParam'=>$this->modelType,
            ],
            'update'=>[
                'class'=>'common.components.actions.LanguageUpdateAction',
                'form'=>$this->formType,
                'loadModelMethod'=>'prepareForm',
                'setModelAttributesMethod'=>'setModelAttributes',
                'viewFile'=>'common.modules.pages.views.management.update',
                'serviceInvokeParam'=>$this->modelType,
            ],
            'delete'=>[
                'class'=>'common.components.actions.LanguageDeleteAction',
                'model'=>$this->modelType,
                'serviceInvokeParam'=>$this->modelType,
                'redirectUrl'=>$this->getIndexPageUrl(),
            ],
        ];
    }  
    
    public function prepareForm($id=null)
    {
        if (isset($id)){//update action
            $form = new $this->formType($id, 'update');
            $form->loadLocaleAttributes();
            $form->loadSeoParamsAttributes();
        }
        else {
            $form = new $this->formType(Helper::NULL,Page::model()->getCreateScenario());
            $owner = $this->getPageOwner();
            $form->owner_id = $owner->id;
            $form->owner_type = get_class($owner);
        }
        return $form;
    }

    public function setModelAttributes($form)
    {
        //[1]copy form attributes to model attributes
        $form->modelInstance->attributes = $form->getAttributes();
        //[2]set model scenario to follow $form scenario
        $form->modelInstance->setScenario($form->getScenario());
        //[3]Populate seo param fields, making sure seo params should not overwrite other params
        $currentParams = json_decode($form->modelInstance->params,true);
        foreach ($form->seoParams as $key => $value) {
            $currentParams[$key] = $value;//overwrite existing if any
        }
        $form->modelInstance->params = json_encode($currentParams);
        return $form;
    }    
    
    public function getCreatePageUrl() 
    {
        return url('pages/management/create');
    }
    
    public function getIndexPageUrl() 
    {
        return url('pages/management/index');
    }
    
    public function getPageUrl($action,$params=[]) 
    {
        return url('pages/management/'.$action,$params);
    }
    
    public function findPage()
    {
        $search = current(array_keys($_GET));//take the first key as search attribute
        return Page::model()->mine()->retrieve($search)->find();
    }

}

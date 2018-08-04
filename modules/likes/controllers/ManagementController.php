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
        $this->modelType = 'Like';
        $this->route = 'likes/management/index';
        $this->pageControl = SPageIndex::CONTROL_ARROW;
        $this->viewName = Sii::t('sii','Likes');
        $this->enableViewOptions = false;
        $this->enableSearch = false;
        $this->sortAttribute = 'update_time';
        //-----------------
        // Exclude following actions from rights filter 
        //-----------------
        $this->rightsFilterActionsExclude = [
            'toggle',
        ];
    }        
    /**
     * Toggle a model (Like or dislike).
     * This action supports either way, it depends on the passing in value of variable 'action'
     */
    public function actionToggle()
    {
        if(isset($_POST['LikeForm'])) {

            $form = new LikeForm($_POST['LikeForm']['type'],$_POST['LikeForm']['target']);
            if (isset($_POST['LikeForm']['modal']))
                $form->modal = $_POST['LikeForm']['modal'];
            if (isset($_POST['LikeForm']['formObject']))
                $form->formObject = $_POST['LikeForm']['formObject'];
            
            if (user()->isGuest){
                $type = $form->type;
                user()->returnUrl = $this->getLoginReturnUrl($type,$form->target);
                header('Content-type: application/json');
                user()->loginRequiredAjaxResponse = CJSON::encode(array('status'=>'loginrequired','url'=>user()->loginUrl));
                user()->loginRequired();
                Yii::app()->end();  
            }

            if($form->validate()){
                try {
                    $model = $this->loadModelbyObj($form->type, $form->target);
                    $model->status = $form->parseAction();
                    $model = $this->module->serviceManager->toggle(user()->getId(),$model);
                    //user()->setFlash($this->getId(),array('message'=>$this->modelType.' is posted successfully.',
                    //        'type'=>'success',
                    //        'title'=>$this->modelType.' Creation')); 
                    unset($_POST);

                    $form->switchAction();
                    $status = 'success';
                    logTrace('After switch action', $form->getAttributes());

                } catch (CException $e) {
                    logError(__METHOD__.' '.$e->getMessage());
                    user()->setFlash($this->getId(),array('message'=>$e->getMessage(),'type'=>'error','title'=>null)); 
                }
            }
            
            $counter = Yii::app()->serviceManager->getAnalyticManager()->getMetricValue(SActiveRecord::restoreTablename($form->type), $form->target, Metric::COUNT_LIKE);
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'status'=>isset($status)?$status:'failure',
                'type'=>strtolower($form->type),
                'target'=>$form->target,
                'button'=>$this->renderPartial('_button',array('model'=>$form),true),
                'total'=>$counter,
                'total_text'=>Sii::t('sii','n<=1#{n} Like|n>1#{n} Likes',array($counter)),
            ));
            Yii::app()->end();  

        }
        throwError404();

    }
    /**
     * Dislike an object.
     */
    public function actionUndo($id)
    {
        $model=$this->loadModel($id);

        if ($this->module->serviceManager->checkObjectAccess(user()->getId(),$model)){

            try {
                $model = $this->module->serviceManager->undo(user()->getId(),$model,false);
                user()->setFlash($this->getId(),array('message'=>Sii::t('sii','{object} is disliked successfully',array('{object}'=>$model->obj_name)),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Oops, but you can always like it again if you change your mind.'))); 

            } catch (CException $e) {
                user()->setFlash($this->getId(),array('message'=>$e->getMessage(),'type'=>'error','title'=>null)); 
            }

            header('Content-type: application/json');
            echo CJSON::encode(array('body'=>$this->widget($this->getModule()->getClass('listview'), 
                                    array(
                                       'id'=>$this->getScope(),
                                       'dataProvider'=>$this->getDataProvider($this->getScope()),
                                       'itemView'=>'_like_listview',
                                    ),
                                    true),
                                'flash'=>$this->sflashWidget($this->id, true)));
            Yii::app()->end();
        }
        throwError403(Sii::t('sii','Unauthorized Access'));

    }
    /**
     * Batch likes (support obj_type s_product only)
     * Skip if the model is already previously liked
     */
    public function actionBatch($ids)
    {
        $likes = new CList();
        $batch = explode(',', $ids);
        array_pop($batch);//removing the last entry ','
        $batch = array_unique($batch);//remove duplicate id, in case for whose having same id diff sku
        logTrace(__METHOD__.' likes batch',$batch);
        foreach ($batch as $id) {
             $model=$this->loadModelbyObj('product',$id);
             if ($model->likable()){
                try {
                    $model = $this->module->serviceManager->like(user()->getId(),$model,false);
                    $likes->add(Sii::t('sii','{object} is liked successfully',array('{object}'=>$model->displayLanguageValue('obj_name',user()->getLocale()))));
                } catch (CException $e) {
                    logError('Failed to save Like',$model->getErrors());
                }
             }
             else  {
                 logTrace(__METHOD__.' '.$model->obj_name.' skips like');
                 $likes->add(Sii::t('sii','Thanks for your support again! In fact, you have liked {object} before.',array('{object}'=>$model->displayLanguageValue('obj_name',user()->getLocale()))));
             }
        }

        header('Content-type: application/json');
        if (count($likes)>0)
            user()->setFlash('cart',array('message'=>Helper::htmlList($likes),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Likes Message'))); 
        else
            user()->setFlash('cart',array('message'=>Sii::t('sii','Oops, it seems we have problem save your liked items'),
                    'type'=>'error',
                    'title'=>Sii::t('sii','Likes Error'))); 
        echo CJSON::encode(array(
                    'flash'=>$this->sflashWidget('cart',true),
                 ));
        Yii::app()->end();      

    }
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        $filters = new CMap();
        $filters->add('all',Helper::htmlIndexFilter(Sii::t('sii','All'), false));
        $filters->add('shop',Helper::htmlIndexFilter(Sii::t('sii','Shop'), false));
        $filters->add('product',Helper::htmlIndexFilter(Sii::t('sii','Product'), false));
        $filters->add('campaignBga',Helper::htmlIndexFilter(Sii::t('sii','Campaign'), false));
        return $filters->toArray();
    }
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeDescription($scope)
    {
        switch ($scope) {
            case 'all':
                return Sii::t('sii','This lists everything that you have liked. If you change your mind now, you can dislike any one of them by clicking again on "Heart".');
            case 'shop':
                return Sii::t('sii','This lists every shop that you have liked.');
            case 'product':
                return Sii::t('sii','This lists every product that you have liked.');
            case 'campaignBga':
                return Sii::t('sii','This lists every campaign that you have liked.');
            default:
                return null;
        }
    }    
    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    protected function loadModelbyObj($type,$target)
    {
        $tableName = SActiveRecord::restoreTablename($type);
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$tableName,'obj_id'=>$target));
        $model = Like::model()->mine()->find($criteria);
        if ($model===null){
            $model = new Like();
            $typeModel = $this->loadModel($target,$type);
            $model->obj_type = $tableName;
            $model->obj_id = $target;
            $model->obj_name = $typeModel->name;
            $model->obj_url = $typeModel->url;
            $model->obj_pic_url = $type=='Tutorial'||$type=='Question'?$typeModel->getImageThumbnail():$typeModel->getImageUrl(Image::VERSION_ORIGINAL);
            $model->account_id = user()->getId();
            if ($type==get_class(Product::model()))
                $model->obj_src_id = $typeModel->shop->id;
        }
        return $model;
   }        
}
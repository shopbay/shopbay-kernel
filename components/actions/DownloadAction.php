<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.ReadAction");
/**
 * Description of DownloadAction
 *
 * @author kwlok
 */
class DownloadAction extends ReadAction 
{
    /**
     * The finder method for search; Default to 'findFile' 
     * 
     * @var string 
     */
    public $finderMethod = 'findFile';
    /**
     * Run the action
     */
    public function run()             
    {
        logInfo('['.$this->controller->uniqueId.'/'.$this->controller->action->id.'] '.__METHOD__.' $_GET', $_GET);
        $search = current(array_keys($_GET));//take the first key as search attribute
        if (user()->currentRole==Role::MERCHANT && $this->model=='Attachment'){
            $this->modelFilter = null;
            $model = $this->_findModel($search);//find attachment by file name only; Here assume file name is unique (already md5)
            if ($model->object!=null && Attachment::isMerchantObject($model->obj_type)){
                if ($model->object->shop->account_id==user()->getId())
                    $this->sendFile($model);//match object owner
            }
            throw new CHttpException(404,Sii::t('sii','Page not found'));
        }
        else {
            $model = $this->_findModel($search);
            $this->sendFile($model);
        }
    }         
    
    protected function sendFile($model)
    {
        if (!$model instanceof Downloadable)
            throw new CHttpException(400,Sii::t('sii','Bad request'));
        
        logTrace(__METHOD__.' filename',$model->filename);
        return Yii::app()->getRequest()->sendFile($model->filename, @file_get_contents($model->filepath));
        Yii::app()->end();
    }
}

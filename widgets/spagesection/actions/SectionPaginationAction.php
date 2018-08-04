<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SectionPaginationAction
 *
 * @author kwlok
 */
class SectionPaginationAction extends CAction 
{
    /**
     * The pagination view file
     * @var string 
     */
    public $viewFile = 'undefined';
    /**
     * Model class of the page
     * @var type 
     */
    public $model = 'undefined';
    /**
     * Handles all input requests
     * @param query params
     */
    public function run() 
    {
        if (!isset($_GET['id']))
            throwError404(Sii::t('sii','The requested page does not exist'));

        $type = $this->model;
        $model = $type::model()->mine()->findByPk($_GET['id']);
        if($model===null)
            throwError404(Sii::t('sii','The requested page does not exist'));
        
        header('Content-type: application/json');
        echo CJSON::encode($this->controller->renderPartial($this->viewFile,['model'=>$model],true));
        Yii::app()->end();      
    }    
    
}

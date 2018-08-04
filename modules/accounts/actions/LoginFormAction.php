<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.LoginForm');
/**
 * Description of LoginFormAction
 *
 * @author kwlok
 */
class LoginFormAction extends CAction
{
    public $viewFile = '_form';
    /**
     * Get login form in JSON/JSONP format
     * Example url:
     *  'QUERY_STRING' => 'callback=jQuery1111013188884400285916_1461485676825&container=%23page_modal&action=login&_=1461485676826',
     *  'REQUEST_URI' => '/shops/login?callback=jQuery1111013188884400285916_1461485676825&container=%23page_modal&action=login&_=1461485676826',
     * @see common.js openmodalbyjsonp
     */
    public function run()
    {
        $model = new LoginForm;
        $model->title = Sii::t('sii','Log in');
        if (isset($_GET['callback'])){
            header('Content-type: application/javascript');
            $data = [
                'container'=>isset($_GET['container'])?$_GET['container']:'',
                'action'=>isset($_GET['action'])?$_GET['action']:'',
                'html'=>$this->controller->renderPartial($this->viewFile,['model'=>$model],true),
            ];
            echo $_GET['callback'].'('.CJSON::encode($data).')';
        }
        else {
            header('Content-type: application/json');
            echo CJSON::encode($this->controller->renderPartial($this->viewFile,['model'=>$model],true));
        }
        Yii::app()->end();      
    }
}

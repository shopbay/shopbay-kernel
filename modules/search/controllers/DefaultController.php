<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of DefaultController
 *
 * @author kwlok
 */
class DefaultController extends SController 
{
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->pageTitle = Sii::t('sii','Search');
    }
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),array(
            'searchbehavior' => array(
                'class'=>'common.modules.search.behaviors.SearchControllerBehavior',
                'targets'=>['SearchShop','SearchProduct'],
                'filter'=>['status'=>SearchFilter::ACTIVE],
                'placeholder'=>Sii::t('sii','Search shops or products'),
            ),
        ));
    }
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', 
        );
    }
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',  
                'actions'=>array('index'),
                'users'=>array('*'),
            ),
            //default deny all users anything not specified       
            array('deny',  
                'users'=>array('*'),
            ),
        );
    }    
    /**
     * This action shows search index page
     */
    public function actionIndex()
    {
        $query = '';//empty search text for initial
        if (isset($_GET['query'])){
            $query = $_GET['query'];
            $response = $this->parseQuery($query, isset($_GET['page'])?$_GET['page']:null);
        }   
        
        if (Yii::app()->request->getIsAjaxRequest() && isset($response)){
            header('Content-type: application/json');
            if (isset($_GET['ajax'])){
                //for pagination
                echo CJSON::encode($this->getSearchResults($response));
            }
            else 
                echo CJSON::encode(array(
                    'query'=>$query,
                    'status'=>$response->status,
                    'results'=>$this->getSearchResults($response),
                ));
            
            Yii::app()->end();      
        }
        else  {
            $this->render('index',array('query'=>$query,'response'=>isset($response)?$response:$this->getSearchEmptyResponse()));
        }
    } 
    
}

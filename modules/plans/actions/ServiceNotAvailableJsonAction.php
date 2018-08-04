<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ServiceNotAvailableJsonAction
 *
 * @author kwlok
 */
class ServiceNotAvailableJsonAction extends CAction 
{
    public $postModel;
    public $postField;
    public $flashId;
    
    public function run() 
    {
        //called from subscription rules check (rejected)
        if (isset($_GET[$this->postModel])&&$_GET[$this->postModel]=='serviceNotAvailable'){
            user()->setFlash($this->flashId,array(
                'message'=>Sii::t('sii','You have not subscribed to this service: {service}',['{service}'=>Feature::siiName()[$_GET['service']]]),
                'type'=>'error',
                'title'=>Sii::t('sii','Service Not Available'),
            ));
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'status'=>'serviceNotAvailable',
                'return_url'=>$_GET['returnUrl'],
              ));
            Yii::app()->end();
        }
    }
    
}
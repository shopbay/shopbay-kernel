<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.SignupCustomerForm');
/**
 * Description of SignupCustomerAction
 * This is for guest checkout customer sign up
 * 
 * @author kwlok
 */
class SignupCustomerAction extends CAction
{
    public $accountType = 'Account';
    public $emailCondition = [];
    public $formModel = 'SignupCustomerForm';
    public $addressFormModel = 'CustomerAddressForm';
    public $formModelParams;//the form model params for construction, expects to be shop id
    public $service = 'signup';
    public $successViewData = [];//extra view data to pass when successful
    
    public function run()
    {
        if (!isset($_GET['order']))
            throwError404 (Sii::t ('sii', 'Order not found'));
        
        $order = $_GET['order'];
        $content = null;
        $form = new $this->formModel($this->formModelParams);
        $form->setAccountType($this->accountType);
        if (!empty($this->emailCondition))
            $form->setEmailCondition($this->emailCondition);
        
        if(isset($_POST['ajax']) && $_POST['ajax']==='signup-customer-form'){
            //Bugfix: should not validate 'verify_code', else its value will change by internal calls
            echo CActiveForm::validate($form,['name','email','password']);
            Yii::app()->end();
        }
        
        $orderModel = Order::model()->guest()->orderNo($order)->find();
        if ($orderModel===null){//first check if order exists in guest
            $content = CHtml::tag('div',['class'=>'form-wrapper','style'=>'padding:30px;'],Sii::t('sii','Order {order_no} not found.',['{order_no}'=>$order]));
        }
        else {
            $form->order_no = $orderModel->order_no;
            if (isset($_POST[$this->formModel])&&isset($_POST[$this->addressFormModel])){
                try {
                    $form->attributes = $_POST[$this->formModel];
                    $form->address->attributes = $_POST[$this->addressFormModel];
                    $this->controller->module->serviceManager->{$this->service}($form);
                    unset($_POST);
                    $removeFlash = '.account-creation';
                    $content = CHtml::tag('div',['class'=>'customer-signup'],$this->controller->renderPartial('complete',array_merge(['email'=>$form->email],$this->successViewData),true));

                } catch (CException $e) {
                    logError(__METHOD__.' '.$e->getTraceAsString(),[],false);
                    $form->unsetAttributes(['password','confirmPassword']);
                }  
            }
            else {//default GET request to load sign up form
                $form->alias_name = $orderModel->address->recipient;
                $form->mobile = $orderModel->address->mobile;
                $form->address->attributes = $orderModel->address->attributes;
                if ($form->isAccountExists($orderModel->buyerEmail)){
                    $message = Sii::t('sii','Email <em>{email}</em> is already registered.',['{email}'=>$orderModel->buyerEmail]);
                    $message .= Sii::t('sii','Please try other email address.');
                    user()->setFlash(get_class($form),[
                        'message'=>$message,
                        'type'=>'error',
                    ]);
                    $form->email = false;
                }
                else {
                    $form->email = $orderModel->buyerEmail;
                }
            }
            //Both GET or POST commonly loading signup form
            if (!isset($content))
                $content = CHtml::tag('div',['class'=>'customer-signup'],$this->controller->renderPartial('customer',['model'=>$form],true));
        }
        
        $modal = $this->controller->smodalWidget(null,$content,null,null,true);
        header('Content-type: application/json');
        echo CJSON::encode([
            'removeFlash'=>isset($removeFlash)?$removeFlash:false,
            'modal'=>$modal,   
        ]);            
        Yii::app()->end();

    }
}

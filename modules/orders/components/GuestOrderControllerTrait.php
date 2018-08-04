<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of GuestOrderControllerTrait
 *
 * @author kwlok
 */
trait GuestOrderControllerTrait 
{
    public function appendOrderHeader($config)
    {
        if (!user()->onShopScope())//shop scoped user already have shop header at page top
            $this->renderShopHeader($config['shop']);
        if (isset($config['email'])&&isset($config['orderNo']))
            $this->renderAccountRegistrationSnippet($config['email'],$config['orderNo']);
        $this->renderOrderMessage($config['message'],isset($config['actionButton'])?$config['actionButton']:null,isset($config['actionButtonVisible'])?$config['actionButtonVisible']:false);
    }
    
    protected function renderOrderMessage($message,$actionButtonWidget=null,$actionButtonVisible=false)
    {
        $flash = $this->widget('common.widgets.SDetailView', array(
            'data'=>['order'],
            'columns'=>[
                [
                    ['label'=>null,
                     'type'=>'raw',
                     'value'=>$message,
                    ],
                    ['label'=>'',
                     'type'=>'raw',
                     'value'=>$actionButtonWidget,
                     'visible'=>$actionButtonVisible,
                    ],
                ],
            ],
            'htmlOptions'=>['class'=>'order-message'],
        ),true);  

        echo CHtml::tag('div',['class'=>'order-flash'],$this->getFlashAsString('success',$flash,Sii::t('sii','Thanks for your order!')));
    }
    
    protected function renderShopHeader($shop)
    {
        $this->widget('common.widgets.SDetailView', array(
            'data'=>['shop'],
            'columns'=>[
                [
                    ['label'=> CHtml::link($shop->logo,$shop->url),
                     'type'=>'raw',
                     'value'=> CHtml::link($shop->parseName(user()->getLocale()),$shop->url),
                    ],
                ],
            ],
            'htmlOptions'=>['class'=>'order-shop-header'],
        ));  
    }
    
    protected function renderAccountRegistrationSnippet($email,$orderNo)
    {
        if (user()->onShopScope())
            $model = CustomerAccount::model()->findByPk(['email'=>$email,'shop_id'=>user()->getShop()]);
        else
            $model = Account::model()->findByAttributes(['email'=>$email]);
        
        if ($model!=null||empty($email)){
            logWarning(__METHOD__.' email already registered in system',$email);
            echo '';
        }
        else {
            $accountFlash = $this->widget('common.widgets.SDetailView', [
                        'data'=>['account'],
                        'columns'=>[
                            [
                                ['label'=>null,
                                 'type'=>'raw',
                                 'value'=>'<p>'.Sii::t('sii','Email address: <strong>{email}</strong>',['{email}'=>$email]).'</p>',
                                ],
                                ['label'=>'',
                                 'type'=>'raw',
                                 'value'=>$this->widget('zii.widgets.jui.CJuiButton',['id'=>'accountbutton',
                                                 'name'=>'accountButton',
                                                 'buttonType'=>'button',
                                                 'caption'=>Sii::t('sii','Register Now'),
                                                 'onclick'=>'js:function(){loadsignupform("'.$orderNo.'");}',
                                            ],true),
                                 'visible'=>true,//to check against if account exists
                                ],
                            ],
                        ],
                        'htmlOptions'=>['class'=>'order-message'],
                    ],true);

            echo CHtml::tag('div',['class'=>'account-creation'],$this->getFlashAsString('advice',$accountFlash,Sii::t('sii','You can track your order status easily by creating an account.')));
        }
    }
}

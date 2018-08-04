<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.oauth.widgets.OAuthNetworks');
/**
 * Description of OAuthUnlinkAction
 *
 * @author kwlok
 */
class OAuthUnlinkAction extends CAction
{
    public function run()
    {		
        if (Yii::app()->user->isGuest)
            throwError403 (Sii::t('sii','Unauthorized Access'));
        
        if (!Yii::app()->user->isActivated)
            throwError403 (Sii::t('sii','Unauthorized Access'));
        
        if (Yii::app()->request->isPostRequest && isset($_POST['network'])){
            $network = OAuth::model()->findAccount(Yii::app()->user->id, $_POST['network']);
            if($network){
                $network->logout();
                $network->delete();
                logInfo(__METHOD__.' oauth network '.$_POST['network'].' unlinked');
                user()->setFlash('unlink',array(
                    'message'=>Sii::t('sii','You have unlinked {network} from {app} successfully and you will not be able to login using {network} account. If you wish to use {network} account again to login {app}, you may relink it.',array('{network}'=>$network->provider,'{app}'=>Yii::app()->name)),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Network Unlink')));
                $status = 'success';
            }
            else {
                logError(__METHOD__.' oauth network '.$_POST['network'].' unlinked error');
                user()->setFlash('unlink',array(
                    'message'=>'',
                    'type'=>'error',
                    'title'=>Sii::t('sii','Network Unlink Error')));
                $status = 'failure';
            }
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'status'=>$status,
                'flash'=>$this->controller->sflashWidget('unlink',true),
                'actions'=>CHtml::link(OAuthNetworks::linkIcon(),'javascript:void(0)',array('onclick'=>OAuthNetworks::linkScript(),'class'=>'link')),
            ));
            Yii::app()->end();
        }
    }
}

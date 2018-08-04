<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of OAuthLinkAction
 *
 * @author kwlok
 */
class OAuthLinkAction extends CAction
{
    public static $flashId = 'AccountProfile';
    /**
     * The controller action/route to redirect when oauth network is authenticated successfully
     * Default to '/account/management/networks' and Account/ManagementConroller need to support this action
     * @see OAuthAction
     */
    public static $redirectRoute = '/account/management/networks';
    /**
     * Overriden method
     * Run action
     * @see OAuthAction::run()
     */
    public function run()
    {		
        if (Yii::app()->user->isGuest)
            throwError403(Sii::t('sii','Unauthorized Access'));
        
        if (!Yii::app()->user->isActivated)
            throwError403(Sii::t('sii','Unauthorized Access'));

        if (isset($_GET['network'])&&isset($_GET['uid'])){
            //When reaching this point, OAuth model record is expected to be inserted handled by OAuthNetworks::$oauthRoute
            $oauth = OAuth::model()->findAccount($_GET['uid'],$_GET['network']);
            if ($oauth==null){
                logError(__METHOD__.' oauth network '.$_GET['network'].' for user '.$_GET['uid'].' not found.');
                user()->setFlash(self::$flashId,array(
                    'message'=>Sii::t('sii','Failed to link your account to {network}.',array('{network}'=>$_GET['network'])),
                    'type'=>'error',
                    'title'=>Sii::t('sii','Network Link Error')));
            }
            else {
                logInfo(__METHOD__.' network '.$_GET['network'].' linked done. return from referrer url '.request()->getUrlReferrer());
                user()->setFlash(self::$flashId,array(
                    'message'=>Sii::t('sii','You have linked {network} to {app} successfully and you will be able to login {app} using {network} account in your future logins.',array('{network}'=>$_GET['network'],'{app}'=>Yii::app()->name)),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Network Link')));
            }
            $this->controller->redirect(url(self::$redirectRoute));
            Yii::app()->end();
        }
    }
}

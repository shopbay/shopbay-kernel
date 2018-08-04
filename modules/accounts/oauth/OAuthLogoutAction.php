<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * OAuthLogoutAction handles the redirect from social network when logout is successfully done at social network site
 * It basically act as proxy and handle internal redirect 
 *
 * @author kwlok
 */
class OAuthLogoutAction extends CAction
{    
    public function run()
    {		
        if (Yii::app()->user->isGuest)
            throwError403 (Sii::t('sii','Unauthorized Access'));
        
        if (isset($_GET['d']) && isset($_GET['u'])){
            
            $data  = self::parseData($_GET['d']);
            logInfo(__METHOD__.' data received '.$_GET['d'],$data);
            $oauth = OAuth::model()->findAccount($data['account_id'],$data['provider']);            
            if ($oauth!=null){
                if (!$oauth->logout())
                    throw new CException(Sii::t('sii','Fail to logout session.'));
                else {
                    //set flash using model AccountProfile as by default the rediret url is profile view page
                    user()->setFlash(get_class(AccountProfile::model()),array(
                        'message'=>Sii::t('sii','You have logged out {network} successfully but this does not affect your current session and use of {app}.',array('{network}'=>$oauth->provider,'{app}'=>Yii::app()->name)),
                        'type'=>'success',
                        'title'=>Sii::t('sii','Network Logout')));
                    logInfo(__METHOD__." session data cleared for user $oauth->account_id $oauth->provider");
                }
            }
            //rediret to social network for logout
            logInfo(__METHOD__.' redirect to logout url '.$_GET['u']);
            $this->controller->redirect(urldecode($_GET['u']));
            Yii::app()->end();
        }
        
    }
    
    public static function formatData($params)
    {
        return base64_encode(json_encode($params));
    }
    
    public static function parseData($data)
    {
        return json_decode(base64_decode($data),true);
    }
    
    public static function formatLogoutUrl($route,$oauth)
    {
        return url($route, array(
                'u'=>urlencode($oauth->logoutUrl),
                'd'=>self::formatData(array(
                    'account_id'=>$oauth->account_id,
                    'provider'=>$oauth->provider)
                ),
            ));   
    }
}

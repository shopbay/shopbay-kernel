<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.actions.api.models.ApiOauthClientTrait');
/**
 * Description of AuthenticateController
 * This controller extends directly from AuthenticatedController, and bypass AccountBaseController
 * 
 * @author kwlok
 */
class AuthenticateController extends AuthenticatedController 
{
    use ApiOauthClientTrait;
    /**
     * Custom return url; Pass in via the GET url as "returnUrl=xxxx' and captured as a hidden field at login form.
     * This field will be later submitted as $_POST params when user attempt to login
     * If login is successful, system will redirect to this custom return url
     * @var string Optional
     */
    protected $customReturnUrl;
    /**
     * The oauth client to request for authorization_code follows OAUTH2 protocol.
     * This is to support the chatbot messenger account linking
     * @see common.modules.chatbots.controllers.OauthController
     * @var string Optional
     */
    protected $oauthClient;
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
        $this->rightsFilterActionsExclude = [
            'login', 'loginform', 'oauth', 'logout',
        ];
    }     
    /**
     * IMPORTANT NOTE: This controller is not including "subscription" filter
     * @return array action filters
     */
    public function filters()
    {
        $filters = parent::filters();
        foreach ($filters as $key => $value) {
            if ($value=='subscription')
                unset($filters[$key]);
        }
        return $filters;
    }      
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            'oauth' => [
                'class'=>'accounts.oauth.OAuthAction',
                'afterLoginUrl'=> [
                    'method'=>'getAfterLoginUrl',
                    'param'=>$this->module->afterLoginRoute,
                ],
            ],
            'oauthLink' => [//handle return from network after successfully linked
                'class'=>'accounts.oauth.OAuthLinkAction',
            ],
            'oauthUnlink' => [
                'class'=>'accounts.oauth.OAuthUnlinkAction',
            ],
            'oauthLogout' => [
                'class'=>'accounts.oauth.OAuthLogoutAction',
            ],
            'loginform'=> [
                'class'=>'common.modules.accounts.actions.LoginFormAction',
            ],  
        ];
    }
    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        if (user()->isAuthenticated){
            $this->redirect(url('/welcome'));
            Yii::App()->end();
        }
        
        $this->layout = Yii::app()->ctrlManager->layout;
        $this->headerView = Yii::app()->ctrlManager->headerView;
        $this->footerView = Yii::app()->ctrlManager->footerView;
        $this->htmlBodyCssClass = Yii::app()->ctrlManager->htmlBodyCssClass;
        
        $this->setPageTitle(Sii::t('sii','Login'));
        $this->registerFormCssFile();
        $this->registerCssFile('application.assets.css','application.css');

        $model = new LoginForm;
        $model->title = Sii::t('sii','Log in');

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form'){
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        //prefix with "__expired" stands for session expired
        if(isset($_GET['__expired'])){
            user()->setFlash(get_class($model),[
                'message'=>Sii::t('sii','Your session has expired. Please login again.'),
                'type'=>'notice',
                'title'=>null,
            ]);               
        }
        
        //prefix with "__", iu stands for class IdentityUser|IdentityAdmin
        if(isset($_GET['__iu'])){//any error from other place (normally activation) routed to login page
            user()->setFlash(get_class($model),[
                'message'=>IdentityUser::errorMessage(base64_decode($_GET['__iu'])),
                'type'=>'error',
                'title'=>null,
            ]);
        }

        //if it is activation request
        if (preg_match('/\baccount\/activate\b/', app()->user->returnUrl)){
            $model->title = Sii::t('sii','Account Activation');
            user()->setFlash(get_class($model),[
                'message'=>Sii::t('sii','Verifying your email address and password'),
                'type'=>'notice',
                'title'=>null]);
            $model->setActivationToken(app()->user->returnUrl);
            logTrace(__METHOD__.' token ',$model->token);
            //set this parameter to get it captured as a form field to let it redirect back to route "account/activate"
            $_GET['returnUrl'] = app()->user->returnUrl;
        }

        //check if any return url
        if(!empty($_POST['returnUrl'])){//cannot use isset() as beware of empty string 
            $this->customReturnUrl = $_POST['returnUrl'];
            //oauth enabled; to request for authorization_code
            if(!empty($_POST['oauthClient'])){
                $this->oauthClient = base64_decode($_POST['oauthClient']);
            }
        }

        // collect user input data
        if(isset($_POST['LoginForm'])){
            
            logInfo(__METHOD__.' Logging in...');
            
            $model->setup($_POST['LoginForm']);
            
            try {
                
                if ($this->module->isApiAuthMode)
                    $this->apiAuth($model);
                else
                    $this->serviceAuth($model);
                
            } catch (CException $e) {
                logError(__METHOD__.' Failed to login username='.$model->username.', error trace >> '.$e->getTraceAsString(),array(),false);
                user()->setFlash(get_class($model),[
                    'message'=>$e->getMessage(),
                    'type'=>'error',
                    'title'=>null]);
                $model->unsetAttributes(['password']);
            }

        }
        
        $this->render('index',['model'=>$model]);
    }
    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        logInfo('Logging out...');
        if ($this->allowOAuth)
            $connectedNetworks = $this->oauthLogoutInternal();

        if ($this->module->isApiAuthMode)
            $this->apiLogout();
        else
            $this->module->serviceManager->logout();//local logout have to be called later as above required usage of user id

        $locale = Yii::app()->language;//cannot call user()->getLocale() anymore as user is already logout
        
        if (request()->isAjaxRequest){
            $response = array('redirect'=>$this->afterLogoutUrl,'showModal'=>false);
            if (isset($connectedNetworks) && count($connectedNetworks)>0){
                Yii::import('common.modules.accounts.oauth.widgets.OAuthWidget');
                $networkList = $this->widget('OAuthWidget',[
                        'disableLink'=>true,
                        'providers'=>$connectedNetworks,
                        'iconOnly'=>false,
                    ],true);
                $message = Sii::tp('sii','n<=1#You had logged in {app} earlier on using following network account and you may still connected with it. Logging out {app} does not auto logout network. {networkList}|n>1#You had logged in {app} earlier on using following network accounts and you may still connected with them. Logging out {app} does not auto logout networks. {networkList}',array(count($connectedNetworks),'{networkList}'=>$networkList,'{app}'=>app()->name),$locale);
                $message .= '<br>'.CHtml::tag('a', ['style'=>'cursor: pointer;color:black;text-decoration:underline;','onclick'=>'closelogoutmodal("logout_modal","'.$this->afterLogoutUrl.'");'], 'OK');
                user()->setFlash($this->action->id,[
                    'message'=>$message,
                    'type'=>'notice',
                    'title'=>Sii::tp('sii','Logged out {app}',array('{app}'=>app()->name),$locale),
                ]);
                $response['showModal'] = true;
                $response['modal'] = $this->smodalWidget('logout_modal',CHtml::tag('div',['class'=>'logout-dialog rounded'],$this->sflashWidget($this->action->id,true)),null,'closelogoutmodal("logout_modal","'.$this->afterLogoutUrl.'");',true);
            }
            //logTrace(__METHOD__.' json response',$response);
            header('Content-type: application/json');
            echo CJSON::encode($response);            
        }
        else {
            $this->redirect($this->afterLogoutUrl);
        }
        Yii::app()->end();
    }      
    /**
     * @return after login url
     */
    public function getAfterLoginUrl($route=null) 
    {
        //Precedence is given to OauthClient (chatbot use case), process return url first
        //$customReturnUrl and $oauthClient are passed from views/authenticate/login.php (login form)
        if (isset($this->customReturnUrl) && isset($this->oauthClient)){
            logInfo(__METHOD__.' oauth client presented. redirect return url '.$this->customReturnUrl,request()->getRequestUri());
            //redirect to return url with authorization code associated
            $this->apiAuthorize($this->oauthClient, $this->customReturnUrl);
            Yii::app()->end();
        }
        
        //Process other scenarios
        //this applies to shop normally and not for oauth login
        if ($this->module->redirectSubdomainAfterLoginRoute && !$this->isSameOrigin(Yii::app()->urlManager->hostDomain) && !$this->isOAuthLogin()){
            if (isset($_SERVER['HTTP_REFERER']))
                logInfo(__METHOD__.' authenticated from other subdomain '.$_SERVER['HTTP_REFERER']);
            if (isset($this->customReturnUrl)){//this is passed from views/authenticate/login.php (login form)
                logInfo(__METHOD__.' Shop use $POST return url '.$this->customReturnUrl,request()->getRequestUri());
                return $this->customReturnUrl;
            } else {
                logTrace(__METHOD__.'Shop action='.$this->action->id.', getUrlReferrer '.Yii::app()->request->getUrlReferrer());
                return Yii::app()->request->getUrlReferrer();
            }
        }

        if ($this->module->redirectShopAfterLoginRoute && strpos(Yii::app()->request->getUrlReferrer(), '/shop/')!=false){
            logInfo(__METHOD__.' authenticated from shop page '.Yii::app()->request->getUrlReferrer(),request()->getRequestUri());
            if (isset($this->customReturnUrl)){//this is passed from views/authenticate/login.php (login form)
                logInfo(__METHOD__.' use $POST return url '.$this->customReturnUrl,request()->getRequestUri());
                return $this->customReturnUrl;
            } else {
                return Yii::app()->request->getUrlReferrer();
            }
        }
        
        if (isset($route)){
            $url = request()->getSecureHostInfo().$route;//always in secure connection
            logTrace(__METHOD__.' action='.$this->action->id.', user defined route '.$url);
            return $url;
        }
        elseif (isset(Yii::app()->user->returnUrl) && Yii::app()->user->returnUrl!='/' && $this->module->useReturnUrl){
            logTrace(__METHOD__.' action='.$this->action->id.', user->returnUrl '.Yii::app()->user->returnUrl);
            return Yii::app()->user->returnUrl;
        }
        elseif (isset($this->customReturnUrl)){//this is passed from views/authenticate/login.php (login form)
            logInfo(__METHOD__.' use $POST return url '.$this->customReturnUrl,request()->getRequestUri());
            return $this->customReturnUrl;
        }
        elseif (!empty($this->module->afterLoginRoute)){
            logTrace(__METHOD__.' action='.$this->action->id.', afterLoginRoute '.$this->module->afterLoginRoute);
            return $this->module->afterLoginRoute;
        }
        else {
            logTrace(__METHOD__.' action='.$this->action->id.', getUrlReferrer '.Yii::app()->request->getUrlReferrer());
            return Yii::app()->request->getUrlReferrer();
        }
    }
    /**
     * @return after logout url
     */
    public function getAfterLogoutUrl($route=null) 
    {
        if (isset($route)){
            $url = url($route);
            logTrace(__METHOD__.' action='.$this->action->id.', user defined route '.$url);
            return $url;
        }
        else if (!empty($this->module->afterLogoutRoute)){
            logTrace(__METHOD__.' action='.$this->action->id.', afterLogoutRoute '.$this->module->afterLogoutRoute);
            return url($this->module->afterLogoutRoute);
        }
        else{
            if ($this->module->useReturnUrl){
                logTrace(__METHOD__.' action='.$this->action->id.', returnUrl '.Yii::app()->user->returnUrl);
                return Yii::app()->user->returnUrl;
            }
            else {
                logTrace(__METHOD__.' action='.$this->action->id.',  getUrlReferrer() '.Yii::app()->request->getUrlReferrer());
                return Yii::app()->request->getUrlReferrer();
            }
        }
    }  
    /**
     * Logout oauth networks (internal handling)
     * Note: this logout solely clear session data but not actual logging out network site
     * 
     * To actual logout network, need to have explicit url redirect call to let network handle its own logout
     * But it may seems better to keep oauth network connection open and let user logout themselve at sn site
     * Espeically when there are multiple oauth connections, different oauth logout may handled differently - case by case basis
     * 
     * @see OAuth::logoutUrl
     */
    protected function oauthLogoutInternal()
    {
        $oauthConnected = [];
        $networks = OAuth::model()->findAccount(user()->id);
        foreach ($networks as $network) {
            if ($network->isConnected){
                $oauthConnected[] = $network->provider;
            }
            $network->logout();
            logInfo(__METHOD__.' Logging out '.$network->provider);
        }    
        logInfo(__METHOD__.' connected network',$oauthConnected);
        return $oauthConnected;
    }
    /**
     * @return the redirect url after netowrk is linked
     */
    public function getAfterLinkedUrl($route) 
    {
        $url = url($route);
        logTrace(__METHOD__.' action='.$this->action->id.', redirect to route '.$url);
        return $url;
    }
    
    protected function apiAuth($model)
    {
        Yii::import('common.components.actions.api.ApiLoginAction');
        $params = userOnScope('shop')?['shop'=>user()->getShop()]:[];
        $action = new ApiLoginAction($this,__METHOD__,$model);
        if ($model->isActivateMode){
            $params = array_merge($params,['token'=>$model->token]);
            $action->apiRoute = $this->module->apiActivateRoute.'?'.http_build_query($params);;
        }
        else {
            $action->apiRoute = $this->module->apiLoginRoute.'?'.http_build_query($params);
        }
                    
        $this->runAction($action);
        if (!Yii::app()->user->isGuest && user()->getApiAccessToken()!=null){
            logTrace(__METHOD__.' login',user()->getApiAccessToken());
            $this->afterAuth($model);
        }
        else
            throw new CException(Sii::t('sii','Undefined login state. Please try again.'));
    }
    
    protected function serviceAuth($model)
    {
        if($this->module->serviceManager->login($model))
            $this->afterAuth($model);
        else
           throw new CException(Sii::t('sii','Undefined login state. Please try again.'));
    }
    
    protected function afterAuth($model)
    {
        //Activation mode: Redirect to /account/activate
        if ($model->isActivateMode) {
            logTrace(__METHOD__.' Activation mode: Redirect to /account/activate..',app()->user->returnUrl);
            $this->redirect(app()->user->returnUrl);
        }
        //logTraceDump(__METHOD__.' user session data',$_SESSION);
        //Normal login mode
        unset($_POST);
        $this->redirect($this->afterLoginUrl);
    }
    
    protected function apiLogout()
    {
        Yii::import('common.components.actions.api.ApiLogoutAction');
        $action = new ApiLogoutAction($this,__METHOD__);
        $action->apiRoute = $this->module->apiLogoutRoute;
        $this->runAction($action);
        if (!Yii::app()->user->isGuest && user()->getApiAccessToken()!=null){
            throw new CException(Sii::t('sii','Logout error.'));
        }                
    }    
    /**
     * Request for authorization_code for an oauth client
     * @throws CException
     */
    protected function apiAuthorize($clientId,$redirectUri)
    {
        $client = $this->findOAuthClient($clientId);
        Yii::import('common.components.actions.api.ApiAuthorizeAction');
        $action = new ApiAuthorizeAction($this,__METHOD__);
        $action->client_id = $client->client_id;
        $action->client_secret = $client->client_secret;
        $action->redirect_uri = $client->redirect_uri;
        $action->user_id = Yii::app()->user->id;
        $action->redirectUriOnSuccess = $redirectUri;
        $this->runAction($action);
    }    
    
    protected function isOAuthLogin()
    {
        return strpos(Yii::app()->request->getRequestUri(), '/oauth/')!=false;
    }
}

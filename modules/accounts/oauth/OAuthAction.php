<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of OAuthAction
 *
 * @author kwlok
 */
class OAuthAction extends CAction
{
    /**
     * @var string $model The user model
     */
    public $model = 'AccountOAuth';
    /**
     * Map model attributes to attributes of user's social profile
     * @var array $attributes attributes synchronization array (user model attribute => profile attribute). 
     * List of available profile attributes you can see at {@link http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Profile.html "HybridAuth's Documentation"}.
     *
     * Additional attributes:
     *    birthDate - The full date of birthday, eg. 1991-09-03
     *    genderShort - short representation of gender, eg. 'M', 'F'
     *
     * You can also set attributes, that you need to save in model too, eg.:
     *    'attributes' => array(
     *      'is_active' => 1,
     *      'date_joined' => new CDbExpression('NOW()'),
     *    ),
     *
     * @see OAuthAction::$avaibleAtts
     */
    public $attributes =  array(
                            'username' => 'email',
                            'email' => 'email',
                            'firstname' => 'firstName',
                            'lastname' => 'lastName',
                            'gender' => 'genderShort',
                            'birthday' => 'birthDate',
                            //you can also specify additional attribute values, e.g. status, address etc
                            //'xxx' => 'yyy',
                        );
    /**
     * @var integer $duration how long the script will remember the user
     */
    public $duration = 2592000; // 30 days
    /**
     * @var boolean $alwaysCheckUserData flag to control additional user data check when social network returned email of existing local account. 
     * If set to `false` user will be automatically logged in without further user data check
     */
    public $alwaysCheckUserData = true;
    /**
     * @var mixed $afterLoginUrl If null, it will use CWebUser::returnUrl; Else, refer to controller defined after login url
     * array(
     *  'method'=>'getAfterLoginUrl',
     *  'param'=>$this->module->afterLoginRoute,
     * )
     */
    public $afterLoginUrl;
    /**
     * @var array $_availableAttrs Hybridauth attributes that support by this script (this a list of all available attributes in HybridAuth 2.0.11) + additional attributes (see $attributes property)
     */
    protected $_availableAttrs = array('identifier', 'profileURL', 'webSiteURL', 'photoURL', 
                                    'displayName', 'description', 'firstName', 'lastName', 
                                    'gender', 'language', 'age', 'birthDay', 'birthMonth', 
                                    'birthYear', 'email', 'emailVerified', 'phone', 
                                    'address', 'country', 'region', 'city', 
                                    'zip', 'birthDate', 'genderShort');
    /**
     * @var OAuth the model to handle the work during the request
     */
    protected $_oauth;

    public function run()
    {		
        $this->validateAction();

        if(isset($_GET['provider'])) {
            if (isset($_GET['returnUrl'])){
                logInfo(__METHOD__.' returnUrl is supplied '.$_GET['returnUrl']);
                $this->afterLoginUrl = urldecode($_GET['returnUrl']);
            }
            // after oauth — working with user model and his data from SN
            Yii::import('accounts.oauth.*');
            Yii::import('accounts.oauth.widgets.*');
            $this->oAuth($_GET['provider']);
        }
        else {
            //TODO: Need to add access block here to prevent direct access to this area (without $_GET['provider']) access will route to here
            //return url format: https://<domain>/account/authenticate/oauth?hauth.start=<Network>&hauth.time=1433460244            
            //Handling OAuth (redirects, tokens etc.)
            $path = Yii::getPathOfAlias('common.vendors.hybridauth');
            require($path.'/index.php');
            Yii::app()->end();
        }
    }
    /**
     * Initiates authorization with specified $provider and 
     * then authenticates the user, when all goes fine
     * 
     * @param mixed $provider provider name for HybridAuth
     * @access protected
     * @return void
     */
    protected function oAuth($provider)
    {
        try{
            // trying to authenticate user via social network
            $oAuth = OAuth::model()->authenticate($provider);
            $this->setOauth($oAuth);

            $accessCode = $this->getAccessCode();

            if($accessCode === self::ACCESS_OK) {
                //the authentication was successfull. closing auth window
                $widget = new OAuthNetworks();
                ?>
                <script type="text/javascript" src="<?php echo $widget->oAuthNetworkScriptsUrl;?>"></script>                    
                <script>
                    opennetwork('<?php echo $this->parseReturnUrl(); ?>');
                </script>
                <?php
                Yii::app()->end();
            }
        } catch(Exception $e) {
            $this->handleError($e);
        }
        ?>
        <script>
            window.close();
        </script>
        <?php
    }
    const ACCESS_BLOCK = 0;//user is not allowed to access
    const ACCESS_OK    = 1;//user can login and access
    const ACCESS_HOLD  = 2;//user can login, but required email verification and account activation - follow local account sign up procedure
    /**
     * @param  OAuth $oAuth
     * @return integer access code
     */
    protected function getAccessCode()
    {
        $oAuth = $this->oAuth;
        // If we already have a user logged in, associate the authenticated provider with the logged-in user
        if(!Yii::app()->user->isGuest) {
            $accessCode = self::ACCESS_OK;
            $oAuth->bindTo(Yii::app()->user->id);
        } 
        else {
            
            $user = $this->prepareGuestUser();

            // checking if current user is not banned or anything else
            $accessCode = self::ACCESS_OK;
            $accessCode = $this->checkUserAccess($user);

            // sign user in
            switch($accessCode) {
                case self::ACCESS_OK:
                    if(!$oAuth->bindTo($user->id)) {
                        logError(__METHOD__.' error binding user to provider',$oAuth->errors);
                        throw new CException(Sii::t('sii','Account oauth binding error'));
                    }
                    //Call autheticate service
                    try {
                        $identity = new IdentityOAuth($user->email, null);//no password since already authenticated by social network
                        $this->controller->module->serviceManager->authenticate($identity,$this->duration);
                        //At this point, user was successfully logged in here
                    } catch (CException $ex) {
                        throw new CException($ex->getMessage());
                    }
                    break;
                case self::ACCESS_HOLD://stopping script to let checkUserAccess() function render new content
                    Yii::app()->end();
                    break;
                default:
                    throw new CException(Sii::t('sii','Unauthorized Access'));
            }
        }
        return $accessCode;
    }
    /**
     * Prepares the model of the guest user; Registers new user, if needed
     * 
     * @return type user model
     */
    protected function prepareGuestUser()
    {
        $userProfile = $this->oAuth->profile;

        if($this->oAuth->isBond) {
            logInfo(__METHOD__.' Social network account is bond to existing local account');
            return $this->userModel->findByPk($this->oAuth->account_id);
        } 
        elseif (!empty($userProfile->emailVerified)) {
            logInfo(__METHOD__.' Social network returned user verified email, check if system already have a user with this email...');
            $user = $this->userModel->findByEmail($userProfile->emailVerified);
        }

        if(!isset($user)) {
            logInfo(__METHOD__.' Registering a new user..');
            $user = new $this->model();
            $user->isNewUser = true;
        }

        if ($this->alwaysCheckUserData || $user->isNewUser) {
            logInfo(__METHOD__.' Processing user..');
            $user = $this->processUser($user, $userProfile);
        }

        return $user;
    }
    /**
     * Process user
     * [1] Populate user profile data from returning social network profile
     * [2] If it is a new user, create local account and profile
     * [3] Check user data for both newly created local account or existing local account (user login before using any social network)
     *
     * @param CActiveRecord $user current user model
     * @param object $userProfile social network's user profile object
     * @access protected
     */
    protected function processUser($user, $userProfile)
    {
        if ($user->isNewRecord) { 
            $this->populateModel($user, $userProfile);
        }
        
        //This interrupt the user processing, a form could get displayed to ask for more data if required
        $user = $this->checkUserData($user);

        if ($user->isNewRecord) {
            //Call presignup service; For new user signin using social network account, have to go through auto presignup process
            $newAccount = $this->controller->module->serviceManager->presignup($user->prepareSignupForm());
            //Signup Service is returning Account model, so have to transfer back attributes back to AccountOAuth
            $user->cloneAccount($newAccount);
        }

        return $user;
    }    
    /**
     * Populates User model with data from social network profile
     * 
     * @param CActiveRecord $user users model
     * @param mixed $profile HybridAuth user profile object
     * @access protected
     */
    protected function populateModel($user, $profile)
    {
        foreach($this->attributes as $attribute => $pAtt) {
            if(in_array($pAtt, $this->_availableAttrs)) {
                switch($pAtt) {
                    case 'genderShort':
                        $gender = array('female'=>'F','male'=>'M');
                        $att = $gender[$profile->gender];
                        break;
                    case 'birthDate':
                        $att = $profile->birthYear 
                        ? sprintf("%04d-%02d-%02d", $profile->birthYear, $profile->birthMonth, $profile->birthDay)
                        : null;
                        break;
                    case 'email':
                        $att = $profile->emailVerified;
                        break;
                    default:
                        $att = $profile->$pAtt;
                }
                if(!empty($att)) {
                    $user->$attribute = $att;
                }
            } 
            else {
                    $user->$attribute = $pAtt;
            }
        }
    }
    /**
     * This process check if we have all data, that we need from new user
     * or, perform more stringent user data validation, 
     * or ask more user data if required but not available / return from social network
     * 
     * This may use a CFormModel to perform validation, displays the form to get the required, but not specified user data
     * 
     * Use case:
     * [1] when social network returned email of existed user (and not yet verified?), he is logging the first time with this SN. 
     * We will ask him for password to validate, that it is realy his account
     *
     * @param  CActiveRecord $user user model
     * @return CActiveRecord user model with correct data
     */
    protected function checkUserData($user)
    {
        logInfo(__METHOD__.' Checking user data..');
        //add logic here; currently do nothing
        return $user;
    }    
    /**
     * Checks whether the $user can be logged in
     *
     * @param CActiveRecord $user current `Account' model
     * @param boolean $render flag that enables rendering
     */
    protected function checkUserAccess($user, $render = true)
    {
        if ($user->isAdmin || $user->isSuperuser || $user->isSystem){
            //admin portal currently does not allow oauth login
            $accessCode = self::ACCESS_HOLD;
            $message = Identity::errorMessage(Identity::ERROR_ROLE_INVALID);
        }
        elseif (!$user->hasRole(Yii::app()->user->currentRole)){
            //admin portal currently does not allow oauth login
            $accessCode = self::ACCESS_HOLD;
            $message = Identity::errorMessage(Identity::ERROR_ROLE_INVALID);
        }
        else if ($user->pendingSignup()) {
            //grant access even local account is not yet activated
            //system will prompt, and send notificationm, to guide user to activate asap, or after first login
            $accessCode = self::ACCESS_OK;
            $message = Identity::errorMessage(Identity::ERROR_NONE);
        }
        else if (!$user->isActive()) {
            $accessCode = self::ACCESS_BLOCK;
            $message = Identity::errorMessage(Identity::ERROR_USER_INACTIVE);
        } else {
            $accessCode = self::ACCESS_OK;
            $message = Identity::errorMessage(Identity::ERROR_NONE);
        }

        if($accessCode!=self::ACCESS_OK && isset($message) && $render) {
            $this->controller->render('accounts.oauth.widgets.views.error', array(
                'message' => $message,
            ));
        }

        return $accessCode;
    }
    /**
     * Checks if the action was properly setup and ready to run
     * @throws Exception If improperly setted up
     */
    protected function validateAction()
    {
        if(!is_array($this->attributes)) {
            $this->attributes = array();
        }

        if (!in_array('email', $this->attributes)) {
            throw new CException(Sii::t('sii','You must bind "email" field in {class}::attributes property.',array('{class}'=>__CLASS__)));
        }

        if(empty($this->model) || !class_exists($this->model)) {
            throw new CException(Sii::t('sii','User model is not defined'));
        }
        
        if (isset($this->afterLoginUrl)){
            if (!is_array($this->afterLoginUrl)) 
                throw new CException(Sii::t('sii','{class}::afterLoginUrl must be in array format.',array('{class}'=>__CLASS__)));
            if (!array_key_exists('method', $this->afterLoginUrl))
                throw new CException(Sii::t('sii','{class}::afterLoginUrl is missing key "method".',array('{class}'=>__CLASS__)));
            if (!array_key_exists('param', $this->afterLoginUrl))
                throw new CException(Sii::t('sii','{class}::afterLoginUrl is missing key "param".',array('{class}'=>__CLASS__)));
        }
        
    }
    /**
     * @return CActiveRecord user model 
     */
    public function getUserModel()
    {
        return call_user_func(array($this->model, 'model'));
    }
    /**
     * Sets the OAuth model to work with
     * @param OAuth $value model
     */
    public function setOauth(OAuth $value)
    {
        if(!$this->_oauth) {
            $this->_oauth = $value;
        }
    }
    /**
     * Sets the OAuth model to work with
     * @param OAuth $value model
     */
    public function getOauth()
    {
        return $this->_oauth;
    }
    /**
     * Handles, log or displays errors
     * @see OAuth::authenticate
     * @param  Exception $e
     */
    protected function handleError(Exception $e)
    {
        $error = "";
        // Display the received error
        switch($e->getCode()) { 
            case 0 : $error = "Unspecified error."; throw $e; break;
            case 1 : $error = "Hybriauth configuration error."; break;
            case 2 : $error = "Provider not properly configured."; break;
            case 3 : $error = "Unknown or disabled provider."; break;
            case 4 : $error = "Missing provider application credentials."; break;
            case 5 : $error = "Authentication failed. The user has canceled the authentication or the provider refused the connection."; break;
            case 6 : $error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again."; 
                @$this->oAuth->logout(); 
            break;
            case 7 : $error = "User not connected to the provider."; 
                 @$this->logout(); 
            break;
            case 8 : $error = "Provider does not support this feature."; break;
        }

        $error .= "\n\n<br /><br /><b>Original error message:</b> " . $e->getMessage(); 
        logError(__METHOD__.' '.strip_tags($error),$e->getTraceAsString());
        if(YII_DEBUG)
            throw $e;
    }
    
    protected function parseReturnUrl()
    {
        if (isset($this->afterLoginUrl) && is_array($this->afterLoginUrl))
            return $this->controller->{$this->afterLoginUrl['method']}($this->afterLoginUrl['param']);
        else if (isset($this->afterLoginUrl) && is_scalar($this->afterLoginUrl))
            return $this->afterLoginUrl;
        else 
            return Yii::app()->user->getReturnUrl(false);
    }
}

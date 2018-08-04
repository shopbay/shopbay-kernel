<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_oauth".
 *
 * The followings are the available columns in table 's_oauth':
 * @property integer $account_id
 * @property string $provider
 * @property string $identifier
 * @property string $profile_cache
 * @property string $session_data
 *
 * @author kwlok
 */
class OAuth extends CActiveRecord
{
    /**
     * @var $_hybridauth HybridAuth class instance
     */
    protected $_hybridauth;
    /**
     * @var $_adapter HybridAuth adapter	
     */
    protected $_adapter;
    /**
     * @var $_profileCache property for holding of unserialized profile cache copy
     */
    protected $_profileCache;
    /**
     * Returns the static model of the specified AR class.
     * @return Account the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_oauth';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array();
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
        );
    }
    
    public function afterFind()
    {
        parent::afterFind();

        if(!empty($this->profile_cache))
            $this->_profileCache = (object)unserialize($this->profile_cache);
    }

    public function beforeSave() 
    {
        if(!empty($this->_profileCache))
            $this->profile_cache = serialize((array)$this->_profileCache);

        return parent::beforeSave();
    }
    /**
     * @static
     * @access public
     * @return configuration array of HybridAuth lib
     */
    public static function getConfig()
    {
        $config = self::getConfigPath();

        if(!file_exists($config))
            throw new CException("The HybriAuth config.php file doesn't exists");

        return require($config);
    }
    /**
     * @return path to the HybridAuth config file
     */
    public static function getConfigPath()
    {
        $yiipath = Yii::getPathOfAlias('application.config.oauth');
        return $yiipath . '.php';
    }
    /**
     * @access public
     * @return array of OAuth models
     */
    public function findAccount($account_id, $provider=false)
    {
        $params = array('account_id' => $account_id);
        if($provider){
            $params['provider'] = $provider;
            return $this->findByAttributes($params);
        }
        else
            return $this->findAllByAttributes($params);
    }
    /**
     * @access public
     * @return Auth class. With restored users authentication session data
     * @link http://hybridauth.sourceforge.net/userguide.html
     * @link http://hybridauth.sourceforge.net/userguide/HybridAuth_Sessions.html
     */
    public function getHybridAuth()
    {
        if(!isset($this->_hybridauth)){
            $path = Yii::getPathOfAlias('common.vendors.hybridauth');
            require_once($path.'/Hybrid/Auth.php');
            $this->_hybridauth = new Hybrid_Auth( self::getConfig() );

            if(!empty($this->session_data))
                $this->_hybridauth->restoreSessionData($this->session_data);
        }
        return $this->_hybridauth;
    }
    /**
     * @access public
     * @return Adapter for current provider or null, when we have no session data.
     * @link http://hybridauth.sourceforge.net/userguide.html
     */
    public function getAdapter()
    {
        if(!isset($this->_adapter) && isset($this->session_data) && isset($this->provider))
            $this->_adapter = $this->hybridAuth->getAdapter($this->provider);

        return $this->_adapter;
    }
    /**
     * authenticates user by specified adapter	
     * 
     * @param string $provider 
     * @access public
     * @return void
     */
    public function authenticate($provider)
    {
        if(empty($this->provider)){
            try {
                $this->_adapter = $this->hybridauth->authenticate($provider);
                $this->identifier = $this->profile->identifier;
                $this->provider = $provider;
                $oAuth = self::model()->findByPk(array('provider' => $this->provider, 'identifier' => $this->identifier));
                if($oAuth)
                    $this->setAttributes($oAuth->attributes, false);
                else
                    $this->isNewRecord = true;

                $this->session_data = $this->hybridauth->getSessionData();
                return $this;
            } catch( CException $e ) {
                $error = "";
                switch( $e->getCode() )
                {
                    case 6 : //$error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again."; 
                    case 7 : //$error = "User not connected to the provider."; 
                    $this->logout();
                    return $this->authenticate($provider);
                    break;
                }
                throw $e;
            }
        }
        return null;
    }
    /**
     * Breaks HybridAuth session and logs user from network out.
     * Clear session data
     *
     * @access public
     */
    public function logout()
    {
        if(!empty($this->_adapter)){
            $this->_adapter->logout();
            $this->unsetAttributes(); 
        }
        return $this->clearSessionData();
    }
    /**
     * @access public
     * @return Hybrid_User_Profile user social profile object
     */
    public function getProfile()
    {
        $profile = $this->adapter->getUserProfile();
        //caching profile
        $this->_profileCache = $profile;

        return $profile;
    }
    /**
     * Clear session data when user logout social network site
     * @return whether the model successfully saved
     */
    public function clearSessionData()
    {
        $this->session_data = null;
        return $this->save();
    }    
    /**
     * binds local user to current provider 
     * 
     * @param mixed $account_id id of the user
     * @access public
     * @return whether the model successfully saved
     */
    public function bindTo($account_id)
    {
        $this->account_id = $account_id;
        return $this->save();
    }
    /**
     * @access public
     * @return whether this social network account bond to existing local account
     */
    public function getIsBond()
    {
        return !empty($this->account_id);
    }
    /**
     * Getter for cached profile.
     */
    public function getProfileCache()
    {
        if(empty($this->_profileCache)){
            $this->getProfile();
            $this->save();
        }
        return $this->_profileCache;
    }    
    /**
     * Check if user is connected to the provider
     * @return boolean
     */
    public function getIsConnected()
    {
        return !empty($this->session_data) && (bool)$this->hybridauth->storage()->get( "hauth_session.{$this->provider}.is_logged_in" );
    }
    /**
     * Get access token
     * For Facebook provider, token name is "access_token"
     * @see each Hybrid_Providers_"xxx" for its loginFinish() method
     */
    public function getAccessToken($token='access_token')
    {
        return $this->hybridauth->storage()->get( "hauth_session.{$this->provider}.token.$token" );
    }    
    /**
     * Get actual network logout URL 
     * @param string $afterLogoutUrl
     * Facebook api is supported with logout url
     * The parameters:
     * - next: the url to go to after a successful logout
     * @see each Hybrid_Providers_"xxx" property $api
     */
    public function getLogoutUrl($afterLogoutUrl=null)
    {
        $params = array();
        if (isset($afterLogoutUrl))
            $params = array('next'=>$afterLogoutUrl);
        return $this->hybridauth->getAdapter($this->provider)->api()->getLogoutUrl($params);
    }    
}

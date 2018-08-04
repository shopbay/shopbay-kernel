<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.accounts.models.BaseAccount");
/**
 * This is the model class for table "s_account".
 *
 * The followings are the available columns in table 's_account':
 * @property integer $id
 * @property string $name
 * @property string $password
 * @property integer $status
 * @property string $reg_ip
 * @property string $activate_str
 * @property integer $activate_time
 * @property string $last_login_ip
 * @property integer $last_login_time
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class Account extends BaseAccount
{
    const SYSTEM    = 0;//FOR SYSTEM INTERNAL USED
    const SUPERUSER = 1;//FOR SYSTEM INTERNAL USED
    const GUEST     = -1;//FOR GUEST
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
        return 's_account';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['name, password, email, status', 'required'],
            ['name, email', 'unique'],
            ['email', 'email'],
            ['status', 'safe'],
            ['name', 'length', 'max'=>32],
            ['status', 'length', 'max'=>20],
            ['password', 'length', 'max'=>64],//hashed password - standard 64 chars
            ['email', 'length', 'max'=>100],
            ['reg_ip', 'length', 'max'=>15],
            ['activate_str', 'length', 'max'=>50],
            
            ['id, name, password, email, status, reg_ip, activate_str, activate_time, last_login_ip, last_login_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'profile' => [self::HAS_ONE, 'AccountProfile', 'account_id'],
            'address' => [self::HAS_ONE, 'AccountAddress', 'account_id'],
        ];
    }
    /**
     * Returns Account model by its access token
     * 
     * @param string $token 
     * @return Account
     */
    public function findByAccessToken($token)
    {
        return self::model()->findByAttributes(['access_token' => $token]);
    }     
    /**
     * Returns Account model by its email
     * 
     * @param string $email 
     * @return Account
     */
    public function findByEmail($email)
    {
        return self::model()->findByAttributes(['email' => $email]);
    }
    /**
     * A wrapper method to return all records of this model
     * @return \SActiveRecord
     */
    public function all()
    {
        $criteria=new CDbCriteria;
        $criteria->condition = 'id NOT IN ('.self::SYSTEM.','.self::SUPERUSER.','.self::GUEST.')';
        parent::all()->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * A finder method to return all users (non admin users)
     * Return merchant user only
     * Shop customer accounts should be managed by shop owner (not shopbay admin)
     */
    public function users()
    {
        $criteria=new CDbCriteria;
        $criteria->join = 'INNER JOIN '.AuthAssignment::model()->tableName().' a ON a.userid=t.id AND a.itemname IN (\''.Role::MERCHANT.'\')';
        $criteria->condition = 't.id NOT IN ('.self::SYSTEM.','.self::SUPERUSER.')';
        $criteria->group = 't.id';
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * A finder method to return all admin users
     */
    public function admin($includeSuperuser=false)
    {
        $criteria=new CDbCriteria;
        $criteria->join = 'INNER JOIN '.AuthAssignment::model()->tableName().' a ON a.userid=t.id AND a.itemname = \''.Role::ADMINISTRATOR.'\'';
        if (!$includeSuperuser)
            $criteria->condition = 't.id NOT IN ('.self::SYSTEM.','.self::SUPERUSER.')';
        $criteria->group = 't.id';
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }

    /**
     * Set login id
     * Either username or email can be used as login id; Default to "email address"
     * But username for now kept as internal and follows email address
     * However, username now length is 32, so have to trim is email has bigger length
     */
    public function setLoginId($id)
    {
        $this->email = $id;
        $this->name = $this->email;
        if (strlen($this->name)>32)//reach maximum name length 32
            $this->name = Helper::rightTrim($this->name,28);
    }
    /**
     * Change email address
     * @param array $params Contains "email", and "userid"
     */
    public function changeEmail($params)
    {
        if (isset($params['email']) && isset($params['userid'])){
            $this->setLoginId($params['email']);
            $this->prepareEmailActivation($params['userid']);
        }
        else
            throw new CException(Sii::t('sii','Missing params to change email'));
    }
    /**
     * Create account
     * @param $roles The roles to be assigned for this account
     * @throws CDbException
     */
    public function create($roles=[])
    {
        $this->insert();
        foreach ($roles as $role) {
            Rights::assign($role, $this->id);
        }
        $profile = new AccountProfile();
        $profile->account_id = $this->id;
        $profile->locale = param('LOCALE_DEFAULT');
        $profile->insert();
    }
    /**
     * Sign up account
     * 
     * Rules:
     * [1] Default create an profile with locale = en_sg, if profile is passing in, it will populate profile data
     * [2] Default assign Role User 
     * 
     * General rule of thumb for permission assignments:
     * All account is a buyer (customer), but not necessary is a seller (merchant)
     * If an account is signed up as seller (merchant), he/she will always be a buyer too
     * 
     * @param AccountProfile $profileData
     */
    public function signup($profileData=[])
    {
        $this->insert();   
        Rights::assign(Role::USER, $this->id);
        $profile = new AccountProfile();
        $profile->account_id = $this->id;
        if (!empty($profileData)){
            foreach ($profileData as $key => $value) {
                $profile->$key = $value;
            }
            logInfo(__METHOD__.' account profile created');
            logTrace(__METHOD__.' account profile created with details',$profile->attributes);
        }
        else {
            $profile->locale = param('LOCALE_DEFAULT');
        }
        
        $profile->insert();
    } 
    /**
     * Return account nick name
     * @return type
     */
    public function getNickname()
    {
        if ($this->profile===null)
            return Sii::t('sii','{app} user',['{app}'=>Yii::app()->name]); 
        else
            return $this->profile->getAlias();
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        if ($this->isAdmin)
            return url('users/admin/view/id/'.$this->id);
        else
            return url('users/management/view/id/'.$this->id);
    }    
    /**
     * @inheritdoc
     */
    public function getUid()
    {
        return $this->id;
    }     
}
<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Administerable");
Yii::import("common.modules.accounts.models.AccountTrait");
/**
 *
 * @author kwlok
 */
abstract class BaseAccount extends Administerable
{
    use AccountTrait;
    /*
     * Account sub types such as CustomerAccount, MerchantAccount is designed to be compatible Account inside "rights" framework, e.g. table s_auth_assignment, 
     * As both SubAccount and Account user can have same id, so must use prefix to differentiate them 
     * All sub account users must be prefix to distinguish from Account users (will have no prefix or generic "s").
     */
    //const TYPE_GENERIC  = 's';//todo, not implemented; default system account signup (e.g. can be merchant or platform user)
    const TYPE_CUSTOMER = 'c';//sub account for shop customers
    const TYPE_MERCHANT = 'm';//todo sub account for merchant staff
    
    public $suspendedStatus   = Process::SUSPEND;
    public $suspendableStatus = Process::ACTIVE;
    /**
     * Get user id (or unique user id across platform)
     * By default this should be equals to account id
     * @return type
     */
    abstract public function getUid();
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Account|Accounts',[$mode]);
    }    
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'id',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'self',
                'localeAttribute'=>'profileLocale',
            ],            
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'name',
                'iconUrlSource'=>'profile',
            ],            
        ];
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'name' => Sii::t('sii','Name'),
            'password' => Sii::t('sii','Password'),
            'email' => Sii::t('sii','Email'),
            'status' => Sii::t('sii','Status'),
            'reg_ip' => 'Reg Ip',
            'activate_str' => 'Activate Str',
            'activate_time' => 'Activate Time',
            'last_login_ip' => Sii::t('sii','Last Login IP'),
            'last_login_time' => Sii::t('sii','Last Login Time'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }    
    /**
     * Return the account locale
     */
    public function getProfileLocale()
    {
        return is_null($this->profile)?param('LOCALE_DEFAULT'):$this->profile->locale;
    }
    /**
     * Get account avatar
     * @param type $version
     * @param type $htmlOptions
     * @return type
     */
    public function getAvatar($version,$htmlOptions=['style'=>'vertical-align:top'])
    {
        if ($this->profile===null){
            Yii::app()->image->modelClass = 'Image';
            return Yii::app()->image->loadModel(Image::DEFAULT_IMAGE_ACCOUNT)->render($version,'Image',$htmlOptions); 
        }
        else
            return $this->profile->getImageThumbnail($version,$htmlOptions);
    }    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('password',$this->password,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('reg_ip',$this->reg_ip,true);
        $criteria->compare('activate_str',$this->activate_str,true);
        $criteria->compare('activate_time',$this->activate_time);
        $criteria->compare('last_login_ip',$this->last_login_ip,true);
        $criteria->compare('last_login_time',$this->last_login_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    } 
    
    public function hasRole($role)
    {
        foreach ($this->getRoles() as $value) {
            if ($role==$value)
                return true;
        } 
        return false;
    }
    /**
     * Get all the roles the account is attached to.
     * Note that the roles selection is based on $uid
     * @see $this->getUid()
     * @return type
     */
    public function getRoles()
    {
        $roles = [];
        foreach(AuthAssignment::model()->getRoles($this->uid) as $role){
            $roles[] = $role->itemname;
        }
        return $roles;
    } 
    
    public static function isSubAccount(SActiveRecord $model)
    {
        //For those sub account having own sequence id as sub_account_id
        return in_array(get_class($model), [
            'CustomerAccount',
            //'MerchantAccount',//todo to add merchant staff account..
        ]);
    }
    
    public static function getAccountClass($id)
    {
        $type = substr($id, 0, 1);//take out first char
        switch ($type) {
            case self::TYPE_CUSTOMER:
                return 'CustomerAccount';
            case self::TYPE_MERCHANT:
                return 'MerchantAccount';//todo
            default:
                return 'Account';//main class
        }
    }
    /**
     * Check if account is owned by shop or merchant account
     * i.e. Non signed up merchant account (direct Shopbay.org user)
     * 
     * @param type $id
     * @return type
     */
    public static function isSubType($id)
    {
        //extract first char and compare
        return in_array(substr($id,0, 1),[self::TYPE_CUSTOMER,self::TYPE_MERCHANT]);
    }
    
    public static function encodeId($type,$id)
    {
        return $type.$id;
    }

    public static function decodeId($encodedId)
    {
        $id = substr($encodedId, 1);//remove first char
        logTrace(__METHOD__." $encodedId decoded to $id");
        return $id;
    }    
    
    public static function assignSubAccountRole($role,$type,$id)
    {
        Rights::assign($role, self::encodeId($type,$id));
    }    
    
}

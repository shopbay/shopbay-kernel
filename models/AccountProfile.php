<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_account_profile".
 *
 * The followings are the available columns in table 's_account_profile':
 * @property integer $id
 * @property integer $account_id
 * @property string $alias_name
 * @property string $first_name
 * @property string $last_name
 * @property string $gender
 * @property string $birthday
 * @property string $mobile
 * @property string $locale
 * @property string $email
 * @property integer $image
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 * 
 * @author kwlok
 */
class AccountProfile extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return AccountProfile the static model class
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
        return 's_account_profile';
    }
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
            ],
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'transitionMedia'=>false,
                'label'=>Sii::t('sii','Avatar'),
                'stateVariable'=>SActiveSession::ACCOUNT_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_ACCOUNT,
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'self',
                'localeAttribute'=>'locale',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'alias',
                'buttonIcon'=>[
                    'enable'=>true,
                ],
            ],            
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['alias_name, first_name, last_name', 'length', 'max'=>50],
            ['gender', 'length', 'max'=>1],
            ['mobile', 'numerical', 'min'=>8, 'integerOnly'=>true],
            ['mobile', 'length', 'max'=>20],
            ['birthday, locale', 'safe'],
            ['birthday', 'default', 'setOnEmpty'=>true, 'value' => null],
            ['image', 'numerical', 'integerOnly'=>true],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, account_id, alias_name, first_name, last_name, gender, birthday, mobile, image', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' =>Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'alias_name' => Sii::t('sii','Alias Name'),
            'first_name' => Sii::t('sii','First Name'),
            'last_name' => Sii::t('sii','Last Name'),
            'gender' => Sii::t('sii','Gender','model'),
            'birthday' => Sii::t('sii','Birthday'),
            'mobile' => Sii::t('sii','Mobile'),
            'locale' => Sii::t('sii','Language'),
            'image' => 'Avatar',
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return [
            'alias_name' => Sii::t('sii','This name will be used as your display name when you post comments, ask questions etc , and the default recipient name when you checkout.'),
        ];
    }  
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('alias_name',$this->alias_name,true);
        $criteria->compare('first_name',$this->first_name,true);
        $criteria->compare('last_name',$this->last_name,true);
        $criteria->compare('gender',$this->gender,true);
        $criteria->compare('birthday',$this->birthday,true);
        $criteria->compare('mobile',$this->mobile,true);
        $criteria->compare('locale',$this->locale,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }
    
    public function saveAccountAddress()
    {
        $this->account->address->save();
    } 
    /**
     * Get alias 
     * @return type
     */
    public function getAlias()
    {
        if ($this->alias_name!=null)
            return $this->alias_name;
        else
            return Sii::t('sii','{app} user',['{app}'=>Yii::app()->name]); 
    }    
        
    public function getAvatar($version,$htmlOptions=['style'=>'vertical-align:top'])
    {
        return $this->getImageThumbnail($version,$htmlOptions);
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('account');
    }        
}
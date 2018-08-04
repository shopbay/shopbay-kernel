<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_tag".
 *
 * The followings are the available columns in table 's_tag':
 * @property integer $id
 * @property integer $account_id
 * @property integer $name
 * @property integer $display_name
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Tag extends SActiveRecord 
{ 
    use LanguageModelTrait;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Brand the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Tag|Tags',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_tag';
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
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'account',
            ], 
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],              
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ],            
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, display_name', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>50],
            ['name', 'ruleNameWhitelist'],
            //This column stored json encoded display_name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            ['display_name', 'length', 'max'=>2000],

            ['id, account_id, name, display_name, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Verify name whitelist method
     * @param type $attribute
     * @param type $params
     * @return type
     */
    public function ruleNameWhitelist($attribute,$params)
    {
        if (!preg_match('/^[\p{L}0-9-#_]+$/u', $this->$attribute))
            $this->addError($attribute,Sii::t('sii','URL accepts only letters, digits, hypen, underscore and hex key.'));
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
            'id' => 'ID',
            'account_id' => Sii::t('sii','Account'),
            'name' => Sii::t('sii','Tag'),
            'display_name' => Sii::t('sii','Name'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('display_name',$this->display_name,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this,[
                    'criteria'=>$criteria,
                    'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                ]);
    }    
    /**
     * Return account profile
     * @return type
     */
    public function getAccountProfile()
    {
        return $this->account->profile;
    }    
    /**
     * Return view url
     * @return type
     */
    public function getViewUrl() 
    {
        return url('tags/management/view/'.$this->id);
    }    
    /**
     * Return view url
     * @return type
     */
    public function getUrl($name=null,$action='topics') 
    {
        return url('community/'.$action.'/'.urlencode(isset($name)?$name:$this->name));
    }
    /**
     * Get tag display name
     * @param type $locale
     */
    public function tagName($locale)
    {
        return $this->displayLanguageValue('display_name',$locale);
    }
    /**
     * Retrieve tag display text
     * 
     * @return type
     */
    public static function getList($locale)
    {
        $cacheKey = SCache::TAGS_CACHE.$locale;//store per locale
        $list = Yii::app()->commonCache->get($cacheKey);
        if($list===false){
            $list = new CMap();
            foreach (Tag::model()->findAll() as $tag) {
                $list->add($tag->name,$tag->tagName($locale));
            }
            Yii::app()->commonCache->set($cacheKey , $list);
            logTrace(__METHOD__.' Add tags to cache '.$cacheKey,$list);
        }
        return $list->toArray();
    } 
}
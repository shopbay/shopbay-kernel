<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_config_account".
 * The idea is to have account level configuration, that can override system default configuration (s_config)
 * Note: This is an admin function, and not for directly editable by account user themselves.
 * 
 * The followings are the available columns in table 's_config_account':
 * @property integer $id
 * @property string $account_id
 * @property string $category
 * @property string $name
 * @property string $value
 *
 * @author kwlok
 */
class ConfigAccount extends Config 
{
    /*
     * Supported account level config parameters
     */
    const PRODUCT_IMAGES_LIMIT = 'limit_product_image';
    const SHOP_BANNERS_LIMIT   = 'limit_shop_banner';
    const MEDIA_STORAGE_LIMIT  = 'media_storage_limit';//usage refer to Media
    const MEDIA_MOUNT_POINT    = 'media_mount_point';//usage refer to MediaStorage
    /**
     * Returns the static model of the specified AR class.
     * @return Config the static model class
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
        return 's_config_account';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, category, name, value', 'required'],
            ['account_id', 'length', 'max'=>12],
            ['category', 'length', 'max'=>20],
            ['name', 'length', 'max'=>50],
            ['id, account_id, category, name, value', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'account_id' => Sii::t('sii','Account'),
        ]);
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
        $criteria->compare('category',$this->category,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('value',$this->value,true);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    public function getViewUrl() 
    {
        return url('configs/account/view/id/'.$this->id);
    }
    
    public static function getAccountSystemSetting($account,$param, $default=null, $overwrite=false)
    {
        $value=Yii::app()->commonCache->get(ConfigAccount::cacheKey($account, ConfigAccount::SYSTEM,$param));
        if($value===false || $overwrite)
            $value = ConfigAccount::setCache($account, ConfigAccount::SYSTEM, $param, $default, $overwrite);
        return $value;
    }
    
    public static function getAccountBusinessSetting($account,$param, $default=null, $overwrite=false)
    {
        $value=Yii::app()->commonCache->get(ConfigAccount::cacheKey($account, ConfigAccount::BUSINESS,$param));
        if($value===false || $overwrite)
            $value = ConfigAccount::setCache($account, ConfigAccount::BUSINESS, $param, $default, $overwrite);
        return $value;
    }
    /**
     * Default is business setting
     */
    public static function getSetting($account,$param, $default=null, $overwrite=false)
    {
        return self::getAccountBusinessSetting($account, $param, $default, $overwrite);
    }
    /**
     * Set setting
     */
    public static function setSetting($account,$category, $param, $value)
    {
        $value = ConfigAccount::setCache($account, $category, $param, $value, true);
        self::refreshAccountSetting($account, $category, $param);
        return $value;
    }
    
    public static function refreshAccountSetting($account, $category,$param)
    {
        Yii::app()->commonCache->delete(ConfigAccount::cacheKey($account, $category,$param));
        ConfigAccount::setCache($account, $category, $param);//refresh no need default value as is refreshing new / existing value
        logInfo(__METHOD__." For account '$account' category '$category' param '$param': new value", Yii::app()->commonCache->get(ConfigAccount::cacheKey($account, $category,$param)));
    }
    /**
     * Regenerate $value because it is not found in cache
     * and save it in cache for later use
     * 
     * @param type $account
     * @param type $category
     * @param type $param
     * @param type $default The default value - If not set, it will try to pick up from parent {@link Config} if any
     * @param type $overwrite If to overwrite existing value using $default
     * @return type
     */
    private static function setCache($account, $category, $param, $default=null, $overwrite=false)
    {
        $criteria=new CDbCriteria;
        $criteria->addColumnCondition([
            'account_id'=>$account,
            'category'=>$category,
            'name'=>$param,
        ]);
        $config = ConfigAccount::model()->find($criteria);
        if ($config!=null && !$overwrite){//found and not overwrite
            logTrace(__METHOD__.' Account level config found! ',$config->attributes);
            $value = $config->value;
        }
        elseif ($config!=null && $overwrite && $default!=null){//found and overwrite existing value
            logTrace(__METHOD__." Account level config found, but overwrite existing value from $config->value to $default",$config->attributes);
            $config->value = $default;
            $value = $config->value;
            if ($config->save()){
                logTrace(__METHOD__." New config value saved!",$config->attributes);
            }
            else
                logError(__METHOD__." Failed to save config value!",$config->errors);
        }
        else {
            $value = isset($default) ? $default: static::loadFromConfigTable($category, $param);
            //auto create account level record
            if ($value!=false){
                $model = new ConfigAccount();
                $model->account_id = $account;
                $model->category = $category;
                $model->name = $param;
                $model->value = $value;
                if ($model->save()){
                    logTrace(__METHOD__.' account level config record created.',$model->attributes);
                }
                else  {
                    logTrace(__METHOD__.' account level config record creation failed.',$model->errors);
                }
            }
        }
        Yii::app()->commonCache->set(ConfigAccount::cacheKey($account,$category,$param) , $value);
        return $value; 
    }
    
    private static function cacheKey($account,$category,$param)
    {
        return $account.'_'.$category.'_'.$param;
    }
    /**
     * Auto load config param from Config table. if found any
     * @param type $param
     * @return string $value The config param value
     */
    private static function loadFromConfigTable($category,$param)
    {
        logTrace(__METHOD__.' account level config not found; search into default config...');
        if ($category==Config::BUSINESS)
            return Config::getBusinessSetting($param);
        else if ($category==Config::SYSTEM)
            return Config::getSystemSetting($param);
        else
            return false;
    }
}
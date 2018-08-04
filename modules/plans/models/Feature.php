<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.plans.models.FeatureTrait');
Yii::import('common.modules.plans.models.FeatureRbac');
/**
 * This is the model class for table "s_feature".
 *
 * The followings are the available columns in table 's_feature':
 * @property integer $id
 * @property string $name
 * @property string $group
 * @property string $params
 *
 * @author kwlok
 */
class Feature extends CActiveRecord
{
    use FeatureTrait;
    const KEY_SEPARATOR = '_';
    const LIMIT_PATTERN = '_n_';
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
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
        return Sii::t('sii','Feature|Features',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_feature';
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['name, group', 'required'],
            ['name, group', 'length', 'max'=>255],
            ['params', 'length', 'max'=>500],
        ];
    }    
    
    public function saveAsRbac($rule)
    {
        if (!$this->validate()){
            logError(__METHOD__.' validation errors',$this->errors);
            return false;
        }
        //search feature id 
        $map = Feature::getMap();
        foreach ($map[$this->group] as $feature) {
            if ($feature['name']==$this->name)
                $this->id = $feature['id'];
        }
        $this->save();
        $rbac = new FeatureRbac();
        $rbac->name = $this->toKey();
        $rbac->type = 2;//fixed value, as rbac item
        $rbac->rule_name = $rule;
        $rbac->created_at = time();
        $rbac->updated_at = time();
        $rbac->save();
        return $this;
    }
    
    public function getRbacRule()
    {
        $rbac = FeatureRbac::model()->find('name=\''.$this->toKey().'\'');
        if ($rbac==null)
            return Sii::t('sii','unset');
        else
            return $rbac->rule_name;
    }
    
    public function getParam($field)
    {
        $params = json_decode($this->params,true);
        return isset($params[$field])?$params[$field]:null;
    }

    public function toKey()
    {
        return $this->id.Feature::KEY_SEPARATOR.$this->name.Feature::KEY_SEPARATOR.$this->group;
    }
    
    public static function toArray()
    {
        Yii::beginProfile(__METHOD__);
        $features = Yii::app()->cache->get(SCache::FEATURES_CACHE);
        if($features===false){
            $data = new CMap();
            foreach (Feature::model()->findAll() as $feature) {
                $name = Feature::siiGroup()[$feature->group].' '.Helper::DOUBLE_ARROW_CHAR.' '.Feature::getNameDesc($feature->name);
                $data->add($feature->toKey(),$name);
            }
            Yii::app()->cache->set(SCache::FEATURES_CACHE , $data->toArray());
            logTrace(__METHOD__.' Add features to cache '.SCache::FEATURES_CACHE,$data->getCount());
        }
        Yii::endProfile(__METHOD__);
        return $features;
    }   
    
    public static function parseKey($key,$targetField=null)
    {
        $feature = explode(Feature::KEY_SEPARATOR, $key);
        if (is_array($feature)){
            if (!isset($targetField))
                return $feature;
            else {
                switch ($targetField) {
                    case 'id':
                        return $feature[0];
                    case 'name':
                        return $feature[1];
                    case 'group':
                        return $feature[2];
                    default:
                        return null;
                }
            }
        }
        else
            return $key;//return back $key
    }
        
    public static function getKey($name)
    {
        $record = self::getRecord($name);
        if ($record!=null)
            return $record->toKey();
        else
            return '0_Key-Not-Found_Error';//follow key pattern; in a way make it no key, means no permission
    }    
    
    public static function getRecord($name)
    {
        $record = Yii::app()->commonCache->get(SCache::FEATURE_CACHE.$name);
        if($record==null)
            $record = self::_cache($name);
        return $record;
    }    
    /**
     * Find feature in subscription plan
     * @param type $planName
     * @param type $patternizedFeatureName
     * @return Feature
     */
    public static function findRecordInPlan($planName,$patternizedFeatureName)
    {
        Yii::import('common.modules.plans.models.SubscriptionPermission');
        $featureKey = SubscriptionPermission::model()->fuzzySearch($planName,$patternizedFeatureName);
        if ($featureKey==$patternizedFeatureName)//same name
            return Feature::getRecord($featureKey);//no need to parse key
        else 
            return Feature::getRecord(Feature::parseKey($featureKey, 'name'));
    }       

    private static function _cache($name)
    {
        $criteria=new CDbCriteria;
        $criteria->condition='name=\''.$name.'\'';
        $record = Feature::model()->find($criteria);
        Yii::app()->commonCache->set(SCache::FEATURE_CACHE.$name , $record);
        return $record; 
    }
    
}

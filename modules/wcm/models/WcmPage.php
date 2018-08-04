<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.pages.models.PageTrait');
/**
 * This is the model class for table "s_wcm_page".
 *
 * The followings are the available columns in table 's_wcm_page':
 * @property integer $id
 * @property integer $account_id
 * @property string $name 
 * @property string $content 
 * @property string $params 
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class WcmPage extends CActiveRecord
{
    use PageTrait;
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ShopAddress the static model class
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
        return 's_wcm_page';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Page|Pages',[$mode]);
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
                'buttonIcon'=>true,
            ],
            'content' => [
                'class'=>'common.components.behaviors.ContentBehavior',
            ],            
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, content', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>255],            
            //['content', 'length', 'max'=>10000], //TODO no limit?         
            ['content','rulePurify'],//TODO enable this cannot have html tags inside content?
            ['params', 'length', 'max'=>5000],
            ['id, account_id, name, content, params, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * This rule perform purify content
     * This is to prevent malicious code; e.g without this, 
     * content contains script can get executed: <script>alert("test");</script>
     * 
     * @param type $attribute
     * @param type $params
     */
    public function rulePurify($attribute,$params)
    {
        if (is_array($this->$attribute)){
            foreach ($this->$attribute as $locale => $localeContent) {
                logTrace(__METHOD__.' validate locale '.$locale,$localeContent);
                $this->validatePurifyContent($attribute, $localeContent);//method inhertied from ContentBehavior    
            }
        }
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
        return array_merge($this->pageSeoAttributeLabels(),[
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'name' => Sii::t('sii','Name'),
            'content' => Sii::t('sii','Content'),
            'params' => Sii::t('sii','Page Params'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ]);
    }

    public static function getCacheContent($page)
    {
        $content=Yii::app()->commonCache->get(self::cacheKey($page));
        if($content===false)
            $content = WcmPage::setCache($page);
        return $content;
    }

    public static function refreshCache($page)
    {
        Yii::app()->commonCache->delete(self::cacheKey($page));
        WcmPage::setCache($page);
    }
    /**
     * Regenerate $value because it is not found in cache
     * and save it in cache for later use
     * 
     * @param type $page
     * @return type
     */
    private static function setCache($page)
    {
        $criteria=new CDbCriteria;
        $criteria->select='content';
        $criteria->condition='name=\''.$page.'\'';
        $model = WcmPage::model()->find($criteria);
        if ($model!=null){
            $content = WcmPage::model()->find($criteria)->content;
            Yii::app()->commonCache->set(self::cacheKey($page) , $content);
        }
        return isset($content)?$content:null; 
    }    
    
    public static function getCacheSeo($page)
    {
        $cache=Yii::app()->commonCache->get(self::cacheKey($page.'seo'));
        if($cache===false)
            $cache = WcmPage::setCacheSeo($page);
        return $cache;
    }
    
    public static function refreshCacheSeo($page)
    {
        Yii::app()->commonCache->delete(self::cacheKey($page.'seo'));
        WcmPage::setCacheSeo($page);
    }    
    /**
     * Set SEO cache
     * 
     * @param type $page
     * @return type
     */
    private static function setCacheSeo($page)
    {
        $criteria=new CDbCriteria;
        $criteria->select='params';
        $criteria->condition='name=\''.$page.'\'';
        $model = WcmPage::model()->find($criteria);
        if ($model!=null){
            $params = WcmPage::model()->find($criteria)->params;
            Yii::app()->commonCache->set(self::cacheKey($page.'seo') , $params);
        }
        return isset($params)?$params:null; 
    }      
    
    private static function cacheKey($page)
    {
        return SCache::PAGE_CACHE.'_'.$page;
    }
    
}

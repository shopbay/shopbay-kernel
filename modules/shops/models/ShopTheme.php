<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.services.workflow.models.Transitionable');
Yii::import('common.modules.themes.models.Theme');
Yii::import('common.modules.themes.models.ThemeParams');
/**
 * This is the model class for table "s_shop_theme".
 *
 * The followings are the available columns in table 's_shop_theme':
 * @property integer $id
 * @property integer $shop_id
 * @property string $theme
 * @property string $style
 * @property string $params
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Shop $shop
 *
 * @author kwlok
 */
class ShopTheme extends Transitionable
{
    use ThemeParams;
    private $_m;//theme model instance
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
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
        return 's_shop_theme';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Theme|Themes',[$mode]);
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
                'accountSource'=>'shop',
            ], 
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>true,
            ],
            'transition' => [
              'class'=>'common.components.behaviors.TransitionBehavior',
              'activeStatus'=>Process::THEME_ONLINE,
              'inactiveStatus'=>Process::THEME_OFFLINE,
            ],
            'workflow' => [
              'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ],              
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['shop_id, theme, style', 'required'],
            ['theme, style', 'length', 'max'=>25],
            ['status', 'length', 'max'=>20],
            ['shop_id', 'numerical', 'integerOnly'=>true],
            ['params', 'safe'],
            ['id, shop_id, theme, style, params, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => Sii::t('sii','Shop'),
            'theme' => Sii::t('sii','Theme'),
            'style' => Sii::t('sii','Theme Style'),
        ];
    }
    /**
     * A helper method to get theme owner
     * @return \ShopTheme
     */
    public function getThemeOwner()
    {
        return $this->shop;
    }
    /**
     * A helper method (wrapper) by shop
     * @param string $shop
     * @return \ShopTheme
     */
    public function locateOwner($shop) 
    {
        return $this->locateShop($shop);
    }    
    /**
     * A finder method by shop
     * @param string $shop
     * @return \ShopTheme
     */
    public function locateShop($shop) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'shop_id'=>$shop,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }    
    /**
     * A finder method by theme and style
     * @param string $theme
     * @param string $style
     * @return \ShopTheme
     */
    public function locateTheme($theme,$style=null) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'theme'=>$theme,
        ]);
        if (isset($style)){
            $criteria->addColumnCondition([
                'style'=>$style,
            ]);
        }
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }    
    /**
     * Url to view this model (update infact)
     * @return string url
     */
    public function getViewUrl()
    {
        return static::viewUrl($this->shop, $this->theme, $this->style);
    }        
    /**
     * To be compatible with Activity recording
     * @return string
     */
    public function getName()
    {
        return $this->theme;
    }
    
    public function getModel()
    {
        if (!isset($this->_m)){
            $this->_m = Theme::model()->locateTheme($this->theme)->find();
            if ($this->_m==null)
                throw new CException('Theme not found');
        }
        return $this->_m;
    }    
    /**
     * Make sure only one theme is stay active at one time
     */
    public function saveTheme($saveAsCurrent=false)
    {
        if ($saveAsCurrent){
            //Make all other shop themes to offline 
            foreach ($this->shop->themes as $theme) {
                $theme->status = Process::THEME_OFFLINE;
                $theme->update();
            }
            $this->status = Process::THEME_ONLINE;
        }
        else {
            $this->status = Process::THEME_OFFLINE;
        }
        $this->save();
    }
    /**
     * Get theme setting
     * @param string $group
     * @return array
     */
    public function getSetting($group)
    {
        return $this->getParam('settings')[$group];
    }    
    /**
     * Get theme setting group
     * @param string $group
     * @return array
     */
    public function getSettingGroups()
    {
        return $this->getParam('settings')!=null ? array_keys($this->getParam('settings')) : [];
    }      
    /**
     * Get theme setting
     * @param type $field
     * @param type $group
     * @return string
     */
    public function getValue($field,$group=null)
    {
        if (isset($group)){
            $setting = $this->getSetting($group);
            if ($setting!=null){
                return isset($setting[$field]) ? $setting[$field] : null;
            }
        }
        else {
            foreach ($this->getSettingGroups() as $group) {
                $value = $this->getValue($field, $group);
                if ($value!=null)//loop through all groups until find one
                    return $value;
            }
        }
        return null;
    }
    /**
     * Create shop level theme and load its settings map 
     * @param type $shop
     * @param type $theme
     * @param type $style
     * @param type $status
     * @return \ShopTheme
     */
    public static function create($shop,$theme,$style,$status=Process::THEME_OFFLINE)
    {
        $model = new ShopTheme();
        $model->shop_id = $shop;
        $model->theme = $theme;
        $model->style = $style;
        $model->status = $status;//subsequent shop theme is default OFFLINE until user save it as ONLINE
        $model->save();
        $settingsMap = $model->model->getSettingsMap();//getting setting map from Theme model
        if ($model->getParam('settings')==null){//retrieve shop theme local settings values
            $mySettings = [];
            //install the default theme settings values for first time
            foreach ($settingsMap as $id => $setting) {
                //logTrace(__METHOD__.' Traverse setting map '.$id,$setting);
                foreach ($setting['fields'] as $i => $field) {
                    $mySettings[$id][$field['id']] = $field['value'];
                }
                $model->saveParamField('settings', $mySettings);
                //logTrace(__METHOD__.' Init setting '.$id,$mySettings[$id]);
            }
        }
        return $model;
    }
    /**
     * Url to view shop theme
     * @return string url
     */
    public static function viewUrl(Shop $shop, $theme, $style)
    {
        return url('shop/themes/update/'.$shop->slug.'?theme='.$theme.'&style='.$style);
    }        
}

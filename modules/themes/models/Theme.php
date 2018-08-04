<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.services.workflow.models.Transitionable');
Yii::import('common.modules.themes.models.ThemeParams');
Yii::import('common.modules.themes.models.ThemeStyle');
Yii::import('common.modules.shops.components.BasePage');
/**
 * This is the model class for table "s_theme".
 *
 * The followings are the available columns in table 's_theme':
 * @property integer $id
 * @property integer $account_id
 * @property string $theme
 * @property string $theme_group
 * @property string $name
 * @property string $desc
 * @property string $designer
 * @property float $price
 * @property string $currency
 * @property string $params
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Theme extends Transitionable 
{ 
    use ThemeParams, LanguageModelTrait;
    
    private $_s = [];//array of ThemeStyle
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
        return Sii::t('sii','Theme|Themes',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_theme';
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
            'transition' => [
              'class'=>'common.components.behaviors.TransitionBehavior',
              'activeStatus'=>Process::THEME_ONLINE,
              'inactiveStatus'=>Process::THEME_OFFLINE,
            ],
            'workflow' => [
              'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ],              
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ],                      
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],            
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, theme, theme_group, name, designer, price', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            ['theme', 'length', 'max'=>25],
            ['theme_group', 'length', 'max'=>20],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            ['name', 'length', 'max'=>1000],
            ['desc', 'length', 'max'=>10000],//buffers each theme 500 chars
            ['designer', 'length', 'max'=>100],
            ['currency', 'length', 'max'=>3],
            ['status', 'length', 'max'=>20],            
            ['params', 'length', 'max'=>5000],
            ['price', 'length', 'max'=>10],
            ['price', 'type', 'type'=>'float'],
            
            //on deactivate scenario, id field here as dummy
            ['status', 'ruleDeactivation','params'=>[],'on'=>'deactivate'],
            
            ['id, account_id, theme, theme_group, name, desc, designer, price, currency, params, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Deactivation Check
     * (1) Verify that page is not in used in any categories
     */
    public function ruleDeactivation($attribute,$params)
    {
        Yii::import('common.modules.shops.models.ShopTheme');
        if (ShopTheme::model()->locateTheme($this->theme)->exists())
            $this->addError('status',Sii::t('sii','This theme is in use and must always stay online.'));
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
            'theme' => Sii::t('sii','Theme'),
            'name' => Sii::t('sii','Name'),
            'desc' => Sii::t('sii','Description'),
            'price' => Sii::t('sii','Price'),
            'currency' => Sii::t('sii','Currency'),
            'params' => Sii::t('sii','Theme Params'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
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
     * A finder method by theme
     * @param string $theme
     * @return \Theme
     */
    public function locateTheme($theme) 
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('theme=\''.$theme.'\'');
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * A finder method by theme group
     * @param string $group
     * @return \Theme
     */
    public function locateGroup($group) 
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('theme_group=\''.$group.'\'');
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    
    public function getViewUrl() 
    {
        return url('theme/view/'.$this->id);
    }
    /**
     * Theme preview url (view inside preview panel)
     * @param type $owner
     * @param type $style
     * @param type $page
     * @return type
     */
    public function getPreviewUrl($owner,$style,$page=null) 
    {
        if (!isset($page))
            $page = BasePage::HOME;
        
        $route = 'themes/preview/'.$this->theme.'/'.$style;
        $pageModel = Page::model()->locateOwner($owner)->locatePage($page)->find();
        if ($pageModel!=null)
            return url($route.'?page='.$pageModel->id);
        else
            return null;
    }
    
    public function getThemeStoreUrl() 
    {
        return url('themes/'.$this->theme);
    }
        
    public function getAdminViewUrl() 
    {
        return url('themes/admin/view/'.$this->id);
    }

    public function saveSettingsMap($map)
    {
        $this->saveParamField('settings_map', $map);
    }
    
    public function getSettingsMap()
    {
        $map = $this->getParam('settings_map');
        if ($map===null){
            $map = Tii::getThemeLayout($this->theme_group, $this->theme, 'settings_map.json');
            $this->saveSettingsMap($map);
            $map = $this->getParam('settings_map');//query back
        }
        return $map;
    }
    
    public function saveLayoutMap($map)
    {
        $this->saveParamField('layout_map', $map);
    }
    
    public function saveStyles($styles)
    {
        $this->saveParamField('styles', $styles);
    }
    /**
     * @return array of ThemeStyle objects
     */
    public function getStyles()
    {
        if (empty($this->_s)){
            $styles = $this->getParam('styles');
            if ($styles==null){//init styles if not set
                $this->saveStyles(ThemeStyle::config($this->theme_group, $this->theme));
                $styles = $this->getParam('styles');//pick up again
            }
            foreach ($styles as $id => $config) {
                //logTrace(__METHOD__.' style '.$id,$config);
                $this->_s[$id] = new ThemeStyle($this->theme_group, $this->theme,$id,$config);
            }
        }
        return $this->_s;
    }
    /**
     * Get available styles
     * @return array
     */
    public function getAvailableStyles()
    {
        $styles = $this->styles;
        unset($styles[Tii::STYLE_COMMON]);//remove out common share style
        return $styles;
    }
    /**
     * Get styles total
     * @return int
     */
    public function getTotalStyles()
    {
        return count($this->availableStyles);
    }
    /**
     * Get a particular style
     * @param type $style
     * @return type
     */
    public function getStyle($style=Tii::STYLE_DEFAULT)
    {
        return $this->styles[$style];
    }
    
    public function formatPrice()
    {
        if ($this->price==0)
            return Sii::t('sii','Free');
        else
            return $this->accountProfile->formatCurrency($this->price,$this->currency);
    }
 
}
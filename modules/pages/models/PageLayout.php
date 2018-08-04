<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.themes.models.ThemeParams');
Yii::import('common.modules.pages.models.PageOwnerTrait');
Yii::import("common.widgets.sgridlayout.SGridLayout");
/**
 * This is the model class for table "s_page_layout".
 *
 * The followings are the available columns in table 's_page_layout':
 * @property integer $id
 * @property integer $owner_id 
 * @property string $owner_type  
 * @property integer $theme_id
 * @property string $theme
 * @property string $page
 * @property string $name
 * @property string $layout
 * @property string $params
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class PageLayout extends SActiveRecord 
{ 
    use ThemeParams, PageOwnerTrait;
    
    public static $defaultLayout  = 'default_layout';
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
        return Sii::t('sii','Page Layout|Page Layouts',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_page_layout';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge($this->ownerBehaviors(),[
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>true,
            ],
        ]);
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge($this->ownerRules(),[
            ['theme_id, theme, name, layout', 'required'],
            ['theme_id', 'numerical', 'integerOnly'=>true],
            ['theme, name, page', 'length', 'max'=>25],
            ['params', 'length', 'max'=>1000],
            ['id, theme_id, theme, name, page, layout, params, create_time, update_time', 'safe', 'on'=>'search'],
        ]);
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'theme' => [self::BELONGS_TO, 'Theme', 'theme_id'],
        ];
    }
    /**
     * A finder method by theme and layout
     * @param string $theme
     * @param string $page
     * @return \PageLayout
     */
    public function locatePage($theme,$page) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'theme'=>$theme,
            'page'=>$page,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }    
    /**
     * A finder method by layout
     * @param string $layout
     * @return \PageLayout
     */
    public function locateLayout($theme,$layout,$page=null) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'theme'=>$theme,
            'name'=>$layout,
        ]);
        if (isset($page)){
            $criteria->addColumnCondition([
                'page'=>$page,
            ]);
        }
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }     
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge($this->ownerAttributeLabels(),[
            'id' => 'ID',
            'account_id' => Sii::t('sii','Account'),
            'theme_id' => Sii::t('sii','Theme'),
            'theme' => Sii::t('sii','Theme'),
            'page' => Sii::t('sii','Page Name'),
            'name' => Sii::t('sii','Layout Name'),
            'layout' => Sii::t('sii','Page Layout'),
            'params' => Sii::t('sii','Theme Params'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ]);
    }
    /**
     * @see merchant/config/main.php for url mapping: pages/layout/edit/<page_name>
     * @return type
     */
    public function getViewUrl() 
    {
        return url('pages/layout/edit/'.$this->page);
    }
    /**
     * Save layout
     * @param array $config 
     */
    public function saveLayout($config)
    {
        if (!(isset($config['user']) && isset($config['layout'])))
            throw new CException('Missing layout config keys');
        
        //[1] Get layout 
        $layout = $this->parseLayout($config['user'],$config['layout']);
        //[2] Convert layout to json string
        $this->layout = json_encode($layout);
        logTrace(__METHOD__.' Final layout to save', $layout);
        
        $this->update();
        
        $customPageContent = $this->findCustomPage($layout);
        //Save custom page content copy if any
        //Only save when current theme is being edited
        if ($config['layoutName']==static::$defaultLayout && 
            $config['theme']==$this->owner->theme && 
            isset($customPageContent)){
            $this->saveCustomPageContentCopy(PageLayout::findPageModel($this->owner, $this->page), $customPageContent);
        }
        else {
            logTrace(__METHOD__.' Skip saving a copy of custom page content',$this->page);
        }
    }
    /**
     * Parse layout each column types and pick up field to convert to target data format
     * @param type $config
     * @return type
     */
    protected function parseLayout($user,$config)
    {
        foreach ($config as $i => $row) {
            if (isset($row['columns'])){
                foreach ($row['columns'] as $j => $column) {
                    if ($column['type']==SGridLayout::CONTAINER_BLOCK && isset($column['rows'])){
                        $config[$i]['columns'][$j]['rows'] = $this->parseLayout($user,$column['rows']);
                    }
                    elseif ($column['type']==SGridLayout::SLIDE_BLOCK){
                        $items = [];
                        foreach ($column as $key => $value) {
                            if (strpos($key, '_t')==false){//skip any non-numeric keys, e.g. template key
                                if (strpos($key, 'slideImage')!==false){//incoming format is slideImage_n
                                    $index = str_replace('slideImage_', '', $key);
                                    $items[$index]['image'] = $this->saveUploadImage($user,$value,SGridLayout::SLIDE_BLOCK);
                                    unset($config[$i]['columns'][$j][$key]);//remove temp key
                                }
                                if (strpos($key, 'slideText')!==false){//incoming format is slideText_n
                                    $index = str_replace('slideText_', '', $key);
                                    $items[$index]['text'] = json_decode($value,true);//language field
                                    unset($config[$i]['columns'][$j][$key]);//remove temp key
                                }
                                if (strpos($key, 'slideCtaLabel')!==false){//incoming format is slideCtaLabel_n
                                    $index = str_replace('slideCtaLabel_', '', $key);
                                    $items[$index]['ctaLabel'] = json_decode($value,true);//language field
                                    unset($config[$i]['columns'][$j][$key]);//remove temp key
                                }
                                if (strpos($key, 'slideCtaUrl')!==false){//incoming format is slideCtaUrl_n
                                    $index = str_replace('slideCtaUrl_', '', $key);
                                    $items[$index]['ctaUrl'] = $value;
                                    unset($config[$i]['columns'][$j][$key]);//remove temp key
                                }
                            }
                            
                            if (strpos($key, '_t')!==false){
                                unset($config[$i]['columns'][$j][$key]);//remove template key
                            }
                        }
                        //logTrace(__METHOD__.' slide items',$items);
                        $config[$i]['columns'][$j]['items'] = array_values($items);//remove items keys as not required to store
                    }
                    elseif ($column['type']==SGridLayout::IMAGE_BLOCK){
                        foreach ($column as $key => $value) {
                            if ($key=='bgImage'){
                                $bgImage = $this->saveUploadImage($user,$value,SGridLayout::IMAGE_BLOCK);
                                $config[$i]['columns'][$j]['style'] .= ';background-image:url('.$bgImage.');';
                                unset($config[$i]['columns'][$j][$key]);//remove temp key
                                break;//since only one image, break here
                            }
                        }                        
                    }
                    elseif ($column['type']==SGridLayout::BOX_BLOCK){
                        foreach ($column as $key => $value) {
                            if ($key=='boxImage'){
                                $boxImage = $this->saveUploadImage($user,$value,SGridLayout::BOX_BLOCK);
                                $config[$i]['columns'][$j][$key] = $boxImage;//update saved image url
                                break;//since only one image, break here
                            }
                        }                        
                    }
                }                
            }
        }     
        return $config;
    }
    
    protected function findCustomPage($layout) 
    {
        foreach ($layout as $i => $row) {
            if (isset($row['columns'])){
                foreach ($row['columns'] as $j => $column) {
                    if ($column['type']==SGridLayout::HTML_BLOCK){//for custom page using html block and default layout, save content at s_page for new themes reference
                        foreach ($column as $key => $value) {
                            if ($key=='html'){
                                return $value;//Expect only one html block, and keep the first block only. break here
                            }
                        }                        
                    }
                }                
            }
        }    
        return null;
    }
    /**
     * For custom page using html block and default layout, save content at s_page for new themes reference
     * @see Page notes
     */
    protected function saveCustomPageContentCopy(Page $pageModel,$content) 
    {
        if ($pageModel!=null && $pageModel instanceof Page){
            $pageModel->content = json_encode($content);
            $pageModel->save();
            logTrace(__METHOD__.' Save a copy of custom page content to s_page',$pageModel->attributes);
        }
    }
    
    protected function saveUploadImage($user,$inputPath,$group)
    {
        if ($inputPath==null || strlen($inputPath)==0)
            return $inputPath;//no image specified
        
        $filepath = Yii::app()->getBasePath().'/www'.$inputPath;
        if (file_exists($filepath) && is_file($filepath)){
            $image = [
                'initialFilepath'=>$filepath,
                'name'=>basename($filepath),//lost the original file name
                'filename'=>basename($filepath),
                'mime_type'=>CFileHelper::getMimeType($filepath),
                'size'=>filesize($filepath),
                'owner'=>$this->getOwner(),
                'media_group'=>$this->page.'_'.$group,
            ];
            $mediaAssoc =  Yii::app()->serviceManager->mediaManager->create($user,$image);
            logTrace(__METHOD__.' Create image media record.', $mediaAssoc->attributes);
            return $mediaAssoc->getUrl(request()->isSecureConnection);
        }
        else
            return $inputPath;//existing image url (e.g. from media gallery) will reach here
    }
    /**
     * Find the underlying page model by page id
     * @param CActiveRecord $owner The page owner model
     * @param string $page
     * @return \Shop
     */
    public static function findPageModel($owner, $page)
    {
        $ownerPageClass = get_class($owner).'Page';
        Yii::import('common.modules.shops.components.ShopPage');
        $map = explode('_', $page);//e.g. home_page, custom_page_1, product_page_x
        if (isset($map[2]) && strpos($page,ShopPage::PRODUCT)!==false){
            return Product::model()->findByPk($map[2]);
        }
        elseif (isset($map[2]) && strpos($page,ShopPage::CAMPAIGN)!==false){
            return CampaignBga::model()->findByPk($map[2]);
        }
        elseif (isset($map[2]) && strpos($page,'custom_page')!==false){//@see Page::getLayoutMapId()
            return Page::model()->findByPk($map[2]);
        }
        elseif ($ownerPageClass::isCustomPage($page)){//for those in-built custom page
            return Page::model()->locateOwner($owner)->locatePage($page)->find();
        }
        else {
            return $owner;//if not page model, return back shop model 
        }
    }
}
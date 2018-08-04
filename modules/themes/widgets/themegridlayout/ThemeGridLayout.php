<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.SGridLayout");
Yii::import("common.modules.themes.widgets.themegridlayout.ThemePageTrait");
Yii::import("common.modules.pages.models.Page");
Yii::import("common.modules.pages.models.PageLayout");
/**
 * Description of ThemeGridLayout
 * This layout uses bootstrap grid system (underlying SGridLayout engine)
 * This also include parser read the layout config, provides helper methods and transform widget to readily use for display
 * 
 * @author kwlok
 */
abstract class ThemeGridLayout extends SGridLayout
{
    use ThemePageTrait;
    
    public static $widgetProperty          = 'w:';//direct getter method
    public static $widgetInvertedProperty  = 'w:!';//direct getter boolean (inverted value)
    public static $widgetFunction          = 'w::';//method invocation
    public static $widgetImage             = 'w:+';//render image
    public static $widgetView              = 'w:~';//render view file
    /**
     * Local property
     */
    protected $layoutMap;//the layout map  
    protected $settingsMap;//the settings map  
    protected $layoutModels =[];//the layout models
    /**
     * @var Theme Theme model
     */
    public $theme;
    /**
     * @var ShopPage The local page object
     */
    public $page;
    /**
     * @var string Current page locale      
     */
    public $locale;
    /**
     * Get the theme basepath
     */
    abstract public function getThemeBasepath();
    /**
     * Get theme owner model 
     * @return CModel
     */
    abstract public function getThemeOwnerModel();     
    /**
     * Get theme internal name 
     * @return string
     */
    public function getThemeName()
    {
        return $this->page->currentTheme;
    }        
    /**
     * Get theme style 
     * @return string
     */
    public function getThemeStyle()
    {
        return $this->page->currentStyle;
    }
    /**
     * Get theme page name (must be unique)
     * Custom page need to attach page model id to differentiate each page (as each can have own layout specification)
     * @return type
     */
    public function getThemePage()
    {
        if ($this->page->model instanceof Page)
            return $this->page->model->layoutMapId;
        else
            return $this->page->id;
    }        
    /**
     * Find the theme page layout based on owner, theme and page name;
     * Auto creates one for first time access
     * @return ThemePage
     */
    public function getPageLayout($themePage=null)
    {
        if (!isset($this->theme))
            throw new CException(__CLASS__.' Theme is not set.');
        if (!isset($this->page))
            throw new CException(__CLASS__.' Page object is not set.');

        if (!isset($themePage))
            $themePage = $this->themePage;
        
        $this->loadLayoutMap($this->theme);
        $this->loadSettingsMap($this->theme);
            
        if (!isset($this->layoutModels[$themePage])){

            if ( ($this->layoutModels[$themePage] = $this->getPageLayoutModel($themePage)) == null) {
                //Decide which layout and page
                $layout = $this->existsFactoryLayout($themePage) ? $this->getLayoutMapParam($themePage,'layout') : PageLayout::$defaultLayout;
                //Create one and save into db
                $this->layoutModels[$themePage] = $this->createPageLayout($this->theme, $layout, $themePage);
                logTrace(__METHOD__." Create layout ".$this->layoutModels[$themePage]->name." of theme ".$this->layoutModels[$themePage]->theme,$themePage);
            }
            else {
                //Theme layout customized at page level will take precedence! 
                logTrace(__METHOD__." Use existing customized layout ".$this->layoutModels[$themePage]->name." of theme ".$this->layoutModels[$themePage]->theme." for page",$themePage);
            }
        }
        
        return $this->layoutModels[$themePage];
    }
    /**
     * Get page layout customized at page level 
     * @return PageLayout
     */
    public function getPageLayoutModel($page)
    {
        return PageLayout::model()->locateOwner($this->themeOwnerModel)->locatePage($this->themeName,$page)->find();
    }
    /**
     * Get the page layout basepath
     */
    public function getPageLayoutBasepath()
    {
        return $this->themeBasepath.DIRECTORY_SEPARATOR.$this->themeName.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'layouts';
    }
    /**
     * Load the layout map
     * If not found, use back factory settings (a json-based file)
     */
    public function loadLayoutMap(Theme $theme)
    {
        $this->layoutMap = $theme->getParam('layout_map');
        if ($this->layoutMap==null){
            $this->layoutMap = $this->factoryLayoutMap;//use factory settings
            $theme->saveLayoutMap($this->layoutMap);
            logTrace(__METHOD__." map",$this->layoutMap);
        }
    }
    /**
     * Load the theme local settings 
     * If not found, use back factory settings (a json-based file)
     */
    public function loadSettingsMap(Theme $theme)
    {
        $this->settingsMap = $theme->getSettingsMap();
    }
    /**
     * Get the layout map param
     * @return string|null
     */
    public function getLayoutMapParam($page,$field)
    {
        if (isset($this->layoutMap[$page][$field]))
            return $this->layoutMap[$page][$field];
        else
            return null;
    }    
    /**
     * Get the factory settings map
     */
    public function getFactorySettingsMap()
    {
        return json_decode(file_get_contents($this->pageLayoutBasepath.DIRECTORY_SEPARATOR.'settings_map.json'),true);
    }
    /**
     * Get the factory layout map
     */
    public function getFactoryLayoutMap()
    {
        return json_decode(file_get_contents($this->pageLayoutBasepath.DIRECTORY_SEPARATOR.'layout_map.json'),true);
    }
    /**
     * Get the factory layout file
     */
    public function getFactoryLayoutFile($layout)
    {
        return $this->pageLayoutBasepath.DIRECTORY_SEPARATOR.$layout.'.json';
    }    
    /**
     * Get the factory layout file
     */
    public function existsFactoryLayout($pageId)
    {
        return $this->getLayoutMapParam($pageId,'layout')!=null && file_exists($this->getFactoryLayoutFile($this->getLayoutMapParam($pageId,'layout')));
    }     
    /**
     * Get the factory page layout
     */
    public function getFactoryLayout($layout)
    {
        return json_decode(file_get_contents($this->getFactoryLayoutFile($layout)),true);
    }
    /**
     * Create theme page layout record from factory setting
     * @param type $theme
     * @param type $layout
     * @param type $page
     * @return \PageLayout
     */
    public function createPageLayout(Theme $theme,$layout,$page)
    {
        $model = new PageLayout();
        $model->owner_id = $this->themeOwnerModel->id;
        $model->owner_type = get_class($this->themeOwnerModel);
        $model->theme_id = $theme->id;
        $model->theme = $theme->theme;
        $model->name = $layout;
        $model->page = $page;
        $model->layout = json_encode($this->getFactoryLayout($layout));
        if (!$model->validate()){
            logError(__METHOD__.' Failed to save theme page layout.',$model->errors);
            throw new CException(__CLASS__.' Failed to save theme page layout.');
        }
        $model->save();
        logTrace(__METHOD__.' PageLayout is created successfully from factory setting.',$model->attributes);
        return $model;
    }       
    /**
     * Entire theme layout config
     * @return type
     */
    public function getLayout()
    {
        return $this->parseLayoutConfig(json_decode($this->pageLayout->layout,true));
    }     
    /**
     * Parse widget config and assign values accordingly
     * @param array $config
     * @return array
     */
    protected function parseLayoutConfig($config)
    {
        foreach ($config as $i => $row) {
            //scan row attributes
            foreach ($row as $key => $value) {
                if ($key=='include'){//to include other row 
                    $config[$i] = $this->getIncludeFile($value);
                    //logTrace(__METHOD__.' include row after parse layout config '.$value,$config[$i]);
                    $config[$i]['include'] = $value;//set the include value
                    $config[$i]['locked'] = true;//for include row, always locked it (cannot edit)
                    //Also lock included columns
                    foreach ($config[$i]['columns'] as $k => $col) {
                        $config[$i]['columns'][$k]['locked'] = true;
                    }
                }
                elseif ($this->isWidget($value)){
                    //logTrace(__METHOD__." row $key contains widget $value ");
                    $config[$i][$key] = $this->getWidget($value);
                }
            }
            //scan column attributes
            if (isset($row['columns'])){
                foreach ($row['columns'] as $j => $column) {
                    foreach ($column as $key => $value) {
                        if ($this->isWidget($value)){
                            //logTrace(__METHOD__." column $key contains widget",$value);
                            $config[$i]['columns'][$j][$key] = $this->getWidget($value);
                        }
                        //array fields parsing: sub column field containing array, e.g. language attribute, list block $viewData etc
                        if (is_array($value) && $key!='rows'){
                            //logTrace(__METHOD__." column $key is array", $value);
                            foreach ($value as $subkey => $subvalue) {
                                if ($this->isWidget($subvalue)){
                                    //logTrace(__METHOD__." column $subkey is widget field", $subvalue);
                                    $config[$i]['columns'][$j][$key][$subkey] = $this->getWidget($subvalue);
                                }
                                elseif (is_array($subvalue)){//third level array, e.g. items at slideblock
                                    //logTrace(__METHOD__." third level array element", $subvalue);
                                    foreach ($subvalue as $subsubkey => $subsubvalue) {
                                        if ($this->isWidget($subsubvalue)){
                                            logTrace(__METHOD__." column $subsubkey is widget field", $subsubvalue);
                                            $config[$i]['columns'][$j][$key][$subkey][$subsubkey] = $this->getWidget($subsubvalue);
                                        }
                                    }
                                }
                            }
                        }

                        if (($key=='rows')){ //nested rows! parse nested layout!
                            $rows = $this->parseLayoutConfig($value);
                            $config[$i]['columns'][$j][$key] = $rows;
                        }
                        
                        if (($key=='container') && !empty($value)){ //include exterinal container
                            $rows = $this->getIncludeFile($value,true);
                            $config[$i]['columns'][$j]['rows'] = $rows;//add rows
                            $config[$i]['columns'][$j]['locked'] = true;//for include rows, always locked it (cannot edit)
                        }
                    }
                }
            }
        }
        //logTrace(__METHOD__.' layout after conversion', $config);
        return $config;        
    }
    /**
     * Check if config value is pointing to a widget 
     * @param string $value
     */
    public function isWidget($value)
    {
        if (is_scalar($value)){
            return substr($value, 0, 2)==static::$widgetProperty || 
                   substr($value, 0, 3)==static::$widgetInvertedProperty || 
                   substr($value, 0, 3)==static::$widgetImage || 
                   substr($value, 0, 3)==static::$widgetView || 
                   substr($value, 0, 3)==static::$widgetFunction;
        }
        return false;
    }
    /**
     * For include file, we read from db (if any), if not factory setting 
     * @param string $file
     */
    public function getIncludeFile($file,$container=false)
    {
        $include = json_decode($this->getPageLayout($file)->layout,true);
        //logTrace(__METHOD__.' Before '.$value,$include);
        $parse = $this->parseLayoutConfig($include);//parse include file layout as well if it contains any widgets
        if ($container)
            return $parse;//return the entire container layout
        else
            //Load the include row into layout
            return $parse[0];//always take the first row (expect one row only in fact)
    }    
    /**
     * Obtain the widget by id
     * [1] w: //direct getter method
     * "Id" Format =  w:ab_cd (separated by underscore)
     * "Property" format = abCd (camel case)
     * 
     * [2] w:: //method invocation with params
     * Example row and column settings:
     * <pre>
     * {
     *   "name":"promotion-items",
     *   "columns":[
     *       {
     *           "size":12,
     *           "type":"categoryblock",
     *           "name":"promotions",
     *           "title":"w::t+Latest Promotions",
     *           "dataProvider":"w::getPageDataProvider+promotions_page",
     *           "itemView":"w::getThemeView+_promotion_banner",
     *           "viewData":{
     *               "page":"w:page"
     *           },
     *           "itemsPerRow":4,
     *           "itemsLimit":4,
     *           "style":"background:white"
     *       },
     *       {
     *           "size":12,
     *           "type":"categoryblock",
     *           "name":"latest-news",
     *           "title":"w::t+Latest Articles",
     *           "dataProvider":"w::getPageDataProvider+news_page",
     *           "itemView":"w::getThemeView+_news+{\"trim\":\"3\"}",
     *           "viewData":{
     *               "page":"w:page"
     *           },
     *           "itemsPerRow":4,
     *           "itemsLimit":4,
     *           "viewData":{"length":50,"link":true},
     *           "style":"background:whitesmoke"
     *       }
     *   ]
     * },          
     * </pre>
     * @param string $value
     */
    public function getWidget($value)
    {
        $parseParam = function($input) {
            $param = json_decode($input,true);
            if (is_array($param)){
                foreach ($param as $key => $value) {
                    $param[$key] = $this->isWidget($value) ? $this->getWidget($value) : $value;
                }
                $result = $param;
            }
            elseif ($param!=null) {
                $result = $this->isWidget($param) ? $this->getWidget($param) : $param;
            }
            else
                $result = $this->isWidget($input) ? $this->getWidget($input) : $input;
            
            //logTrace(__METHOD__.' parsing param input '.$input,$result);
            return $result;
        };
        
        //logTrace(__METHOD__.' parsing...',$value);
        if (substr($value, 0, 3)==static::$widgetFunction){
            $function = explode('+', substr($value, 3));
            $method = Helper::camelCase($function[0]);
            //logTrace(__METHOD__.' w:: function',$function);
            if (isset($function[1])){
                $param1 = $parseParam($function[1]);//parse 1st param
                //logTrace(__METHOD__.' param1',$param1);
            }            
            if (isset($function[2])){
                $param2 = $parseParam($function[2]);//parse 2nd param
                //logTrace(__METHOD__.' param2',$param1);
            }            
            
            if (isset($param1) && isset($param2))
                return call_user_method($method, $this, $param1, $param2);//support 2 params
            elseif (isset($param1))
                return call_user_method($method, $this, $param1);//support single param
            else
                return call_user_method($method, $this);
        }
        elseif (substr($value, 0, 3)==static::$widgetImage){
            $image = substr($value, 3);//discard first 3 chars "w:+"
            return $this->getThemeImage($image);
        }
        elseif (substr($value, 0, 3)==static::$widgetView){
            $viewFile = substr($value, 3);//discard first 3 chars "w:~"
            //logTrace(__METHOD__.' $widgetView',$viewFile);
            return $this->renderTheme($viewFile,['page'=>$this->page]);
        }
        elseif (substr($value, 0, 3)==static::$widgetInvertedProperty){
            $property = Helper::camelCase(substr($value, 3));//discard first 3 chars "w:!"
            return !$this->$property;
        }
        elseif (substr($value, 0, 2)==static::$widgetProperty){
            $property = Helper::camelCase(substr($value, 2));//discard first 2 chars "w:"
            return $this->$property;
        }
        else
            return null;
    }       
    /**
     * Get the theme view file 
     * @param type $view
     * @return string
     */
    public function getThemeView($view)
    {
        return $this->controller->getThemeView($view);        
    }    
    /**
     * Get theme image by filename
     * @return string image url
     */
    public function getThemeImage($filename)
    {
        $styleObj = $this->theme->getStyle($this->themeStyle);
        return $this->controller->getAssetsURL($styleObj->imagePathAlias).'/'.$filename;
    }   
    /**
     * Render a view or widget
     * @param type $view
     * @param type $data
     * @return string
     */
    public function renderTheme($view,$data=[])
    {
        return $this->controller->renderPartial($this->getThemeView($view),$data,true);        
    }    
    /**
     * Message translation facility
     * @param type $message
     * @param type $params
     * @return string
     */
    public function t($message,$params=[])
    {
        return Sii::t('sii',$message,$params);
    }
}

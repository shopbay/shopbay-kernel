<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SAssetManager
 * 
 * Extended CAssetManager mainly to provide a solution for 
 * [1] revving (auto versioning) css/js files as to leverage on browser cache
 * [2] asset bunlde for widget and module assets
 * [3] asset minification (css/js)
 * 
 * @author kwlok
 */
class SAssetManager extends CAssetManager 
{
    /**
     * @var boolean True to publish assets bundle
     */
    public $enableAssetBundle = true;
    /**
     * @var boolean True to publish minified assets files
     */
    public $enableMinfiy = true;
    /**
     * @var boolean whether we should enable revving asset bundle files, i.e. touching the asset folder change its modification time
     * and pave way later for SAssetManager::generatePath() to generate a new hashed name to achieve auto-revving.
     * This property is encouraged to be used during development stage as oftenly we update source files straight away 
     * and want to have asset latest version get published immediately
     * In production, this property is recommended set to false and let build script to handle the folder touch() to update modification time
     * 
     * @see This setting default is made available at config/main.php
     * 
     * @see SAssetManager::performRevving
     */
    public $enableRev = false;
    /**
     * @var boolean whether we should copy the asset files and directories even if they already published before.
     * This property is used only during development stage. The main use case of this property is when you need
     * to force the original assets always copied by changing only one value without searching needed {@link publish}
     * method calls across the application codebase.
     *  
     * @see More explanation at CAssetManager::forceCopy
     */
    public $forceCopy = false;    
    /**
     * Data structure
     * <pre>
     * array(
     *  'bundle1'=>array('asset1','asset2','asset3',...,'assetN')
     *  'bundle2'=>array('assetA','assetB','assetC',...,'assetZ')
     * );
     * </pre>
     * @var array $assetBundle specify the asset to put into asset bundle
     */
    public $assetBundle = [
        'common'=> [
            'assets',
        ],
        'widgets'=> [
            'sflash','sloader','smodal','spage','spageindex','spagelayout','spagemenu','spagesection','snavigationmenu','susermenu','sgridlayout',
            'simageviewer','stooltip','spagetab','schildform','simagemanager','simagezoomer','spagefilter','soffcanvasmenu','ssearch','sanimatedsearch',
        ],
        'modules'=>[
            'AccountsModule','ActivitiesModule','CommentsModule','HelpModule','WcmModule','SearchModule','CommunityModule',
            'ItemsModule','MessagesModule','NewsModule','NotificationsModule','TutorialsModule','TicketsModule','ChatbotsModule','ThemesModule',
            'OrdersModule','PaymentsModule','QuestionsModule','ShopsModule','AnalyticsModule','PlansModule','BillingsModule','PagesModule',
            'LikesModule','TasksModule','CartsModule','AttributesModule','CustomersModule','TagsModule','ConfigsModule','UsersModule',
            'BrandsModule','CampaignsModule','ProductsModule','ShippingsModule','InventoriesModule','TaxesModule','MediaModule',
        ],
        //Array value should be directory name, of substring of path 
        'themes'=>[
            'shopbay-shop-assets/themes','shopbay-shop-assets/widgets',
        ],
    ];
    /**
     * Asset to exclude adding ito asset bundle
     * @var array 
     */
    public $excludeAssetBundle = [
        'common'=>['_process.css'],
        'widgets'=>[],
        'modules'=>[
            'shopckeditor.js','shopckeditor_desc.js','productckeditor.js','brandckeditor.js','campaignckeditor.js',
            'messageckeditor.js','tutorialckeditor.js','pageckeditor.js',
        ],
        'themes'=>[],
    ];
    /**
     * @var array $minifiedExtensionFlags specify the extension names that
     * the extension will check against in order to not compress/minify the 
     * files. 
     */
    public $minifiedExtensionFlags = ['min.js','min.css','jpg','png'];//include image file here to skip minify in case they are put in css folder
    /**
     * Directories to exclude to perform revving if found
     * Array value should be directory name, of substring of path 
     * 
     * @var array 
     */
    public $excludeRev = [
        'yii','infiniteScroll','assets/chosen','assets/ckeditor','assets/material-icons',
        'assets/fancybox','assets/font-awesome','assets/images','elevatezoom',
        'xupload','supload','chart','mbmenu','pikabu','egmap','owlCarousel','oauth',
    ];
    /**
     * The media types (the file extension) to be excluded from asset rev
     * @var array 
     */
    public $excludeRevFileExtensions = [
        '.mov','.gif','.png','.mp3','.txt','.png','.jpg','.jpeg'
    ];
    /** 
     * Init
     */
    public function init()
    {
        $this->initCommonAssetBundle();
        $this->initWidgetAssetBundle('css');
        $this->initWidgetAssetBundle('js');
        $this->baseUrl = Yii::app()->urlManager->createCdnUrl('/assets');
        parent::init();
    }    
    /** 
     * Create asset bundle if not exists
     * Minimize asset first if they are not
     * 
     * @see createAssetBundle()
     */
    public function initCommonAssetBundle($forceCreate=false)
    {
        if ($this->enableAssetBundle){
            $this->createAssetBundle('common','css',Yii::getPathOfAlias('common'),Yii::getPathOfAlias('common.assets.css'),$forceCreate);
            $this->createAssetBundle('common','js',Yii::getPathOfAlias('common'),Yii::getPathOfAlias('common.assets.js'),$forceCreate);
        }
    }    
    /** 
     * Create asset bundle if not exists
     * Minimize asset first if they are not
     * 
     * @see createAssetBundle()
     */
    public function initWidgetAssetBundle($type,$forceCreate=false)
    {
        if ($this->enableAssetBundle){
            $this->createAssetBundle('widgets',$type,Yii::getPathOfAlias('common.widgets'),Yii::getPathOfAlias('common.assets.'.$type),$forceCreate);
        }
    }   
    /**
     * Register assets files based on type
     * It will first minifiy assets for first time, 
     * and register asset based on SAssetManager->enableMinfiy mode if to use minified asset files
     * 
     * For $filepath found in SAssetManager->excludeTouchDirs, no minification will be done
     * 
     * @see SAssetManager->excludeTouchDirs
     * @see self::_getAssetFilename()
     * 
     * @param type $type
     * @param type $filepath
     * @param type $filename
     * @param type $assetsUrl
     */
    public function registerAssets($type,$pathAlias,$filename)
    {
        $filepath = Yii::getPathOfAlias($pathAlias);        
        //logTrace('Register asset file '.$filepath.'/'.$filename);
        if ($this->_hasAssetContent($filepath.'/'.$filename)){
            if (!Helper::strpos_arr($filepath, $this->excludeRev)){
                //register common asset bundle
                if ($this->enableAssetBundle && strpos($filepath,'common/assets')!==false){
                    $this->registerScriptInternal($type, $filepath, $this->_getAssetBundleFilename($filepath,$type,'common'));
                    //logTrace('skip common use asset bundle instead '.$filepath);
                }
                //register widget asset bundle
                else if ($this->enableAssetBundle && Helper::strpos_arr($filepath, $this->assetBundle['widgets'])){
                    $publishpath = $this->_getWidgetAssetBundlePublishPath($type);
                    //-----------//
                    //Note: SAssetManager::revAssets() auto rev $publishpath (refer to SAssetManager::generatePath())
                    //Here we need to explicitly invoke SAssetManager::revAssets() because $filepath != $publishpath
                    $this->revAssets($filepath);
                    //-----------//
                    //Rewrite assets url
                    $assetsUrl = $this->publish($publishpath,false,-1,$this->forceCopy);
                    $this->registerScriptInternal($type, $filepath, $this->_getAssetBundleFilename($publishpath,$type),$assetsUrl);
                    //logTrace('skip widget use asset bundle instead '.$filepath);
                }
                //register module asset bundle
                else if ($this->enableAssetBundle && strpos($filepath,'modules')!==false){
                    $moduleAssetBundle = $this->_getAssetBundleFilename($filepath,$type);
                    //logTrace(__METHOD__.' module asset bundle name '.$moduleAssetBundle.' for '.$filepath);
                    if (file_exists($filepath.DIRECTORY_SEPARATOR.$moduleAssetBundle)){
                        //logTrace(__METHOD__.' use existing '.$moduleAssetBundle.' > '.$filepath);
                        $this->registerScriptInternal($type, $filepath, $moduleAssetBundle);
                    }
                    else{
                        //logTrace(__METHOD__.' '.$moduleAssetBundle.' not found, try to rev new asset bundle '.$filepath.' ...');
                        //Explicit call SAssetManager::revAssets() since $moduleAssetBundle not found
                        $this->revAssets($filepath);
                        if (file_exists($filepath.DIRECTORY_SEPARATOR.$moduleAssetBundle))
                            $this->registerScriptInternal($type, $filepath, $moduleAssetBundle);
                        else {//include min.css if asset bundle not generated
                            //logTrace(__METHOD__.' Minfiy asset bundle '.$filepath,$filename);
                            if ($this->enableMinfiy)
                                $this->{"minify{$type}"}($filepath, $filename);
                            $this->registerScriptInternal($type, $filepath, $this->getAssetFilename($filename));
                        }
                    }
                }        
                //normal individual asset register
                else {
                    if ($this->enableMinfiy)
                        $this->{"minify{$type}"}($filepath, $filename);
                    //logTrace(__METHOD__.' individual asset register '.$filepath.' '.$this->getAssetFilename($filename));
                    $this->registerScriptInternal($type, $filepath, $this->getAssetFilename($filename));
                }
            }
            else {
                $this->registerScriptInternal($type, $filepath, $filename);
                //logTrace('excludeRev asset register '.$filepath);
            }
        }
    }
    /**
     * DO NOT EDIT
     * Boilerplate to register css/js scripts
     * Internally it calls revAssets via generatePath() when required
     */
    protected function registerScriptInternal($type,$filepath,$filename,$assetsUrl=null)
    {
        if (!isset($assetsUrl))
            $assetsUrl = $this->publish($filepath,false,-1,$this->forceCopy);
        Yii::app()->clientScript->{'register'.($type=='js'?'Script':$type).'File'}($assetsUrl.'/'.$filename);
    }
    /**
     * Get asset file name
     * 
     * If neither in mode $forceMinify nor $this->enableMinfiy, it basically does nothing and returning back $filename
     * 
     * If run in mode $forceMinify nor $this->enableMinfiy(production), it will return minified filename based on $extension
     * This method basically changes file extension from [$extension] to min.[$extension] 
     * Example: from ".css" to ".min.css"
     * 
     * @param type $filename
     * @param type $forceMinify If true, it will ignore YII_DEBUG and always return minified file name 
     * @return string For non-YII_DEBUG return [$filename].min.[$extension], else return $filename
     */
    public function getAssetFilename($filename,$forceMinify=false)
    {
        if ($forceMinify || $this->enableMinfiy){
            $filename = explode('.', $filename);//split filename and file extension 
            return $filename[0].'.min.'.$filename[1];
        }
        else
            return $filename;
    }    
    /**
     * Minify css file
     * If file already exists, will skip generate $filename.min.css
     * 
     * @param type $dir
     * @param type $filename
     * @param type $forceMinify True will create one minified file and overwrite existing one
     */
    public function minifyCss($dir,$filename,$forceMinify=false)
    {
        $filepath = $dir.'/'.$filename;
        $forceMinify = $forceMinify || $this->_checkForceMinify($dir, $filename);
        if (!Helper::strpos_arr($filepath,$this->minifiedExtensionFlags)){
            $minAssetPath = $dir.'/'.$this->getAssetFilename($filename,true);
            if ($forceMinify || !file_exists($minAssetPath)){
                Yii::import('common.extensions.escriptboost.EScriptBoost');
                $minified = EScriptBoost::minifyCss(file_get_contents($filepath),EScriptBoost::CSS_MIN);
                file_put_contents($minAssetPath, $minified);  
                logInfo(__METHOD__.' '.$minAssetPath.' generated'.($forceMinify?' in "forceMinify" mode.':'.'));
            }
        }        
    }
    /**
     * Minify js file
     * If file already exists, will skip generate $filename.min.js
     * 
     * @param type $dir
     * @param type $filename
     * @param type $forceMinify True will create one minified file and overwrite existing one
     */
    public function minifyJs($dir,$filename,$forceMinify=false)
    {
        $filepath = $dir.'/'.$filename;
        $forceMinify = $forceMinify || $this->_checkForceMinify($dir, $filename);
        if (!Helper::strpos_arr($filepath,$this->minifiedExtensionFlags)){
            $minAssetPath = $dir.'/'.$this->getAssetFilename($filename,true);            
            if ($forceMinify || !file_exists($minAssetPath)){
                Yii::import('common.extensions.escriptboost.EScriptBoost');
                $minified= EScriptBoost::minifyJs(file_get_contents($filepath),EScriptBoost::JS_MIN);
                file_put_contents($minAssetPath, $minified);        
                logInfo(__METHOD__.' '.$minAssetPath.' generated'.($forceMinify?' in "forceMinify" mode.':'.'));
            }
        }
    }    
    /**
     * Generates path segments relative to basePath.
     * In addition, ensure $file hash is changed whenever there directory content (dir files) are modified)
     * 
     * @override
     * @param string $file for which public path will be created.
     * @param bool $hashByName whether the published directory should be named as the hashed basename.
     * @return string path segments without basePath.
     * @see CAssetManager::generatePath()
     */
    protected function generatePath($file,$hashByName=false)
    {
        if (!Helper::strpos_arr($file, $this->excludeRev)){
            $this->revAssets($file);
        }
        //else logTrace('skip rev '.$file);
        
        return parent::generatePath($file, $hashByName);
    }
    /**
     * This method will check any file directly under the $dir has last modification time larger than itself.
     * If yes, it means there are latest asset files need to be published, and triggers asset bunde to re-create,
     * and finally touch (unix command term) the dir to have its modification time changed to current system time (latest)
     * 
     * @param type $dir The directory to check against for revving
     */
    protected function revAssets($dir)
    {
        if ($this->enableRev){
            $dir = realpath($dir);
            //logTrace(__METHOD__.' Rev '.$dir);
            $filemtime = filemtime($dir);
            if ($handle = opendir($dir)) {
                while (false !== ($assetFile = readdir($handle))) {
                    if ($assetFile != "." && $assetFile != ".." && $assetFile != ".DS_Store" && !Helper::strpos_arr($assetFile,$this->minifiedExtensionFlags) && !Helper::strpos_arr($assetFile,$this->excludeRevFileExtensions)) {
                        $mtime = filemtime($dir.DIRECTORY_SEPARATOR.$assetFile);
                        //Condition: file last modification time against its parent dir
                        $rev = $mtime >= $filemtime;
                        //logTrace(__METHOD__.' Check file '.$dir.DIRECTORY_SEPARATOR.$assetFile.' dir.$mtime ('.$filemtime.') vs file.$mtime ('.$mtime.')', $rev ? 'Rev!' : 'Skip');                        
                        if ( $rev ){
                            logInfo(__METHOD__.' Rev asset! '.$assetFile.' mtime['.date("F d Y H:i:s.",$mtime).'] > dir '.$dir.' mtime['.date("F d Y H:i:s.",$filemtime).']');
                            //re-init common.assets.css/js asset bundle if any
                            if (strpos($dir, 'common/assets')!==false){
                                $type = ltrim(substr($dir,-3),'/');//extract asset type, expect "css" or "js"
                                logInfo(__METHOD__.' Re-init common.assets.'.$type.' asset bundle...',$assetFile);
                                $basepath = substr($dir,0,-strlen('/assets/'.$type));
                                $this->createAssetBundle('common',$type,$basepath,$dir,true);
                            }
                            //re-init widget bundle asset if any
                            if (strpos($dir, 'widgets')!==false){
                                $type = ltrim(substr($dir,-3),'/');//extract asset type, expect "css" or "js"
                                logInfo(__METHOD__.' Re-init widget asset bundle '.$type.'...',$assetFile);
                                $this->initWidgetAssetBundle($type,true); 
                                touch($this->_getWidgetAssetBundlePublishPath($type));//touch the publish location (since it is different from $dir) to change its modification time to current time
                            }
                            //re-init module/themes bundle asset if any
                            if (strpos($dir, 'modules')!==false){
                                //re-init themes bundle asset if any
                                if (Helper::strpos_arr($dir,$this->assetBundle['themes'])){
                                    //todo combine shop widgets/ shop themes into one asset bundle
                                    foreach ($this->assetBundle['themes'] as $theme) {
                                        if (strpos($dir, $theme)!==false){
                                            logInfo(__METHOD__.' Re-init '.$theme.' asset bundle...',$assetFile);
                                            if (!is_dir($dir.DIRECTORY_SEPARATOR.$assetFile)) {//skip if this is a sub folder
                                                $filename = explode('.', $assetFile);//split filename and file extension
                                                $basepath = $dir;
                                                $this->createAssetBundle('themes',$filename[1],$basepath,$dir,true);
                                            }
                                        }
                                    }
                                } 
                                else {//re-init module bundle asset if any
                                    foreach ($this->assetBundle['modules'] as $module) {
                                        $_m = strtolower(rtrim($module, 'Module'));
                                        if (strpos($dir, 'modules/'.$_m)!==false){
                                            logInfo(__METHOD__.' Re-init '.$_m.' module asset bundle...',$assetFile);
                                            $filename = explode('.', $assetFile);//split filename and file extension 
                                            $basepath = substr($dir,0,-strlen('/assets/'.$filename[1]));
                                            $this->createAssetBundle('modules',$filename[1],$basepath,$dir,true);
                                        }
                                    }
                                }
                            }
                            //re-minify application.css/js if any
                            if (strpos($dir, Yii::app()->id.'/assets')!==false){
                                $type = ltrim(substr($dir,-3),'/');//extract asset type, expect "css" or "js"
                                logInfo(__METHOD__.' Re-minify application.'.$type.' ...',$assetFile);
                                if ($this->enableMinfiy)
                                    $this->{"minify{$type}"}($dir,$assetFile,true);
                            }
                            touch($dir);//touch the dir to change its modification time to current time
                            break;
                        }
                        //else logTrace('no modification '.$assetFile);
                    }
                    //else logTrace('skip '.$assetFile);
                }
                closedir($handle);
            }
        }
    }
    /**
     * Check if file size bigger than zero
     * 
     * @param type $filepath
     * @return type
     */
    private function _hasAssetContent($filepath)
    {
        if (file_exists($filepath)){
            $filesize = filesize($filepath);
            //logTrace($filepath.' filesize='.$filesize.' bytes');
            if ($filesize > 0)
                return true;
        }
        return false;
    }  
    /**
     * Prepare assets bundle file path; 
     * Apply to widget / module assets loading
     * Asset directory structure has to be like following:-
     * <pre>
     * - assets
     *  - css
     *  - js
     * </pre>
     *  
     * @return array
     */
    protected function prepareAssetBundle($bundle,$type,$basepath)
    {
        //logTrace(__METHOD__.' pick up location '.$basepath);
        $assetBundle = [];
        if ($handle = opendir($basepath)) {
            while (false !== ($asset = readdir($handle))) {
                //pickup assets by name - for bundle modules / common etc
                $asset = explode('.', $asset);//ignore file extension if any, e.g. ProductsModule.php
                if (in_array($asset[0],$this->assetBundle[$bundle])) {
                    if ($bundle=='modules' || $bundle=='common'){
                        $toBundle = $basepath.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$type;
                        if (file_exists($toBundle))
                            $assetBundle[$asset[0].'/'.$type] = $toBundle;
                    }
                    else {
                        $toBundle = $basepath.DIRECTORY_SEPARATOR.$asset[0].DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$type;
                        if (file_exists($toBundle))
                            $assetBundle[$asset[0]] = $toBundle;
                    }
                }
            }
            closedir($handle);
        }
        
        //pickup assets by file path - for theme bunlde
        //check for theme asset bundle
        //todo This shop asset loading is specific to Shop only; Shop app should create own AssetManager.
        if ($bundle=='themes'){
            $toBundle = $basepath;
            //Only add shop widgets into bundle into theme common asset bundle
            if (strpos($toBundle, 'assets/css/common')!==false){
                $assetBundle = $this->prepareShopWidgetsAssetBundle($assetBundle,$type);
                logTrace(__METHOD__.' Add shop widgets into asset bundle',$assetBundle);
            }
            if (file_exists($toBundle))
                $assetBundle[$bundle.'/'.$type] = $toBundle;
        }
        
        if (!empty($assetBundle))
            logTrace(__METHOD__,$assetBundle);
        
        return $assetBundle;
    }
    /**
     * Pickup all the shop widgets and bundle together with shop theme
     * @param type $assetBundle
     * @param type $type
     * @return string
     */
    public function prepareShopWidgetsAssetBundle($assetBundle,$type)
    {
        $shopWidgetPath = Yii::getPathOfAlias('shopwidgets');//read from config.json
        if ($handle = opendir($shopWidgetPath)) {
            while (false !== ($asset = readdir($handle))) {
                //pickup assets by name - for bundle modules / common etc
                $asset = explode('.', $asset);//ignore file extension if any, e.g. ProductsModule.php

                $toBundle = $shopWidgetPath.DIRECTORY_SEPARATOR.$asset[0].DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$type;
                if (file_exists($toBundle)){
                    //logTrace(__METHOD__.' asset to be bundled location ',$toBundle);
                    $assetBundle['widget/'.$asset[0]] = $toBundle;
                }
            }
            closedir($handle);
        } 
        return $assetBundle;
    }
    /**
     * Create widget asset bundle if not exists
     * Minimize asset first if they are not
     * 
     * @see self::prepareAssetBundle() for the assets criteria to bundle together
     * 
     * @param string $bundle Bundle type, e.g. widget, module
     * @param string $type Asset type, e.g. css, js
     * @param string $basepath File path to create bundle
     * @param string $publishpath File path to publish bundle
     * @param string $forcePublish Default to false; If true, it will create new one and overwrite existing one
     */
    public function createAssetBundle($bundle,$type,$basepath,$publishpath,$forcePublish=false)
    {
        $publishpath = $publishpath.DIRECTORY_SEPARATOR.$this->_getAssetBundleFilename($publishpath,$type,$bundle=='common'?'common':null);
        //logTrace(__METHOD__.' basepath='.$basepath.', and to publish at location '.$publishpath);
        
        //Create widget asset bundle if not exists
        if ($forcePublish || !file_exists($publishpath)){
            $assetBundle = $this->prepareAssetBundle($bundle,$type,$basepath);
            if (!empty($assetBundle)){
                file_put_contents($publishpath,"/*!");
                file_put_contents($publishpath,"\n * Shopbay Theme Assets (http://shopbay.org)",FILE_APPEND);
                file_put_contents($publishpath,"\n * Copyright ".date('Y',time())." Shopbay.org. All rights reserved.",FILE_APPEND);
                file_put_contents($publishpath,"\n */",FILE_APPEND);
                file_put_contents($publishpath,"\n/*! This file is auto-generated on ".date('F d Y H:i:s.',time()).", do not edit it by hand. */",FILE_APPEND);
            }
            foreach ($assetBundle as $asset => $assetpath) {
                if (file_exists($assetpath)){
                    if ($handle = opendir($assetpath)) {
                        file_put_contents($publishpath,"\n/*--{$asset}--*/\n",FILE_APPEND);
                        while (false !== ($assetFile = readdir($handle))) {
                            if ($assetFile != "." && $assetFile != ".." && $assetFile != ".DS_Store" && !Helper::strpos_arr($assetFile,$this->minifiedExtensionFlags)) {
                                //minify asset files
                                if (!is_dir($assetpath.DIRECTORY_SEPARATOR.$assetFile)){//skip if this is a sub folder
                                    if ($this->enableMinfiy)
                                        $this->{"minify{$type}"}($assetpath, $assetFile,$forcePublish);
                                    if (!in_array($assetFile,$this->excludeAssetBundle[$bundle])){
                                        $toBundle = $assetpath.DIRECTORY_SEPARATOR.$this->getAssetFilename($assetFile);
                                        //added minfied files into asset bundle
                                        file_put_contents($publishpath,file_get_contents($toBundle),FILE_APPEND);
                                        logInfo(__METHOD__.' '.$this->getAssetFilename($assetFile).' added into asset bundle'.($forcePublish?' in "forcePublish" mode.':'.'));
                                    }
                                }
                            }
                        }
                        closedir($handle);
                    }
                }
            }
        }
    }
    /**
     * Get widget asset asset file name
     * 
     * @param string $publishpath
     * @param string $type
     * @param string $hashToken Extra token for file name hashing
     * @return string aseet bundle file name
     */
    private function _getAssetBundleFilename($publishpath,$type,$hashToken=null)
    {
        return 'assetbundle-'.$this->hash(realpath($publishpath).(isset($hashToken)?$hashToken:'')).'.min.'.$type;
    }        
    /**
     * Return widget asset bundle publish path location
     * 
     * @param type $type
     * @return type
     */
    private function _getWidgetAssetBundlePublishPath($type)
    {
        return Yii::getPathOfAlias('common.assets.'.$type);
    } 
    /**
     * Extra Condition check: file last modification time against its minified file
     * @param type $dir
     * @param type $filename
     * @return boolean
     */        
    private function _checkForceMinify($dir,$filename)
    {
        $filepath = $dir.'/'.$filename;
        $filemtime = filemtime($filepath);
        $minFile = $this->getAssetFilename($filename,true);
        $minAssetPath = $dir.'/'.$minFile;
        if (file_exists($minAssetPath)){
            $minFilemtime = filemtime($minAssetPath);
            $force = $minFilemtime!=null && $minFilemtime < $filemtime;
            if ($force){
                $this->forceCopy = true;//todo will this trigger all other assets file to force published? here we only want the specific affected min file
                logInfo(__METHOD__.' Force minify! Minifiled file last modification time is older than own source file: $minFilemtime ('.$minFilemtime.') vs $filemtime ('.$filemtime.')',$minAssetPath);
                return true;
            }
            else
                return false;
        }
        else
            return true;//if file not exist, minify!
    }
          
}

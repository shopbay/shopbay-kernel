<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of AssetLoaderBehavior
 *
 * @author kwlok
 */
class AssetLoaderBehavior extends CBehavior 
{
    /**
    * @var string The name of owner. Defaults to 'undefined'
    */
    public $name = 'undefined';
    /**
    * @var string The name of the asset path alias. Defaults to 'null'
    */
    public $pathAlias;
    /**
     * Return path alias
     */
    public function getPathAlias()
    {
        return $this->pathAlias;
    }
    /**
     * Registers the standard CSS and JS files packaged under standard structure
     * Apply for module assets loading
     * <pre>
     * - assets
     *  - css
     *  - js
     * </pre>
     */
    public function registerScripts()
    {
        $this->getOwner()->registerCommonFiles();
        $this->registerCssFile($this->pathAlias.'.css', $this->name.'.css');
        $this->registerScriptFile($this->pathAlias.'.js', $this->name.'.js');
        Yii::app()->clientScript->registerCoreScript('jquery');
        $this->registerJui();
    }    
    /**
     * Registers the necessary css files.
     * Prefer to be called after $this->registerScripts() to load core script first
     */
    public function registerCssFile($pathAlias,$filename)
    {
        if (is_array($filename)){
            foreach ($filename as $value){
                Yii::app()->assetManager->registerAssets('css', $pathAlias, $value);
            }
        }
        else
            Yii::app()->assetManager->registerAssets('css', $pathAlias, $filename);
    }    
    /**
     * Registers the necessary css files.
     * Prefer to be called after $this->registerScripts() to load core script first
     */
    public function registerScriptFile($pathAlias,$filename)
    {
        if (is_array($filename)){
            foreach ($filename as $value){
                Yii::app()->assetManager->registerAssets('js', $pathAlias, $value);
            }
        }
        else {
            Yii::app()->assetManager->registerAssets('js', $pathAlias, $filename);
        }
    }    
   /**
    * Publishes the module assets path.
    * @return string the base URL that contains all published asset files.
    */
    public function getAssetsURL($pathAlias)
    {
        $filepath=Yii::getPathOfAlias($pathAlias);
        if (file_exists($filepath)){
            return Yii::app()->assetManager->publish($filepath);
        }
    }  
   /**
    * Wrapper of SAssetManager::getAssetsFilename()
    */
    public function getAssetFilename($filename,$forceMinify=false)
    {
        return Yii::app()->assetManager->getAssetFilename($filename,$forceMinify=false);
    }  
    /**
     * Registers Fancybox library and its necessary js files.
     */
    public function registerFancybox()
    {
        $this->getOwner()->registerCssFile('common.assets.fancybox','jquery.fancybox-1.3.4.css');
        $this->getOwner()->registerScriptFile('common.assets.fancybox','jquery.fancybox-1.3.4.pack.js');
    }
    /**
     * Registers Chosen library and its necessary js files.
     */
    public function registerChosen()
    {
        $this->getOwner()->registerCssFile('common.assets.chosen','chosen.css');
        $this->getOwner()->registerScriptFile('common.assets.chosen','chosen.jquery.js');
    }
    /**
     * Registers CKEDITOR library and its necessary js files.
     */
    public function registerCkeditor($type,$pathAlias=null)
    {
        $this->getOwner()->registerScriptFile('common.assets.ckeditor','ckeditor.js');
        if (isset($pathAlias))
            $this->registerScriptFile($pathAlias, $type.'ckeditor.js');
        else
            $this->registerScriptFile($this->pathAlias.'.js', $type.'ckeditor.js');
    }
    /**
     * Registers JUI library assets.
     */
    public function registerJui()
    {
        Yii::app()->clientScript->registerCoreScript('jquery.ui');
        Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css');
    }
    /**
     * Registers CStarRating assets.
     */
    public function registerRating()
    {
        Yii::app()->clientScript->registerCoreScript('rating');
        Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/rating/jquery.rating.css');
    }
    /**
     * Registers CTab assets.
     */
    public function registerTab()
    {
        Yii::app()->clientScript->registerCoreScript('yiitab');
    }
    /**
     * Registers Zocial library.
     */
    public function registerZocial()
    {
        $assetsURL=$this->getOwner()->getAssetsURL('common.modules.accounts.oauth.widgets.assets');
        Yii::app()->clientScript->registerCssFile($assetsURL.'/css/zocial.css');
    }
    /**
     * Registers OwlCarousel assets.
     */
    public function registerOwlCarousel()
    {
        $this->getOwner()->registerScriptFile('common.extensions.owlCarousel.assets','owl.carousel.min.js');
        $this->getOwner()->registerCssFile('common.extensions.owlCarousel.assets',['owl.carousel.css','owl.theme.css']);
    }
    /**
     * Registers InfiniteScroll assets.
     */
    public function registerInfiniteScroll()
    {
        $this->getOwner()->registerScriptFile('common.extensions.infiniteScroll.assets.js','jquery-ias.min.js');
        $this->getOwner()->registerCssFile('common.extensions.infiniteScroll.assets.css','jquery.ias.css');
    }
    /**
     * Registers FontAwesome library.
     */
    public function registerFontAwesome()
    {
        $assetsURL=$this->getOwner()->getAssetsURL('common.assets.font-awesome');
        Yii::app()->clientScript->registerCssFile($assetsURL.'/css/font-awesome.css');
    }
    /**
     * Registers listview css file.
     */
    public function registerListViewCssFile()
    {
        $this->getOwner()->registerCssFile('common.assets.css',['listview.css','pager.css']);
    }
    /**
     * Register SListView scripts
     */
    protected function registerSListViewScript()
    {
        $listView = new SListView();
        $listView->registerJsScript();
    }    
    /**
     * Registers gridview css file.
     */
    public function registerGridViewCssFile()
    {
        $this->getOwner()->registerCssFile('common.assets.css',['gridview.css','pager.css']);
    }
    /**
     * Registers pager css file.
     */
    public function registerPagerCssFile()
    {
        $this->getOwner()->registerCssFile('common.assets.css','pager.css');
    }
    /**
     * Registers tab css file.
     */
    public function registerTabCssFile()
    {
        $this->getOwner()->registerCssFile('common.assets.css','tab.css');
    }
    /**
     * Registers form css file.
     */
    public function registerFormCssFile()
    {
        $this->getOwner()->registerCssFile('common.assets.css','form.css');
    }
    /**
     * Registers common css and js file.
     */
    public function registerCommonFiles()
    {
        $this->getOwner()->registerCssFile('common.assets.css','common.css');
        $this->getOwner()->registerScriptFile('common.assets.js','common.js');
    }
    /**
     * Return image url
     */
    public function getImageURL($filename)
    {
        $assetsURL=$this->getOwner()->getAssetsURL($this->pathAlias.'.images');
        return $assetsURL.'/'.$filename;
    }
    /**
     * Return css url
     */
    public function getCssURL($filename)
    {
        $assetsURL=$this->getOwner()->getAssetsURL($this->pathAlias.'.css');
        return $assetsURL.'/'.$filename;
    }
    /**
     * Registers process css file.
     * Dynamically create help_process.css based on the color code retrieved from DB
     */
    public function registerProcessCssFile()
    {        
        Yii::import('common.modules.tasks.models.Process');
        $cssFilename = 'help_process.css';
        $cssBasepath = Yii::getPathOfAlias('help.assets.css');
        $filepath = $cssBasepath.DIRECTORY_SEPARATOR.$cssFilename;
        if (!file_exists($filepath)){
            //adding css of each process status label
            $css = "code.status, span.status {padding:0px 3px;text-transform:uppercase;color:white;font-size:0.9em;border:0;border-radius: 3px;}\n";
            foreach (Process::getList() as $process) {
                //adding css of each process status label   
                $css .= ".status.".str_replace(" ","-",strtolower($process['text']))." {background:".$process['color'].";}\n";
            }
            // Write the contents to the file
            file_put_contents($filepath, $css);
        }
        $this->getOwner()->registerCssFile('common.modules.help.assets.css',$cssFilename);
    }
    /**
     * Registers "tasks" script files to run tasks via javascript.
     * If needed, register tasks css files as well (when $css is true)
     */
    public function registerTaskScript($css=false)
    {        
        $this->getOwner()->registerScriptFile('common.modules.tasks.assets.js','tasks.js');
        if ($css)
            $this->getOwner()->registerCssFile('common.modules.tasks.assets.css','tasks.css');
    }
    /**
     * Registers "search" script files to run search via javascript.
     * If needed, register css files as well (when $css is true)
     */
    public function registerSearchScript($css=false)
    {        
        $this->getOwner()->registerScriptFile('common.modules.search.assets.js','search.js');
        if ($css)
            $this->getOwner()->registerCssFile('common.modules.search.assets.css','search.css');
    }
    /**
     * Registers "analytics" script files to run tasks via javascript.
     */
    public function registerAnalyticsScript()
    {        
        $this->getOwner()->registerScriptFile('common.modules.analytics.assets.js','analytics.js');
        $this->getOwner()->registerCssFile('common.modules.analytics.assets.css','analytics.css');
        $this->getOwner()->registerScriptFile('common.modules.analytics.widgets.chart.assets.js','d3.v3.js');
        $this->getOwner()->registerScriptFile('common.modules.analytics.widgets.chart.assets.js','nv.d3.min.js');
        $this->getOwner()->registerCssFile('common.modules.analytics.widgets.chart.assets.css','nv.d3.min.css');
        $this->getOwner()->registerScriptFile('common.widgets.spagesection.assets.js','spagesection.js');
        $this->getOwner()->registerCssFile('common.widgets.spagesection.assets.css','spagesection.css');
    }
    /**
     * Registers "carts" script files to run tasks via javascript.
     * If needed, register tasks css files as well (when $css is true)
     * Cart module need to be local registered, and provision own cart asset files
     */
    public function registerCartScript($css=false)
    {        
        $this->getOwner()->registerScriptFile('application.modules.carts.assets.js','carts.js');
        if ($css)
            $this->getOwner()->registerCssFile('application.modules.carts.assets.css','carts.css');
    }
    /**
     * Registers "supload" script files to run tasks via javascript.
     */
    public function registerSUploadScript()
    {        
        Yii::import('common.extensions.supload.SUpload');
        $supload = new SUpload();
        $supload->publishAssets();
    }
    /**
     * Registers bootstrap assets.
     * @see requires yii2engine and Yii.1.1.16
     */
    public function registerBootstrapAssets()
    {
        $assetsUrl = Yii::app()->assetManager->publish(readConfig('system', 'yii2Path').'/vendor/'.param('BOWER_ASSET_DIR').'/bootstrap/dist');
        Yii::setAlias('@bower', readConfig('system', 'yii2Path') . DIRECTORY_SEPARATOR . '/vendor/'.param('BOWER_ASSET_DIR'));

        Yii::app()->clientScript->registerCssFile($assetsUrl.'/css/bootstrap.min.css');
        Yii::app()->clientScript->registerCssFile($assetsUrl.'/css/bootstrap-theme.min.css');
        Yii::app()->clientScript->registerScriptFile($assetsUrl.'/js/bootstrap.min.js');
    }
    /**
     * Registers ElevateZoom library and its necessary js files.
     */
    public function registerElevatezoom()
    {
        Yii::import('common.extensions.elevatezoom.ElevateZoom');
        $zoom = new ElevateZoom();
        $zoom->publishAssets();
    }    
    /**
     * Registers "smediagallery" assets files 
     */
    public function registerMediaGalleryAssets()
    {        
        $this->getOwner()->registerScriptFile('common.modules.media.widgets.smediagallery.assets.js','smediagallery.js');
        $this->getOwner()->registerCssFile('common.modules.media.widgets.smediagallery.assets.css','smediagallery.css');
    }
    /**
     * Registers Material Icons library (Google Icons)
     */
    public function registerMaterialIcons()
    {
        $assetsURL=$this->getOwner()->getAssetsURL('common.assets.material-icons');
        Yii::app()->clientScript->registerCssFile($assetsURL.'/material-icons.css');
    }
    
}
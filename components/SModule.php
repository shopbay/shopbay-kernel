<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SModule
 *
 * @author kwlok
 */
class SModule extends CWebModule 
{
    /*
     * ServiceManager run mode. This will affect how ServiceException is returning error; 
     * Default to "local"
     */
    public $serviceMode = 'local';
    /*
     * If to merge all the sii files from other dependent modules (set to dependencies sii) 
     * Default to "false"
     */
    public $mergeSii = false;
    /**
     * The module version.
     */    
    public $version;
    /**
     * @property dependencies objects.
     */
    private $_d;  
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [];//default empty
    }       
    /**
     * Pre init module
     * 
     * [1] Attach behaviors
     * [2] Create module asset bundle if Yii::app()->assetManager->enableAssetBundle is true
     */
    public function preinit() 
    {
        logTrace(__METHOD__.' *** start module '.$this->id.' ***');       
        $this->attachBehaviors($this->behaviors());
        if (!empty($this->behaviors()) && Yii::app() instanceof CWebApplication){
            //create module asset bundle
            if (Yii::app()->assetManager->enableAssetBundle){
                $basepath = Yii::getPathOfAlias($this->id);
                Yii::app()->assetManager->createAssetBundle('modules','css',$basepath,$basepath.'/assets/css');
                Yii::app()->assetManager->createAssetBundle('modules','js',$basepath,$basepath.'/assets/js');
            }
        }
    }
    /**
     * Set module dependecies, currently support modules and views , classes, images
     * For external module (outside own application), include full path alias. 
     * For internal module (within own application), use application.modules.* or shortcut - same as module name
     * 
     * Setup in following way:
     * <pre>
     * array(
     *     'modules'=>array(
     *         'module_name_0'=>array(
     *                            'module_path_alias_0_1',
     *                            'module_path_alias_0_2',
     *                            'module_path_alias_0_3',
     *                            '...',
     *                          ),
     *         'module_name_1'=>array(
     *                            'module_path_alias_1_1',
     *                            'module_path_alias_1_2',
     *                            '...',
     *                          ),
     *         ...
     *     ),
     *     'views'=>array(
     *         'view_name_0'=>'view_path_alias_0',
     *         'view_name_1'=>'view_path_alias_1',
     *         ...
     *     ),
     * ));
     * </pre> 
     * Example:  
     * 'payments' as external module, 'shop' as internal module
     * <pre>
     * array(
     *     'modules'=>array(
     *         'payments'=>array(
     *                      'common.modules.payments.components.*',
     *                      'common.modules.payments.models.PaymentForm',
     *                       ),
     *         'shops'=>'shops.components.*',//for local module within own application, use shortcut - same as module name
     *        ),
     * ));
     * </pre>
     * @property array 
     */
    public function setDependencies($dependencies)
    {
        $this->_d = $dependencies;
        foreach ($this->_d as $key => $dependency) {
            if ($key=='modules')
               foreach ($dependency as $module => $modulePathAliases)
                    if (Yii::app()->hasModule($module))
                        $this->setImport($modulePathAliases);
                    else
                        logWarning(__METHOD__.' '.$module.' not found',[],false);
        }            
    }
    
    public function getDependencySii()
    {
        if (array_key_exists('sii', $this->_d) && $this->mergeSii)
            return $this->_d['sii'];   
        else
            return [];
    }
    
    public function getDependencyModules()
    {
        if (array_key_exists('modules', $this->_d))
            return array_keys($this->_d['modules']);           
        throw new CException('ModuleMap key not exists');
    }
    
    public function findMissingModules()
    {
        $missingModules = new CList();
        foreach ($this->getDependencyModules() as $module) {
            if (!Yii::app()->hasModule($module)) 
                $missingModules->add('Module "'.$module.'" is not installed.');
        }       
        return $missingModules;
    }
    public function getDependecy($id)
    {
        if (array_key_exists($id, $this->_d))
            return $this->_d[$id];           
        throw new CException($id.' identifier not exists');
    }
    /**
     * Return view from ViewMap. It supports single or cross-module view map referencing.
     * ViewMap $key can be in form of [module].[view] or simple key 
     * ViewMap $value can be in form of other module ViewMap $key also, or simple view name 
     * 
     * Example:
     * ---------------------------------------------------------
     * Module X has following ViewMap
     * <pre>
     *   'views'=>array(
     *       'key1'=>'common.modules.A.views.demo._view',
     *       'key2'=>'common.modules.B.views.demo._test',
     *       'key3'=>'moduleY.key5',
     *       'key4'=>'moduleY.key6',
     *   ),
     * </pre>
     * Module Y has following ViewMap
     * <pre>
     *   'views'=>array(
     *       'key5'=>'common.modules.C.views.demo._view',
     *       'key6'=>'common.modules.D.views.demo._test',
     *   ),
     * </pre>
     * ---------------------------------------------------------
     * ControllerX at Module X can access Module Y view map directly via following remote call:
     * $controllerX->renderView('key3') = 'common.modules.C.views.demo._view';
     * 
     * And, ControllerY can access Module X view map indirectly via following local call:
     * $controllerY->renderView('moduleX.key1') = 'common.modules.A.views.demo._view';
     * 
     * @return string ViewMap view name
     */    
    public function getView($key)
    {
        $token = explode('.', $key);
        if (array_key_exists(1, $token)){//format: [module].[viewname]
            $owner = $token[0];
            $key = $token[1];
            $module = Yii::app()->getModule($owner);
        }
        else {
            $key = $token[0];
            $module = $this;
        }
        
        $viewMap = $module->getDependecy('views');         
        if (array_key_exists($key, $viewMap)) {
            //check again if the view has module prefix
            $token = explode('.', $viewMap[$key]);
            if (array_key_exists(1, $token)){//format: [module].[viewname]
                $owner = $token[0];
                if (Yii::app()->hasModule($owner)){
                    $module = Yii::app()->getModule($owner);
                    //logTrace(__METHOD__.' cross-module "'.$viewMap[$key].'" '.$module->getView($viewMap[$key]));
                    return $module->getView($viewMap[$key]);
                }
                else{
                    //logTrace(__METHOD__.' via viewMap '.$viewMap[$key]);
                    return $viewMap[$key];//direct viewMap
                }
            }
            else {//direct viewMap
                //logTrace(__METHOD__.' '.$viewMap[$key]);
                return $viewMap[$key];
            }
        }
        throw new CException('ViewMap key not exists');
    }
    public function getClass($key)
    {
        $classMap = $this->getDependecy('classes');         
        if (array_key_exists($key, $classMap))
            return $classMap[$key];           
        throw new CException('ClassMap key not exists');
    }
    public function getImage($key)
    {
        $imageMap = $this->getDependecy('images');  
        if (array_key_exists($key, $imageMap)){
            $image = $imageMap[$key];
            $pathAlias = array_keys($image);//return type is array
            $filename = array_values($image);//return type is array
            $assetsPath = Yii::getPathOfAlias($pathAlias[0]);
            return Yii::app()->assetManager->publish($assetsPath).'/'.$filename[0];
        }
        throw new CException('ImageMap key not exists');
    }
    
    public function runControllerMethod($controller,$method,$param1=null,$param2=null)
    {        
        $_c = Yii::app()->controller;
        if (Yii::app()->controller->uniqueId!=$controller){
            $res = Yii::app()->createController($controller);
            $_c = $res[0];
            logTrace(__METHOD__.' create controller '.$_c->uniqueId);
        }
        logTrace(__METHOD__.' '.$_c->uniqueId.'->'.$method.'()');
        return $_c->{$method}($param1,$param2);
    }

    public function beforeControllerAction($controller, $action)
    {
        if(parent::beforeControllerAction($controller, $action))
        {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        }
        else
            return false;
    }
    /**
     * Module display name
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Module|Modules',[$mode]);
    }
    /**
     * Returns the module version.
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }    
    
}
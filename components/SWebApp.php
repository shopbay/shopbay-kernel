<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SWebApp
 *
 * @author kwlok
 */
class SWebApp extends CComponent
{
    /*
     * If true, system trace will be logged
     */
    public $enableSystemTrace = false;
    /*
     * If true, system trace will be logged
     */
    public $serviceRunMode = 'local';//either local or api
    /*
     * Private property
     */
    private $_id;
    private $_basePath;//base path of app
    private $_params;
    private $_dependencies;
    private $_allComponents = [];
    private $_myComponents = [];
    private $_commonComponents = [];
    private $_import = [];
    /*
     * Constructor
     */
    public function __construct($id,$basePath) 
    {
        $this->_id = $id;
        $this->_basePath = $basePath;
        //Load common params
        $this->_params = require(KERNEL.'/config/params.php');
        //Merge with local params
        $this->_params = array_merge(require($this->_basePath.DIRECTORY_SEPARATOR.'config/params.php'),$this->_params);
        Yii::trace(__METHOD__.' start webapp "'.$this->_id.'" params >> '.var_export($this->_params,true));        
        //Load dependencies (set aliases and import modules)
        $this->_dependencies = require_once($this->_basePath.DIRECTORY_SEPARATOR.'config/dependencies.php');
    }
    /**
     * Get web application id
     * @return array 
     */
    public function getId()
    {
        return $this->_id;
    }
    /**
     * Get web application basepath
     * @return array 
     */
    public function getBasepath()
    {
        return $this->_basePath;
    }
    /**
     * Get web application config params
     * @return array 
     */
    public function getParams()
    {
        return $this->_params;
    }
    /**
     * Common classes to be imported by default
     * @param array $classes Array of class aliases
     * @return type
     */
    public function import($aliases)
    {
        $this->_import = $aliases;
    }
    /**
     * Get all components
     * @param type $components
     */
    public function getAllComponents()
    {
        $this->_allComponents = array_merge($this->commonComponents,$this->myComponents);
        return $this->_allComponents;
    }    
    /**
     * Get my components
     * @param type $components
     */
    public function getMyComponents()
    {
        return $this->_myComponents;
    }    
    /**
     * Set components on top of common/default
     * @param type $components
     */
    public function addComponents($components)
    {
        $this->_myComponents = array_merge($this->_myComponents,$components);
    }
    /**
     * Set specific common component
     * @param type $component
     */
    public function setCommonComponent($component,$config)
    {
        $this->commonComponents;//load common components
        if (isset($this->_commonComponents[$component])){
            $this->_commonComponents[$component] = array_merge($this->_commonComponents[$component], $config);
        }
    }
    /**
     * Common components to be included by default
     * @return type
     */
    public function getCommonComponents()
    {
        if (empty($this->_commonComponents)){
            $this->_commonComponents = [
                'bootstrap'=> [
                    'class' => 'common.components.SBootstrap',
                ],
                'serviceManager'=> [
                    'class' => 'common.services.ServiceManager',
                    'runMode'=>$this->serviceRunMode,
                ],
                'messages'=> [
                    'class'=> 'SMessageSource',
                ],
                'image'=> [
                    'class'=> 'SImgManager',
                    'modelClass'=> 'MediaAssociation',
                    'baseRelativePath' => $this->_id.'/www',
                    'versions'=> loadImageVersions(),
                ],
                'ctrlManager'=> [
                    'class'=> 'SControllerManager',
                ],
                'authManager'=> [
                    'class'=> 'SAuthManager', // Provides support authorization item sorting
                    'itemTable'=> 's_auth_item', 
                    'itemChildTable'=> 's_auth_itemchild', 
                    'assignmentTable'=> 's_auth_assignment', 
                    'rightsTable'=> 's_auth_rights',
                ],
                'assetManager' => [
                    'class'=> 'SAssetManager',
                   //'basePath'=> '',//unset, load default
                    'enableRev'=> true,
                ],
                'db'=> require(KERNEL.'/config/datasource.php'),
                'cache'=> [
                    'class'=>'system.caching.CFileCache',//to use CDbCache, need to have php pdo sqllite driver)
                ],
                'commonCache'=> [
                    'class'=>'common.components.SCache',
                ],
                'errorHandler'=>[
                    //use 'site/error' action to display errors
                    'errorAction'=>'site/error',
                ],
                'log'=> [
                    'class'=>'SLogRouter',
                    'autoFlush' => 2,
                    'routes'=> [
                        [
                            'class'=>'CFileLogRoute',
                            'levels'=>'error, warning',
                            //'categories'=>'',
                        ],
                        [
                            'class'=>'CFileLogRoute',
                            'levels'=>'trace, info',
                            'categories'=>'application, '.$this->_id.($this->enableSystemTrace?', system.*':''),
                           // 'categories'=>'',
                        ],
                        //uncomment the following to show profiler at the end of the web page. 
                        [
                            'class'=>'CProfileLogRoute',
                            'report'=>'summary',
                            // lists execution time of every marked code block
                            // report can also be set to callstack
                            'enabled'=>YII_DEBUG,
                        ],
                        //uncomment the following to show log messages on web pages
//                        [
//                            'class'=>'CWebLogRoute',
//                        ],
                    ],
                ]            
            ];
        }
        return $this->_commonComponents;
    }
    
    public function toArray()
    {
        return [
            'id'=> $this->_id,
            'basePath'=> $this->_basePath,
            'name'=>$this->params['SITE_NAME'],
            'theme'=>'classic',
            'language'=> $this->params['LOCALE_DEFAULT'],
            'params'=> $this->params,//application-level parameters that can be accessed using Yii::app()->params['paramName']
            //preloading 'log' component
            'preload'=>['log'],
            //autoloading model and component classes
            'import'=>$this->_import,
            //application modules
            'modules'=>loadModules($this->_dependencies),
            //application components
            'components'=>$this->allComponents,
        ];        
    }
    /**
     * Parse config param boolean value
     * @param type $param
     * @return type
     */
    public function parseBoolean($param)
    {
        return isset($this->params[$param])?$this->params[$param]:false;
    }
    /**
     * Parse config param string value
     * @param type $param
     * @return type
     */
    public function parseString($param)
    {
        return isset($this->params[$param])?$this->params[$param]:'';
    }
    /**
     * Parse config param array value
     * @param type $param
     * @return type
     */
    public function parseArray($param)
    {
        return isset($this->params[$param])?$this->params[$param]:[];
    }
}

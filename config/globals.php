<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the global settings
 */
defined('ROOT') or define('ROOT',dirname(__FILE__).'/../../');
// The shopbay-kernel path
defined('KERNEL') or define('KERNEL',dirname(dirname(__FILE__)));
// the name of kernel folder
defined('KERNEL_NAME') or define('KERNEL_NAME',  basename(KERNEL));
// the timezone app is running in
defined('APP_TIMEZONE') or define('APP_TIMEZONE','Asia/Kuala_Lumpur');
date_default_timezone_set(APP_TIMEZONE);
//ini_set("memory_limit","512M");
/**
 * This is the shortcut to Yii::app()
 */
function app()
{
    return Yii::app();
}
/**
 * This is the shortcut to Yii::app()->user.
 */
function securityManager() 
{
    return Yii::app()->getSecurityManager();
}
/**
 * This is the shortcut to Yii::app()->getRequest()
 */
function request()
{
    return Yii::app()->getRequest();
} 
/**
 * This is the shortcut to Yii::app()->clientScript
 */
function cs()
{
    // You could also call the client script instance via Yii::app()->clientScript
    // But this is faster
    return Yii::app()->getClientScript();
}
/**
 * This is the shortcut to Yii::app()->getAssetManager()
 */
function asset()
{
    return Yii::app()->getAssetManager();
}
/**
 * This is the shortcut to Yii::app()->user.
 */
function user() 
{
    try {
        return Yii::app()->getUser();
    } catch (Exception $e) {
        Yii::log('no user object: '.$e->getMessage(), CLogger::LEVEL_WARNING);
        return null;
    }
}
function uid()
{
    return user()!=null?user()->getId():'NO_UID';
}
/**
 * This is the shortcut to Yii::app()->controller if $route is null
 * This is the shortcut to Yii::app()->runController() if $route is not null
 */
function controller($route=null)
{
    if ($route==null)
        return Yii::app()->controller;
    else
        return Yii::app()->runController($route);
}
/**
 * This is the shortcut to Yii::app()->user->returnUrl.
 */
function returnUrl() 
{
    return Yii::app()->getUser()->returnUrl;
}
/**
 * This is the shortcut to Yii::app()->createUrl()
 */
function url($route,$params=[],$ampersand='&')
{
    $url = Yii::app()->createUrl($route,$params,$ampersand);
    return request()->isSecureConnection ? str_replace('http://', 'https://', $url) : $url;
}
/**
 * Secure url
 * @todo Should base on a 'ssl' flag. If on, use secure connection
 */
function surl($route,$params=[],$ampersand='&')
{
    return str_replace('http://', 'https://', Yii::app()->createUrl($route,$params,$ampersand));
}
/**
 * This is the shortcut to Yii::app()->urlManager->createHostUrl()
 */
function hostUrl($route=null,$forceSecure=false)
{
    return Yii::app()->urlManager->createHostUrl($route,$forceSecure);
}
/**
 * This is the shortcut to Yii::app()->urlManager->createCommunityUrl()
 */
function communityUrl($route=null,$forceSecure=false)
{
    return Yii::app()->urlManager->createCommunityUrl($route,$forceSecure);
}
/**
 * This is the shortcut to Yii::app()->createUrl()
 */
function imageUrl($path)
{
    return Yii::app()->createAbsoluteUrl('/'.$path);
} 
/**
 * This is the shortcut to CHtml::encode
 */
function h($text)
{
    return htmlspecialchars($text,ENT_QUOTES,Yii::app()->charset);
}
/**
 * This is the shortcut to Sii::t()
 */
function t($message,$category='sii')
{
    return Sii::t($category,$message);
} 
/**
 * This is the shortcut to CHtml::link()
 */
function l($text, $url = '#', $htmlOptions = []) 
{
    return CHtml::link($text, $url, $htmlOptions);
} 
/**
 * This is the shortcut to Yii::app()->request->baseUrl
 * If the parameter is given, it will be returned and prefixed with the app baseUrl.
 */
function bu($url=null) 
{
    static $baseUrl;
    if ($baseUrl===null)
        $baseUrl=Yii::app()->getRequest()->getBaseUrl();
    return $url===null ? $baseUrl : $baseUrl.'/'.ltrim($url,'/');
}
 
/**
 * Returns the named application parameter.
 * This is the shortcut to Yii::app()->params[$name].
 */
function param($name) 
{
    return Yii::app()->params[$name];
}

/**
 * Shortcut to dump the target with syntax highlighting on by default:
 * @param type $target
 * @return type 
 */
function dump($target)
{
  return CVarDumper::dump($target, 10, true) ;
}
/**
 * Shortcut to return unix timestamp based on date format (by locale)
 * @param type $target
 * @return type 
 */
function parseDatetime($date,$defaults=[])
{
  if ($date===null)
    return 'not set';
  //return CDateTimeParser::parse($date, CLocale::getInstance(user()->getLocale())->getDateFormat('short'));
  return CDateTimeParser::parse($date, param('DATETIME_FORMAT'),$defaults);
}
function parseDate($date,$defaults=[])
{
  if ($date===null)
    return 'not set';
  return CDateTimeParser::parse($date, param('DATE_FORMAT'),$defaults);
}

function alias($alias)
{
    return Yii::getPathOfAlias($alias);
}
function logHttpHeader()
{
    Yii::log('*** start web request *** >> '.var_export($_SERVER,true), CLogger::LEVEL_INFO);
}
function logHttpRequest($level,$category='application')
{
   if ($level==CLogger::LEVEL_TRACE)
       Yii::trace('$_REQUEST >> '.var_export($_REQUEST,true), $category);
   else
       Yii::log('$_REQUEST >> '.var_export($_REQUEST,true), $level, $category);
}
function logError($msg,$params=[],$httpRequest=true)
{
    if (isYii2()){
        Yii::error($msg.(!empty($params)?' >> '.\yii\helpers\VarDumper::dumpAsString($params):''),Yii::app()->id);        
    }
    else {
        Yii::log('[user='.uid().'] '.$msg.(!empty($params)?' >> '.var_export($params,true):''),  CLogger::LEVEL_ERROR, Yii::app()->id);
        if ($httpRequest==true)
            logHttpRequest(CLogger::LEVEL_ERROR,Yii::app()->id);
    }
}
function logWarning($msg,$params=[],$httpRequest=true)
{
    if (isYii2()){
        Yii::warning($msg.(!empty($params)?' >> '.\yii\helpers\VarDumper::dumpAsString($params):''),Yii::app()->id);        
    }
    else {
        Yii::log('[user='.uid().'] '.$msg.(!empty($params)?' -> '.var_export($params,true):''),  CLogger::LEVEL_WARNING, Yii::app()->id);
        if ($httpRequest==true)
            logHttpRequest(CLogger::LEVEL_WARNING, Yii::app()->id);
    }
}
function logInfo($msg,$params=[],$httpRequest=false)
{
    if (isYii2()){
        Yii::info($msg.(!empty($params)?' >> '.\yii\helpers\VarDumper::dumpAsString($params):''),Yii::app()->id);        
    }
    else {
        Yii::log('[user='.uid().'] '.$msg.(!empty($params)?' >> '.var_export($params,true):''),  CLogger::LEVEL_INFO, Yii::app()->id);
        if ($httpRequest==true)
            logHttpRequest(CLogger::LEVEL_INFO, Yii::app()->id);
    }
}
function logTrace($msg,$params=[],$httpRequest=false)
{
    if (isYii2()){
        Yii::trace($msg.(!empty($params)?' >> '.\yii\helpers\VarDumper::dumpAsString($params):''),Yii::app()->id);        
    }
    else {
        Yii::trace('[userid='.uid().'] '.$msg.(!empty($params)?' >> '.var_export($params,true):''), Yii::app()->id);
        if ($httpRequest==true)
            logHttpRequest(CLogger::LEVEL_TRACE, Yii::app()->id);       
    }
}
function logTraceDump($msg,$params=[],$httpRequest=false)
{
    Yii::trace('[userid='.uid().'] '.$msg.(!empty($params)?' >> '.print_r($params,true):''), Yii::app()->id);
    if ($httpRequest==true)
        logHttpRequest(CLogger::LEVEL_TRACE, Yii::app()->id);       
}
function logErrorDump($msg,$params=[],$httpRequest=false)
{
    Yii::error('[userid='.uid().'] '.$msg.(!empty($params)?' >> '.print_r($params,true):''), Yii::app()->id);
    if ($httpRequest==true)
        logHttpRequest(CLogger::LEVEL_ERROR, Yii::app()->id);       
}
function throwError400($message) 
{
    logError('400 Bad Request');
    throw new CHttpException(400,$message);
}
function throwError403($message) 
{
    logError('403 Unauthorized access');
    throw new CHttpException(403,$message);
}
function throwError404($message) 
{
    logError('404 The requested page does not exist');
    throw new CHttpException(404,$message);
}
function throwError500($message)
{
    logError('500 Internal Server Error');
    throw new CHttpException(500,$message);
}
/**
 * Load dependencies.php to set alias and import classes
 * @param type $root
 * @param type $depends dependencies data
 * @param type $appPath the app path 
 */
function loadDependencies($root,$depends,$appPath)
{
    Yii::trace('*** start loading dependencies ***');
    
    foreach ($depends as $key => $value) {
        if ($key=='base'){
            foreach ($value as $base => $folder) {
                setAlias($base, $root.DIRECTORY_SEPARATOR.$folder);
            }
        }
        if ($key=='module'){
            foreach ($value as $module => $setting) {
                if ($module!='common'){
                    setAlias($module, $root.DIRECTORY_SEPARATOR.KERNEL_NAME.'/modules/'.$module);
                    importClass($module.'.'.ucfirst($module).'Module');
                }
                if (array_key_exists('import', $setting)){
                    foreach ($setting['import'] as $classes)
                        importClass($module.'.'.$classes);
                }
            }
        }
        if ($key=='local'){
            foreach ($value as $module => $setting) {
                $base = substr($root, 0, strlen($root)-3);
                setAlias($module, $appPath.DIRECTORY_SEPARATOR.'modules/'.$module);
                importClass($module.'.'.ucfirst($module).'Module');
                if (array_key_exists('import', $setting)){
                    foreach ($setting['import'] as $classes)
                        importClass($module.'.'.$classes);
                }
            }
        }
    }
    Yii::trace('<< initial class map >> '.var_export(Yii::$classMap,true),'system.shopbay');//regards this as system trace       
    Yii::trace('<< dependencies settings >> '.var_export($depends,true),'system.shopbay');//regards this as system trace
}
function loadModules($depends)
{
    Yii::trace('*** start loading modules ***');    
    $modules = new CMap();
    foreach ($depends as $key => $value) {
        if ($key=='module'){
            foreach ($value as $module => $setting) {
                if ($module!='common'){
                    if (array_key_exists('config', $setting))
                        $modules->add($module,$setting['config']);
                    else
                        $modules->add($module,[]);
                }                
            }
        }
        if ($key=='local'){
            foreach ($value as $module => $setting) {
                if (array_key_exists('config', $setting))
                    $modules->add($module,$setting['config']);
                else
                    $modules->add($module,[]);
            }
        }
        if ($key=='external'){
            foreach ($value as $setting) 
                $modules->add($setting['module'],[]);
        }        
    }
    Yii::trace('<< modules settings >> '.var_export($modules->toArray(),true),'system.shopbay');//regards this as system trace
    return $modules->toArray();
}
function importClass($alias,$forceInclude=false)
{
    Yii::import($alias,$forceInclude);
    Yii::trace('importing '.$alias,'system.shopbay');//regards this as system trace
}
function setAlias($alias,$path)
{
    Yii::setPathOfAlias($alias, $path);
    Yii::trace('setAlias '.$alias.'='.alias($alias),'system.shopbay');//regards this as system trace
}
function loadImageVersions()
{
    return [
        '20'=>['width'=>20,'height'=>20,'resizeMethod'=>'adaptiveResize'],//refer to Img::METHOD_ADAPTIVE_RESIZE
        '30'=>['width'=>30,'height'=>30,'resizeMethod'=>'adaptiveResize'],//refer to Img::METHOD_ADAPTIVE_RESIZE
        '60'=>['width'=>60,'height'=>60,'resizeMethod'=>'adaptiveResize'],
        '80'=>['width'=>80,'height'=>80,'resizeMethod'=>'adaptiveResize'],
        '100'=>['width'=>100,'height'=>100,'resizeMethod'=>'adaptiveResize'],
        '120'=>['width'=>120,'height'=>120,'resizeMethod'=>'adaptiveResize'],
        '160'=>['width'=>160,'height'=>160,'resizeMethod'=>'adaptiveResize'],
        '200'=>['width'=>200,'height'=>200,'resizeMethod'=>'adaptiveResize'],
        '250'=>['width'=>250,'height'=>250,'resizeMethod'=>'adaptiveResize'],
//        '250x300'=>['width'=>250,'height'=>300,'resizeMethod'=>'adaptiveResize'],
        '300'=>['width'=>300,'height'=>300,'resizeMethod'=>'adaptiveResize'],
        '360'=>['width'=>360,'height'=>360,'resizeMethod'=>'adaptiveResize'],
        '640'=>['width'=>640,'height'=>640,'resizeMethod'=>'adaptiveResize'],
    ];
}

function bootstrapYii2Engine()
{
    importYii2Oauth2Server();
    // configuration for Yii 2 application (basic)
    $yii2Config = require(YII2ENGINE_PATH. '/config/web.php');
    new yii\web\Application($yii2Config); // Do NOT call run()
}
//This will return domain , and remove any subdomain 
//E.g. remove "www" if found - left with ".shopbay.org"
function resolveDomain($domain)
{
    $result = '.';
    $data = explode('.', $domain);//remove $data[0]
    if (isset($data[1]))
        $result .= $data[1];
    if (isset($data[2]))
        $result .= '.'.$data[2];
    return $result;
}
//return back json in array
function readJsonFile($filepath)
{
    return json_decode(file_get_contents($filepath),true);
}

/**
 * A helper function to read config.json
 * @param type $level1 Level 1 param in json file
 * @param type $level2 Level 3 param in json file
 * @param type $jsonFilePath Specific config.json file to be read; If null, use back local app config.json
 */
function readConfig($level1, $level2,$jsonFilePath=null)
{
    $configJson = $jsonFilePath!=null ? readJsonFile($jsonFilePath) : readJsonFile(APP_CONFIG);//use default calling app's config.json default
    return $configJson[$level1][$level2];
}

function readDBConfig($field)
{
    return SSecurityManager::decryptData(readConfig('database', $field));    
}

function bootstrap()
{
    return app()->bootstrap;
}

function importYii2Extension($extensionName,$classes=[],$classDir=null)
{
    //if there is class folder to store classes
    if ($classDir!=null)
        $classDir .= '/';
        
    foreach ($classes as $class) {
        Yii::setPathOfAlias('yii.'.$extensionName.'.'.$class, YII2_FRAMEWORK_PATH . '/../yii2-'.$extensionName.'/'.$classDir.$class);
        Yii::import('yii.'.$extensionName.'.'.$class);
    }        
}

function importYii2Oauth2Server()
{
    $yii2Basepath = readConfig('system','yii2Path');
    $classes = ['Bootstrap'];
    foreach ($classes as $class) {
        Yii::setPathOfAlias('filsh.yii2.oauth2server.'.$class, $yii2Basepath . '/vendor/filsh/yii2-oauth2-server/'.$class);
        Yii::import('filsh.yii2.oauth2server.'.$class);
    }        
}    

function isYii2()
{
    return substr(Yii::getVersion(),0,1)=='2';
}

function isYii1()
{
    return !isYii2();
}

function setYiiEnvironment()
{
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',readConfig('system','yiiTraceLevel'));//specify how many levels of call stack should be shown in each log message
    defined('YII_DEBUG') or define('YII_DEBUG',readConfig('system','yiiDebug'));//set to false when in production mode
    defined('YII1_FRAMEWORK_PATH') or define('YII1_FRAMEWORK_PATH',readConfig('system','yii1Path').'/framework');
    defined('YII2_FRAMEWORK_PATH') or define('YII2_FRAMEWORK_PATH',readConfig('system','yii2Path').'/vendor/yiisoft/yii2');
    defined('YII2ENGINE_PATH') or define('YII2ENGINE_PATH',KERNEL.'/yii2engine');    
    defined('YII_ENV') or define('YII_ENV', readConfig('system','yiiEnv'));//this is Yii2 env variable
    require(YII1_FRAMEWORK_PATH . '/YiiBase.php'); // Yii 1.x
    require(YII2_FRAMEWORK_PATH . '/BaseYii.php'); // Yii 2.x
}
/**
 * Default cross subdomain cookie settings
 * @param type $httpOnly
 * @param type $path
 * @return array
 */
function cookieSharingSettings($httpOnly=false,$path='/')
{
    return [
        'domain' => resolveDomain(param('HOST_DOMAIN')),//expect starts with e.g. ".myapp.com"
        'path' => $path,
        'httpOnly' => $httpOnly,
    ];
}
/**
 * Check if user is on shop scope
 * @see CustomerUser::onShopScope
 * @return boolean
 */
function userOnScope($scope)
{
    $method = 'on'.ucfirst($scope).'Scope';
    if (method_exists(user(), $method))
        return user()->{$method}();
    else
        return false;
}

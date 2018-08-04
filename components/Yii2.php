<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

//load customized Yii class (enable both Yii 1.x and Yii 2.x)
setYiiEnvironment();
/**
 * Yii is a helper class serving common framework functionalities.
 * This is a customized Yii class to combine both Yii1 (partial) and Yii2 so that both can run in same environment.
 * Note: YiiBase is the base yii class of Yii1, while BaseYii is the base yii class of Yii2.
 * We put symbol '@see YiiBase' to indicate codes that are directly copied from YiiBase.
 * 
 * //----------------
 * //NOTE: copy Yii 1.x YiiBase.php partial methods here:
 * //----------------/
 *   YiiBase::app()
 *   YiiBase::setApplication($app)
 *   YiiBase::createApplication($class,$config=null)
 *   YiiBase::createComponent($config)
 *   YiiBase::setPathOfAlias($alias,$path)
 *   YiiBase::import($alias,$forceInclude=false)
 *   YiiBase::getPathOfAlias($alias)
 *   YiiBase::log($msg,$level=CLogger::LEVEL_INFO,$category='application')
 *   YiiBase::t($category,$message,$params=array(),$source=null,$language=null)
 *     -> note that here t() is renamed to t1 as BaseYii has also t() with incompatible method signature
 *  
 * @author kwlok
 */
class Yii extends \yii\BaseYii
{
    /**
     * @see YiiBase
     */
    public static $enableIncludePath=true;
    private static $_aliases=array('system'=>YII_PATH,'zii'=>YII_ZII_PATH); // alias => path
    private static $_imports=array();					// alias => class name or directory
    private static $_includePaths;						// list of include paths
    private static $_app;
    private static $_logger;
        
    /**
     * @see YiiBase:app()
     */
    public static function app()
    {
        return self::$_app;
    }
    /**
     * @see YiiBase:setApplication($app)
     */
    public static function setApplication($app)
    {
        if(self::$_app===null || $app===null)
                self::$_app=$app;
        else
                throw new CException(Yii::t('yii','Yii application can only be created once.'));
    }  
    /**
     * @see YiiBase:createApplication($class,$config=null)
     */
    public static function createApplication($class,$config=null)
    {
            return new $class($config);
    }
    /**
     * @see YiiBase:createComponent($config)
     */
    public static function createComponent($config)
    {
        if(is_string($config))
        {
                $type=$config;
                $config=array();
        }
        elseif(isset($config['class']))
        {
                $type=$config['class'];
                unset($config['class']);
        }
        else
                throw new CException(Yii::t('yii','Object configuration must be an array containing a "class" element.'));

        if(!class_exists($type,false))
                $type=Yii::import($type,true);

        if(($n=func_num_args())>1)
        {
                $args=func_get_args();
                if($n===2)
                        $object=new $type($args[1]);
                elseif($n===3)
                        $object=new $type($args[1],$args[2]);
                elseif($n===4)
                        $object=new $type($args[1],$args[2],$args[3]);
                else
                {
                        unset($args[0]);
                        $class=new ReflectionClass($type);
                        // Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
                        // $object=$class->newInstanceArgs($args);
                        $object=call_user_func_array(array($class,'newInstance'),$args);
                }
        }
        else
                $object=new $type;

        foreach($config as $key=>$value)
                $object->$key=$value;

        return $object;
    }    
    /**
     * @see YiiBase:setPathOfAlias($alias,$path)
     */
    public static function setPathOfAlias($alias,$path)
    {
        if(empty($path))
                unset(self::$_aliases[$alias]);
        else
                self::$_aliases[$alias]=rtrim($path,'\\/');
    }    
    /**
     * @see YiiBase:import($alias,$forceInclude=false)
     */
    public static function import($alias,$forceInclude=false)
    {
        if(isset(self::$_imports[$alias]))  // previously imported
                return self::$_imports[$alias];

        if(class_exists($alias,false) || interface_exists($alias,false))
                return self::$_imports[$alias]=$alias;

        if(($pos=strrpos($alias,'\\'))!==false) // a class name in PHP 5.3 namespace format
        {
                $namespace=str_replace('\\','.',ltrim(substr($alias,0,$pos),'\\'));
                if(($path=self::getPathOfAlias($namespace))!==false)
                {
                        $classFile=$path.DIRECTORY_SEPARATOR.substr($alias,$pos+1).'.php';
                        if($forceInclude)
                        {
                                if(is_file($classFile))
                                        require($classFile);
                                else
                                        throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing PHP file and the file is readable.',array('{alias}'=>$alias)));
                                self::$_imports[$alias]=$alias;
                        }
                        else
                                self::$classMap[$alias]=$classFile;
                        return $alias;
                }
                else
                {
                        // try to autoload the class with an autoloader
                        if (class_exists($alias,true))
                                return self::$_imports[$alias]=$alias;
                        else
                                throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
                                        array('{alias}'=>$namespace)));
                }
        }

        if(($pos=strrpos($alias,'.'))===false)  // a simple class name
        {
                // try to autoload the class with an autoloader if $forceInclude is true
                if($forceInclude && (Yii::autoload($alias,true) || class_exists($alias,true)))
                        self::$_imports[$alias]=$alias;
                return $alias;
        }

        $className=(string)substr($alias,$pos+1);
        $isClass=$className!=='*';

        if($isClass && (class_exists($className,false) || interface_exists($className,false)))
                return self::$_imports[$alias]=$className;

        if(($path=self::getPathOfAlias($alias))!==false)
        {
                if($isClass)
                {
                        if($forceInclude)
                        {
                                if(is_file($path.'.php'))
                                        require($path.'.php');
                                else
                                        throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing PHP file and the file is readable.',array('{alias}'=>$alias)));
                                self::$_imports[$alias]=$className;
                        }
                        else
                                self::$classMap[$className]=$path.'.php';
                        return $className;
                }
                else  // a directory
                {
                        if(self::$_includePaths===null)
                        {
                                self::$_includePaths=array_unique(explode(PATH_SEPARATOR,get_include_path()));
                                if(($pos=array_search('.',self::$_includePaths,true))!==false)
                                        unset(self::$_includePaths[$pos]);
                        }

                        array_unshift(self::$_includePaths,$path);

                        if(self::$enableIncludePath && set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR,self::$_includePaths))===false)
                                self::$enableIncludePath=false;

                        return self::$_imports[$alias]=$path;
                }
        }
        else
                throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
                        array('{alias}'=>$alias)));
    }
    /**
     * @see YiiBase:getPathOfAlias($alias)
     */
    public static function getPathOfAlias($alias)
    {
        if(isset(self::$_aliases[$alias]))
                return self::$_aliases[$alias];
        elseif(($pos=strpos($alias,'.'))!==false)
        {
                $rootAlias=substr($alias,0,$pos);
                if(isset(self::$_aliases[$rootAlias]))
                        return self::$_aliases[$alias]=rtrim(self::$_aliases[$rootAlias].DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($alias,$pos+1)),'*'.DIRECTORY_SEPARATOR);
                elseif(self::$_app instanceof CWebApplication)
                {
                        if(self::$_app->findModule($rootAlias)!==null)
                                return self::getPathOfAlias($alias);
                }
        }
        return false;
    }
    /**
     * @see YiiBase:log($msg,$level=CLogger::LEVEL_INFO,$category='application')
     */
    public static function log($msg,$level=CLogger::LEVEL_INFO,$category='application')
    {
        if(self::$_logger===null)
                self::$_logger=new CLogger;
        if(YII_DEBUG && YII_TRACE_LEVEL>0 && $level!==CLogger::LEVEL_PROFILE)
        {
                $traces=debug_backtrace();
                $count=0;
                foreach($traces as $trace)
                {
                        if(isset($trace['file'],$trace['line']) && strpos($trace['file'],YII_PATH)!==0)
                        {
                                $msg.="\nin ".$trace['file'].' ('.$trace['line'].')';
                                if(++$count>=YII_TRACE_LEVEL)
                                        break;
                        }
                }
        }
        self::$_logger->log($msg,$level,$category);
    }    
    /**
     * Both YiiBase and BaseYii have same t() function name for translation.
     * Here, we rename the Yiibase::t() to YiiBase:t1() to avoid name clash.
     * @see YiiBase:t($category,$message,$params=array(),$source=null,$language=null)
     */
    public static function t1($category,$message,$params=array(),$source=null,$language=null)
    {
        if(self::$_app!==null)
        {
            if($source===null)
                    $source=($category==='yii'||$category==='zii')?'coreMessages':'messages';
            if(($source=self::$_app->getComponent($source))!==null)
                    $message=$source->translate($category,$message,$language);
        }
        if($params===array())
                return $message;
        if(!is_array($params))
                $params=array($params);
        if(isset($params[0])) // number choice
        {
            if(strpos($message,'|')!==false)
            {
                    if(strpos($message,'#')===false)
                    {
                            $chunks=explode('|',$message);
                            $expressions=self::$_app->getLocale($language)->getPluralRules();
                            if($n=min(count($chunks),count($expressions)))
                            {
                                    for($i=0;$i<$n;$i++)
                                            $chunks[$i]=$expressions[$i].'#'.$chunks[$i];

                                    $message=implode('|',$chunks);
                            }
                    }
                    $message=CChoiceFormat::format($message,$params[0]);
            }
            if(!isset($params['{n}']))
                    $params['{n}']=$params[0];
            unset($params[0]);
        }
        return $params!==array() ? strtr($message,$params) : $message;
    }       
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require(YII2_FRAMEWORK_PATH.'/classes.php');
Yii::$container = new yii\di\Container();

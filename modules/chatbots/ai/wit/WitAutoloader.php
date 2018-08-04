<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Autoloads Wit SDK classes
 *
 * @author    kwlok
 */
class WitAutoloader
{
    private $baseDir;

    public function __construct()
    {
        $yii2Basepath = readConfig('system','yii2Path');
        $this->baseDir = $yii2Basepath.'/vendor';
    }
    /**
     * Registers WitAutoloader as an SPL autoloader.
     */
    public static function register($dir = null)
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self($dir), 'autoload'));
    }
    /**
     * Handles autoloading of classes.
     * @param string $class A class name.
     * @return boolean Returns true if the class has been loaded
     */
    public function autoload($class)
    {
        $temp = str_replace('\\', '/', $class);
        
        if (strpos($temp, 'Tgallice/Wit/')!==false){
            $classFile = substr($temp, strlen('Tgallice/Wit/')).'.php';
            $path = $this->baseDir.'/tgallice/wit-php/src/'.$classFile;
        }
        elseif (strpos($temp, 'GuzzleHttp/Promise/')!==false){
            $classFile = substr($temp, strlen('GuzzleHttp/Promise/')).'.php';
            $path = $this->baseDir.'/guzzlehttp/promises/src/'.$classFile;
        }
        elseif (strpos($temp, 'GuzzleHttp/Psr7/')!==false){
            $classFile = substr($temp, strlen('GuzzleHttp/Psr7/')).'.php';
            $path = $this->baseDir.'/guzzlehttp/psr7/src/'.$classFile;
        }
        elseif (strpos($temp, 'GuzzleHttp/')!==false){
            $classFile = substr($temp, strlen('GuzzleHttp/')).'.php';
            $path = $this->baseDir.'/guzzlehttp/guzzle/src/'.$classFile;
            $this->autoloadFiles();//Guzzle entry point
        }
        elseif (strpos($temp, 'Psr/Http/Message/')!==false) {
            $classFile = substr($temp, strlen('Psr/Http/Message/')).'.php';
            $path = $this->baseDir.'/psr/http-message/src/'.$classFile;
        }
        else
            return ;//nothing
        
        //logTrace(__METHOD__.' file...',$path);
        if (file_exists($file = $path)) {
            require $file;
        }
    }
    /**
     * Manual Auto load files
     * If composer fails to load (as Witloader class loading is manual and not using composer)
     * @see vendor/composer/autoload_files.php
     */
    protected function autoloadFiles()
    {
        require_once $this->baseDir.'/guzzlehttp/guzzle/src/functions_include.php';
        require_once $this->baseDir.'/guzzlehttp/promises/src/functions_include.php';
        require_once $this->baseDir.'/guzzlehttp/psr7/src/functions_include.php';
    }
}

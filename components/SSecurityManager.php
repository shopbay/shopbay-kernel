<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SSecurityManager
 *
 * @author kwlok
 */
class SSecurityManager extends CSecurityManager 
{
    /**
     * OVERRIDEN METHOD
     * Encryption Key note: For yii-1.1.16, encryption key length must be either 16,24,32 
     * @return string
     */
    public function getEncryptionKey() 
    {
        $key = readJsonFile(Yii::getPathOfAlias('common.config').DIRECTORY_SEPARATOR.'.key');
        return $key[Yii::getVersion()];
    }
    
    public static function getInstance()
    {
        $manager = Yii::createComponent(['class'=>'SSecurityManager']);
        $manager->init();
        return $manager;
    }
    
    public static function encryptData($data) 
    {
        $manager = self::getInstance();
        return base64_encode($manager->encrypt($data,$manager->getEncryptionKey()));  
    }    
    /**
     * Decrypt data
     * But Turning errors into exceptions
     * To prevent error caused by mcrypt_generic_init() when getting issues running mcrypt
     * Default it seems error is not thrown caused by error mcrypt_generic_init() etc
     */
    public static function decryptData($data) 
    {
        $manager = self::getInstance();
        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try {
            return $manager->decrypt(base64_decode($data),$manager->getEncryptionKey());  
            
        } catch (Exception $ex) {
            logError(__METHOD__.' Error! '.$ex->getMessage(),$ex->getTraceAsString());
            return $ex->getMessage();
        }
    }
}

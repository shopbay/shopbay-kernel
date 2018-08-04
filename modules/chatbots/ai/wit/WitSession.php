<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WitSession
 *
 * @author kwlok
 */
class WitSession extends CComponent 
{
    /**
     * The chatbot client id at host
     * @var type 
     */
    public $client;
    /**
     * The chatbot provider app id - as proxy between user and Wit
     * @var type 
     */
    public $app;
    /**
     * The user (speaks to Wit)
     * @var type 
     */
    public $user;
    /**
     * The property to exclude digest; Default to $client;
     * $client cannot be digested as its rollback (method encryptField()) does not work (since it contains alphanumeric chars, a hash string)
     * @var type 
     */
    protected static $fieldsExcludeDigest = ['client'];
    /**
     * Constructor
     * @param array $data
     */
    public function __construct($data=[],$digest=true) 
    {
        if (!empty($data))
            foreach ($data as $key => $value) {
                if ($digest && !in_array(Helper::camelCase($key),self::$fieldsExcludeDigest))
                    $this->{Helper::camelCase($key)} = $this->encryptField($value);
                else
                    $this->{Helper::camelCase($key)} = $value;
            }
    }
    /**
     * Get data in array form
     * @return array
     */
    public function toArray()
    {
        return [
            'user'=>$this->user,
            'app'=>$this->app,
            'client'=>$this->client,
        ];
    }
    /**
     * Get data in string
     * @return array
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }
    /**
     * A simple way to encrypt a string
     * @param type $value
     * @return type
     */
    protected function encryptField($value)
    {
        $tr = strtr($value, '1234567890', 'YPmZkqSbAO');//any random pattern
        return base64_encode($tr);
    }
    /**
     * Decrypt string
     * @param type $value
     * @return type
     */
    public function decryptField($value)
    {
        $tr = base64_decode($value);
        return strtr($tr, 'YPmZkqSbAO','1234567890');//any random pattern
    }    
    /**
     * Decode session
     * @param $encodedSession Already containing digested values
     * @param $digest If to digest values
     * @return WitSession
     */
    public static function decode($encodedSession,$digest=true)
    {
        $session = new WitSession(json_decode($encodedSession,true),false);
        if (!$digest){
            foreach (array_keys($session->toArray()) as $field) {
                if (!in_array($field,self::$fieldsExcludeDigest))
                    $session->$field = $session->decryptField($session->$field);
            }
        }
        return $session;
    }    
}

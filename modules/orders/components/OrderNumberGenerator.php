<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of OrderNumberGenerator
 * The default template is {prefix}{separator}{randomstring}{separator}{checksum}
 * 
 * Prefix: A prefix for the order number
 * Station(optional): 
 * RandomString: A random string to prevent guessable order numbers
 * Checksum: A additional measure to support concurrent PO having same time up to seconds, but diff in microseconds.
 *           also, includes the station that generated the number, example shop id
 * @author kwlok
 */
class OrderNumberGenerator extends CComponent
{
    CONST ALPHABETS    = 'a';
    CONST ALPHANUMERIC = 'an';
    CONST DATETIME     = 'dt';
    CONST COUNTER      = 'c';//a sequence number
    CONST PREFIX_PO    = 'PO';//default prefix for purchase order 
    CONST PREFIX_SO    = 'SO';//default prefix for shipping order 
    CONST TEMPLATE     = '{prefix}{separator}{randomstring}{separator}{checksum}';//default template
    CONST SEPARATOR_DASH = '-';//todo separator must be url safe? as it can be url params e.g. "/"? " " (space)?
    public static $datetimeFormat = 'YmdHis';
    public static $randomStringLength = 6;//default length
    
    public $separator = null;
    public $prefix = null;
    public $template = null;
    private $_o;//owner the number is generated for 
    /**
     * Constructor
     */
    public function __construct($owner) 
    {
        $this->_o = $owner;
    }
    /**
     * @return string Get generator owner
     */
    public function getOwner()
    {
        return $this->_o;
    }
    /**
     * @return string get generator attribute
     */
    public function getAttribute($attribute)
    {
        if ($this->$attribute!=null)
            return $this->$attribute;
        else
            return $this->owner->{'orderNum'.ucfirst($attribute)}();
    }
    /**
     * Generate the number
     * @return string
     */
    public function generate($fashion=null)
    {
        if (!isset($fashion))
            $fashion = $this->owner->orderNumRandomStringFashion();
        
        $num = Sii::t('sii',$this->getAttribute('template'),[
            '{prefix}'=>$this->getAttribute('prefix'),
            '{separator}'=>$this->getAttribute('separator'),
            '{randomstring}'=>$this->getRandomString($fashion),
            '{checksum}'=>$this->station.$this->checksum,//combining with station
        ]);
        return $num;
    }
    /**
     * A up to 2 char length shop code (to represent the station generate the order number)
     * @return type
     */
    protected function getStation()
    {
        $seed = rand(0,9);
        if (isset($this->owner->shop_id))
            $seed += $this->owner->shop_id;
        return strtoupper(base_convert($this->getChecksum((string)$seed, 2),10,36));
    }
    /**
     * Generate a random string
     * @return string
     */
    protected function getRandomString($fashion,$length=null) 
    {
        if (!isset($length))
            $length = OrderNumberGenerator::$randomStringLength;
        
        if ($fashion==self::DATETIME){
            $now = new DateTime('now');
            return $now->format(self::$datetimeFormat);
        }
        elseif ($fashion==self::COUNTER){
            return self::formatCounter($this->getCounter(),$length);
        }
        else {
            if ($fashion==self::ALPHANUMERIC)
                $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";//A string of all possible characters to select from
            else //default is ALPHABETS
                $keyspace = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                
            return strtoupper(substr(str_shuffle($keyspace), 0, $length));
        }
    }
    /**
     * A sequential running number
     * @return type
     */
    protected function getCounter()
    {
        $counter = 1;//default to 1
        if (isset($this->owner->shop_id)){//make sure has shop to retrieve order counter
            $currentCounter = $this->owner->orderNumCounter();
            if (isset($currentCounter))//if got existing counter
                $counter = $currentCounter + $counter;//increment by 1
            $this->owner->updateOrderNumCounter($counter);
        }
        return $counter;
    }    
    /**
     * A checksum for error checking and also avoid concurrent PO having same date time or random string.
     * This is achieved via using the microtime as input for checksum initiation.
     * @param type $source
     * @return int Single digit checksum
     */
    protected function getChecksum($source=null,$digit=1)
    {
        if (!isset($source)){//use micro time
            list($usec, $sec) = explode(' ', microtime());
            $source = substr($usec,2,6);
        }
        $sum = 0;
        for ($i=0; $i < strlen($source); $i++) {
            $sum += (int)$source[$i];
        }
        if ($sum > (pow(10,$digit)-1))
            $sum = $this->getChecksum((string)$sum);//repeatedly sum until hit single digit
        return $sum;
    }  
    /**
     * Order number example
     * @return string
     */
    public static function example($owner,$template,$fashion,$separator,$prefix=null)
    {
        $num = new OrderNumberGenerator($owner);
        $num->separator = $separator;
        $num->prefix = $prefix;
        $num->template = $template;
        return Sii::t('sii','Example: {example}',['{example}'=>$num->generate($fashion)]);
    }    
    /**
     * Order number counter formatting
     * @return string
     */
    public static function formatCounter($value,$length=null)
    {
        if (!isset($length))
            $length = OrderNumberGenerator::$randomStringLength;
        
        return str_pad($value,$length,'0',STR_PAD_LEFT);//left pad 0 up to 8 chars
    }        
}

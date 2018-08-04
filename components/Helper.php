<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * General purpose Helper class
 * @author kwlok
 */
class Helper 
{
    /*
     * A dummy constant to indicate readonly
     */
    const READONLY  = true;
    /*
     * A dummy constant to indicate null
     */
    const NULL = null;
    /*
     * A dummy constant to indicate purify
     */
    const PURIFY = true;
    /*
     * Separator chars
     */
    const DASH_SEPARATOR  = '-';
    const PIPE_SEPARATOR = '|';
    /*
     * Html entities chars
     */
    const SINGLE_ARROW_CHAR = '&#8250;';
    const DOUBLE_ARROW_CHAR = '&#187;';
    /*
     * A flag to show singular or plural
     */
    const SINGULAR = 1;
    const PLURAL   = 2;
    /*
     * A indication flag to return formatted value
     */
    const FORMAT = true;
    const NO_FORMAT = false;//opposite of above
    /**
     * Purify html content
     * @param string The string to get purified
     * @param array options configuration 
     * @see http://htmlpurifier.org/live/configdoc/plain.html for otpons
     */
    public static function purify($text,$options=[])
    {
        $p = new CHtmlPurifier();
        $p->options = array_merge($options,[
            'URI.AllowedSchemes'=>[
                'http' => true,
                'https' => true,
            ],
            /**
             * @todo Currently the attributes allowed for 'img.style' seems not working
             * Interim solution is to set img width=100% at css level
             * Image style e.g. width=100% is stored, but when rendered it is stripped off!
             */
//            'HTML.Allowed' => 'div,b,strong,i,em,a[href|title],ul,ol,li,p[style|class],br,span[class|s‌​tyle],img[width|heig‌​ht|alt|src|style]',
//            'HTML.AllowedAttributes'=>'img.src,img.style,*.style,*.class',//allow styles to be stored
            'HTML.SafeIframe'=>true,
            'URI.SafeIframeRegexp'=>'%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',//allow YouTube and Vimeo 
        ]);
        return $p->purify($text);
    }
    /**
     * Sort Array by key
     * @param Array
     */
    public static function sortArrayByKey($array,$order='ASC')
    {
        switch ($order) {
            case 'ASC':
                ksort($array);
                break;
            case 'DESC':
                krsort($array);
                break;
        }
        return $array;

    }
    public static function getQuantityList($min=1,$max=8,$interval=1)
    {
        $map = new CMap();
        for ($i = $min; $i <= $max; $i = $i + $interval) 
            $map->add($i, $i);
        return $map->toArray();
    }    
    /**
     * Get file extension
     * @param type $file
     * @return type
     */
    public static function getFileExtension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);      
    }
    /**
     * Custom strpos to support arrays as needles
     * @param mixed $haystack the The string / array to search in
     * @param mixed $needle the array|string to check against
     * @return boolean
     */
    public static function strpos_arr($haystack, $needle, $caseSensitive=false) 
    {
        $compare = function($haystack,$needle) use ($caseSensitive) {
            if ($caseSensitive)
                return strpos(strtolower($haystack), strtolower($needle));
            else
                return strpos($haystack, $needle);
        };
        
        if (!is_array($needle)){
            return $compare($haystack,$needle);
        }

        foreach ($needle as $what){
            if (($pos = $compare($haystack,$what)) !== false)
                return $pos;
        }
        return false;
    }
    
    public static function isInteger($int) 
    {
        return (is_numeric($int) === TRUE && (int)$int  == $int);
    }  
    
    public static function isFloat($float) 
    {
        return (is_numeric($float) === TRUE && (float)$float  == $float);
    }      
    
    public static function jsUrl($jsfunc,$url,$params) 
    { 
        return 'javascript:'.$jsfunc.'(\''.Yii::app()->createUrl($url,$params).'\');';
    }

    public static function rightTrim($string,$length,$trailing='...') 
    { 
        if(strlen($string) != mb_strlen($string, 'utf-8')){ //non english, e.g. chinese
            if (mb_strlen($string)>$length)
                return mb_substr($string, 0, $length/2,'UTF-8').$trailing;
            return $string;
        }
        else {
            if (mb_strlen($string)>$length)
                return mb_substr($string, 0, $length,'UTF-8').$trailing;
            return $string;
        }
    }
    
    public static function leftRightTrim($string,$leftMask,$rightMask)
    {
        $_tmp1 = ltrim($string,$leftMask);
        return rtrim($_tmp1,$rightMask);
    }
    /**
     * @return mixed string or array when more than one element
     */
    public static function explodeErrors($string,$delimiter=self::PIPE_SEPARATOR)
    {
        $result = explode($delimiter, $string);
        return count(array_filter($result))==1?$result[0]:array_values(array_filter($result));
    }
    /**
     * Implode error into string
     * @param array $array The validation errors return from CModel / CActiveRecord
     * @param string $glue
     * @return string
     */
    public static function implodeErrors($array,$glue=self::PIPE_SEPARATOR)
    {
        $text = '';
        foreach ($array as $key) {
            foreach ($key as $value) {
                if (is_array($value)){
                    foreach ($value as $subkey)
                        foreach ($subkey as $subvalue){
                            $text .= $subvalue.$glue;
                        }
                }
                else {
                    $text .= $value.$glue;
                }
            }
        }
        return $text;
    }
    
    public static function htmlErrors($array,$htmlOptions=['style'=>'padding:0px 20px;'])
    {
        $html = CHtml::openTag('ul',$htmlOptions);
        foreach ($array as $key1 => $key2) {
            foreach ($key2 as $value) {
                if (is_array($value)){
                    foreach ($value as $key2 => $key3) {
                        foreach ($key3 as $subvalue) {
                            $html .= CHtml::tag('li',[],$subvalue);
                        }
                    }
                }
                else
                    $html .= CHtml::tag('li',[],$value);
            }
        }
        $html .= CHtml::closeTag('ul');
        return $html;
    }
    public static function htmlList($list,$htmlOptions=['style'=>'margin:0px;padding:0px 20px;'])
    {
        if ($list instanceof CList)
            $list = $list->toArray();
        $html = CHtml::openTag('ul',$htmlOptions);
        foreach ($list as $value) {
            $html .= CHtml::tag('li',[],$value);
        } 
        $html .= CHtml::closeTag('ul');
        return $html;
    }
    public static function htmlKeyValueArray($array,$htmlOptions=[])
    {
        $html = '';
        if (empty($array))
            return '';
        else{
            $id = isset($htmlOptions['id'])?$htmlOptions['id']:'option';
            $html .= CHtml::openTag('div',$htmlOptions);
            foreach ($array as $key => $values) {
                $html .= CHtml::openTag('div',['class'=>'option '.$key,'id'=>$id.'_'.$key]);
                $html .= CHtml::tag('div',['class'=>'key'],$key);
                $html .= CHtml::openTag('div',['class'=>'values']);
                $html .= CHtml::openTag('ul');
                foreach ($values as $valueKey => $valueValue) {
                    $html .= CHtml::openTag('li');
                    $html .= CHtml::tag('span',['class'=>'value','data-group'=>$key,'data-key'=>$valueKey],$valueValue);
                    $html .= CHtml::closeTag('li');
                }
                $html .= CHtml::closeTag('ul');
                $html .= CHtml::closeTag('div');
                $html .= CHtml::closeTag('div');
            }
            $html .= CHtml::closeTag('div');
            return $html;
        }
    }

    public static function htmlSmartKeyValues($array)
    {
        if (empty($array))
            return '';
        else{
            $result = '';
            foreach ($array as $key => $value) {
                $result .= CHtml::openTag('div',['class'=>'key-value-pair']);
                if (!empty($key))
                    $result .= CHtml::tag('span',['class'=>'key'],$key);
                $result .= CHtml::tag('span',['class'=>'value'],empty($value)?'<i>not set</i>':$value);
                $result .= CHtml::closeTag('div');
            }
            return $result;
        }
    }         
    public static function htmlColorTag($text,$color='red',$round=true,$inverse=false)
    {
        return self::htmlColorText(['text'=>$text,'color'=>$color], $round, $inverse);
    }  

    public static function htmlColorText($text,$round=true,$inverse=false)
    {
        if (empty($text))
            return '';
        if (!$inverse)
           return '<span class="tag '.($round==true?'rounded3':'').'" style="background:'.$text['color'].';">'.Sii::t('sii','{text}',['{text}'=>$text['text']]).'</span>';
        else
           return '<span class="tag '.($round==true?'rounded3':'').' inverse"><span>'.Sii::t('sii','{text}',['{text}'=>$text['text']]).'</span></span>';
    }  
    
    public static function getMimeTypeIconUrl($mime,$assetUrl)
    {
        switch ($mime) {
            case substr($mime, 0, 5)=='image':
                return $assetUrl.'/attachment.png';
                break;
            case substr($mime, 0, 4)=='text':
                return $assetUrl.'/document_txt.png';
                break;
            case 'application/pdf':
                return $assetUrl.'/document_pdf.png';
                break;
            case 'application/msword':
                return $assetUrl.'/document_word.png';
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return $assetUrl.'/document_word.png';
                break;
            case 'application/vnd.ms-excel':
                return $assetUrl.'/document_excel.png';
                break;
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                return $assetUrl.'/document_excel.png';
                break;
            case 'application/vnd.ms-powerpoint':
                return $assetUrl.'/document_ppt.png';
                break;
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                return $assetUrl.'/document_ppt.png';
                break;
            default:
                return $assetUrl.'/attachment.png';
                break;
        }
    }
    public static function htmlDownloadLink($file,$assetUrl=null)
    {
        if ($file instanceof Attachment){
            $html = '<span>';
            $html .= '<span style="padding: 5px 5px;">'.CHtml::image(Helper::getMimeTypeIconUrl($file->mime_type,$assetUrl),'attachment',['style'=>'vertical-align:middle']).'</span>';
            $html .= l($file->name,$file->downloadUrl,['style'=>'padding: 5px 5px;','title'=>$file->name,'download'=>$file->name]);
            $html .= '</span>';
            return $html;
        }
        elseif (is_array($file)) {
            if (!isset($file['download']))
                $file['download'] = $file['name'];
            $html = '<span>';
            $html .= l($file['name'],$file['url'],['style'=>'padding: 5px 5px;','title'=>$file['name'],'download'=>$file['download']]);
            $html .= '</span>';
            return $html;
        }
        else
            return '';
    }
    public static function formatBytes($bytes, $precision = 2) 
    { 
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 

        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        //$bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }   
    /**
     * Construct url query 
     * @param type $url need to be a clean url route without params
     * @param type $params params to be attached to the url
     * @return type
     */
    public static function constructUrlQuery($url,$params)
    {
        if (!empty($params)){
            $url .= '?';
            foreach ($params as $key => $value) {
                $url .= $key.'='.$value.'&';
            }
            $url = substr($url,0,-1);//remove last "&" char
        }
        return $url;
    }
    /**
     * File provides easy way to manipulate url parameters
     * @author Lokkw
    */        
    public static function convertUrlQuery($query) 
    { 
        $queryParts = explode('&', $query); 

        $params = []; 
        foreach ($queryParts as $param) { 
            $item = explode('=', $param); 
            if (isset ($item[1]))
                $params[$item[0]] = $item[1]; 
        } 

        return $params; 
    }
    /**
     * File provides easy way to manipulate url parameters
     * @author Lokkw
     */          
    public static function getParams($url) 
    { 
        $query = parse_url($url,PHP_URL_QUERY);
        return self::convertUrlQuery($query);
    }     
        
    /**
     * Encode Any String to Only Alphanumeric Chars 
     * @param type $input
     * @return type
     */
    public static function hex_encode($input) 
    {
      return bin2hex($input);
    }
    /**
     * Decode Any String to Only Alphanumeric Chars 
     * @param type $input
     * @return type
     */
    public static function hex_decode($input) 
    {
      return hex2bin($input);
    }
    /**
     * Return html format of status using tag <code>
     * 
     * @see _process.css (for css style)
     * @param type $status
     * @return type
     */
    public static function htmlIndexFilter($status,$tag=true,$plain=false) 
    {
        if (!$tag){   
            if ($plain)
                return $status;
            else
                return '<span style="padding:0px 10px;font-size:1.25em;">'.$status.'</span>';
        }
        else {
            if (is_array($status)){
                return '<code class="status '.Helper::cssSafe($status['code']).'">'.$status['text'].'</code>';                
            }
            else
                return '<code class="status '.Helper::cssSafe($status).'">'.$status.'</code>';                
        }
    }
    /**
     * @return type System time up to milli second
     */
    public static function getSystemTime($withMicrosec=true)
    {
        list($usec, $sec) = explode(' ', microtime());
        if ($withMicrosec)
            return $sec.'.'.substr($usec,2,6);
        else
            return $sec;
    }
    /**
     * Read latest deployed git version information
     * Format: repo_name/branch_name/commit_hash,timestamp
     * 
     * @param type $gitWorkTree
     * @param type $versionFile
     * @return type
     */
    public static function getSystemVersion($gitWorkTree,$versionFile='git.version')
    {
        $version = app()->id;//default version
        $gitFile = $gitWorkTree.DIRECTORY_SEPARATOR.$versionFile;
        if (file_exists($gitFile)){
            //read from file
            $info = explode(',', file_get_contents($gitFile));
            $date = new DateTime();
            $date->setTimestamp($info[1]);
            $version = $info[0].' '.$date->format('Y-m-d H:i:s');
        }
        return $version;
    }
    /**
     * Read latest deployed git commit hash information
     * Format: repo_name/branch_name/commit_hash,timestamp
     * 
     * @param type $gitWorkTree
     * @param type $versionFile
     * @return type
     */
    public static function getGitCommitHash($gitWorkTree,$versionFile='git.version')
    {
        $gitFile = $gitWorkTree.DIRECTORY_SEPARATOR.$versionFile;
        if (file_exists($gitFile)){
            $info = explode(',', file_get_contents($gitFile));//example: shopbay/alpha_test/73d0d81,timestamp
            $version = explode('/', $info[0]);//example: shopbay/alpha_test/73d0d81
            return $version[2];
        }
        else
            return '-unknown-';
    }
    
    public static function getMySqlDateFormat($timestamp)
    {
        return date('Y-m-d',$timestamp);
    }

    public static function prettyDate($timestamp)
    {
        return PrettyDate::getString(new SDateTime($timestamp));     
    }
    /**
     * Merge array values by same key
     * @param int $key For now support only integer
     */
    public static function aggregateArrayValues($array,$key,$value)
    {
        $data = new CMap();
        foreach ($array as $id => $target) {
            if (!$data->contains($target[$key])){
                $clone = $array;
                unset($clone[$id]);//remove itself for comparison
                foreach ($clone as $_t) {
                    if ($target[$key]==$_t[$key])
                        $target[$value] += $_t[$value];//support counter
                }
                $data->add($target[$key],$target);
            }
        }
        return array_values($data->toArray());
    }
    /**
     * url safe encode targeting url unfriendly chars only
     * @param type $str
     * @return type
     */
    public static function urlstrtr($str,$base64=true)
    {
        $tr = strtr($str, '+/=%', '-_.~');
        return urlencode($base64?base64_encode($tr):$tr);
    }
    /**
     * url safe decode of urlstrtr
     * @param type $str
     * @return type
     */
    public static function urlstrdetr($str,$base64=true)
    {
        $tr = $base64?base64_decode(urldecode($str)):urldecode($str);
        return strtr($tr, '-_.~', '+/=%');
    }
    /**
     * Decode Unicode escape sequences like “\u00ed” to proper UTF-8 encoded characters
     * @param type $str
     */
    public static function getUTF8encodedChars($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            }, $str);
    }
    /**
     * Decode Unicode escape sequences like “\u00ed” to proper UTF-16 encoded characters
     * @param type $str
     */
    public static function getUTF16encodedChars($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
            }, $str);
    }
    /**
     * Visitor: unique visitor count; For login user, this will be account_id; 
     * For guest, this will be IP address
     * @return mixed
     */
    public static function getVisitor($asGuest=false)
    {
        if ($asGuest)
            self::getGuest();
        else
            return user()->isGuest?self::getGuest():user()->getId();
    }
    /**
     * For guest, this will be IP address
     * @return mixed
     */
    public static function getGuest()
    {
        return Yii::app()->getRequest()->getUserHostIPAddress();
    }
    
    public static function registerJs($script,$name=null,$position=CClientScript::POS_END)
    {
        Yii::import('common.extensions.escriptboost.EScriptBoost');
        $minified = EScriptBoost::minifyJs($script,EScriptBoost::JS_MIN);
        cs()->registerScript(__METHOD__.$name,$minified,$position);
    }
    
    public static function camelCase($key)
    {
        $result = '';
        $tokens = explode('_', $key);
        foreach ($tokens as $index => $value) {
            if ($index==0)
                $result = $value;
            else
                $result .= ucfirst($value);
        }
        return $result;
    }
    /**
     * Example:
     * 
     * $startDate = '2014-06-03'; // select date in Y-m-d format
     * $nMonths = 1; // choose how many months you want to move ahead
     * $final = endDateCycle($startDate, $nMonths) // output: 2014-07-02
     * 
     * @param type $months
     * @param DateTime $dateObject
     * @return \DateInterval
     */
    public static function endDateCycle($startDate, $months)
    {
        $date = new DateTime($startDate);

        // call second function to add the months
        $newDate = $date->add(self::addMonths($months, $date));

        // goes back 1 day from date, remove if you want same day of month
        $newDate->sub(new DateInterval('P1D')); 

        //formats final date to Y-m-d form
        $dateReturned = $newDate->format('Y-m-d'); 

        return $dateReturned;
    }   
    
    public static function addMonths($months, DateTime $dateObject) 
    {
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +'.$months.' month');

        if($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P'.$months.'M');
        }
    }
    
    public static function getBooleanValues($mode=null)
    {
        if (!isset($mode)){
            return [1=>Sii::t('sii','Yes'),0=>Sii::t('sii','No')];
        }
        else {
            $modes = self::getBooleanValues();
            return $modes[$mode];
        }
    }
    /**
     * Add nofollow for SEO optimization
     * @param $html
     * @param string $target
     * @param null $skip
     * @return mixed
     */
    public static function addNofollow($html, $target='_blank', $skip = null)
    {
        return preg_replace_callback(
                "#(<a[^>]+?)>#is", function ($mach) use ($skip,$target) {
                return (
                    !($skip && strpos($mach[1], $skip) !== false) &&
                    strpos($mach[1], 'rel=') === false
                ) ? $mach[1] . (' rel="nofollow" target="'.$target.'">') : $mach[0];
            },
            $html
        );
    }
    /**
     * Replace space with dash char to make it safe to use in css / js
     * @param type $string
     * @return type
     */
    public static function cssSafe($string)
    {
        return str_replace(' ', '-',$string);
    }
    /**
     * Replace space with underscore char to make it safe to use in php method
     * @param type $string
     * @return type
     */
    public static function phpSafe($string)
    {
        return str_replace(' ', '_',$string);
    }  
    
    public static function parseBool($value)
    {
        if (is_bool($value))
            return $value;
        else
            return $value=='true';
    }
    /**
     * Return in structure:
     * array(
     *  'url'=>'',
     *  'path'=>'',
     *  'query'=>'',
     * )
     * @param type $uri
     * @return type
     */
    public static function parseUri($uri=null)
    {
        if (!isset($uri))
            $uri = request()->getRequestUri();
        
        $res = ['path'=>[],'query'=>[]];//default to empty
        $url = parse_url($uri);
        $res['url'] = $url;
        if (isset($url['path']))
            $res['path'] = explode('/', $url['path']);
        if (isset($url['query'])){
            parse_str($url['query'], $res['query']);
        }        
        logTrace(__METHOD__,$res);
        return $res;
    }
}
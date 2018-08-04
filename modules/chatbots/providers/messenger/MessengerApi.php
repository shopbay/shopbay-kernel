<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerMessage');
Yii::import('common.modules.chatbots.providers.messenger.threads.ThreadSetting');
/**
 * Description of MessengerApi
 *
 * @author kwlok
 */
class MessengerApi
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_DELETE = 'DELETE';
    /**
     * Messenger API base url
     * @var string
     */
    protected $apiBaseRoute = 'https://graph.facebook.com/v2.6';
    /**
     * Page access token
     * @var 
     */
    protected $token;
    /**
     * Constructor
     * @param string $token The page access token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }
    /**
     * Get user profile
     * 
     * Example request:
     * curl -X GET "https://graph.facebook.com/v2.6/<USER_ID>?fields=first_name,last_name,profile_pic,locale,timezone,gender&access_token=PAGE_ACCESS_TOKEN"  
     * 
     * <pre>
     * //Example json response
     * {
     *  "first_name": "Peter",
     *  "last_name": "Chang",
     *  "profile_pic": "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-xpf1/v/t1.0-1/p200x200/13055603_10105219398495383_8237637584159975445_n.jpg?oh=1d241d4b6d4dac50eaf9bb73288ea192&oe=57AF5C03&__gda__=1470213755_ab17c8c8e3a0a447fed3f272fa2179ce",
     *  "locale": "en_US",
     *  "timezone": -7,
     * "gender": "male"
     * } 
     * </pre>
     * @param string $userId
     * @param array $fields The user profile fields to query; If empty, means get all fields
     *              Fields are: 'first_name','last_name','profile_pic','locale','timezone','gender'
     * @return array
     */
    public function userProfile($userId,$fields=[])
    {
        if (empty($fields))
            $fields = ['first_name','last_name','profile_pic','locale','timezone','gender'];
        
        $url = $this->apiBaseRoute.'/'.$userId;
        $data = ['fields'=>implode(',', $fields)];
        $response = $this->_invoke($url,$data, self::HTTP_GET);
        if ($this->_parseResponse($response, $data)==false)
            return false;
        else 
            return $response;//return back the profile output
    }
    /**
     * Get user page scoped id
     * 
     * Example request:
     * curl -X GET "https://graph.facebook.com/v2.6/me?access_token=PAGE_ACCESS_TOKEN&fields=recipient&account_linking_token=ACCOUNT_LINKING_TOKEN"
     * 
     * <pre>
     * //Example json response
     * {
     * "id": "PAGE_ID",
     * "recipient": "PSID"
     * }  
     * </pre>
     * @param string $accountLinkingToken A valid and unexpired account_linking_token
     * @return array
     */
    public function getPSID($accountLinkingToken)
    {
        $url = $this->apiBaseRoute.'/me';
        $data = [
            'fields'=>'recipient',
            'account_linking_token'=>$accountLinkingToken,
        ];
        $response = $this->_invoke($url,$data, self::HTTP_GET);
        if ($this->_parseResponse($response, $data)==false)
            return false;
        else 
            return $response;//return back the profile output
    }    
    /**
     * Send Message
     * @param MessengerMessage|ThreadSetting $object
     * @return array
     */
    public function send($object)
    {
        if ($object instanceof MessengerMessage)
            $url = $this->apiBaseRoute.'/me/messages';
        elseif ($object instanceof ThreadSetting)
            $url = $this->apiBaseRoute.'/me/thread_settings';
        
        $response = $this->_invoke($url, $object->data);
        return $this->_parseResponse($response, $object->data);
    }    
    /**
     * Delete Thread Setting
     * @param ThreadSetting $setting
     * @return array
     */
    public function delete($setting)
    {
        if (!$setting instanceof ThreadSetting)
            throw new CException('Invalid thread setting');
            
        $url = $this->apiBaseRoute.'/me/thread_settings';
        
        $response = $this->_invoke($url, $setting->data, self::HTTP_DELETE);
        return $this->_parseResponse($response, $setting->data);
    }
    /**
     * Send request to Facebook graph api
     * @param string $url
     * @param array  $data
     * @param string $type Type of request (GET|POST|DELETE)
     * @return boolean
     */
    private function _invoke($url, $data, $type=self::HTTP_POST)
    {
        $headers = [
            'Content-Type: application/json',
        ];
        $data['access_token'] = $this->token;
        
        logTrace(__METHOD__.' data',$data);
        
        if ($type == self::HTTP_GET) {
            $url .= '?'.http_build_query($data);
        }
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, false);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        
        if($type == self::HTTP_POST || $type == self::HTTP_DELETE) {
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        if ($type == self::HTTP_DELETE) {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($process);
        if ($response===false){
            $err = curl_error($process);
            logError(__METHOD__.' error ', $err, false);
        }
        curl_close($process);
        return json_decode($response, true);
    }
    /**
     * Parse the response after send api
     * @param type $response
     * @param array $data
     * @return type
     */
    private function _parseResponse($response,$data)
    {
        if (isset($response['error'])){
            logError(__METHOD__.' failed!',['response'=>$response,'data'=>$data]);
            return false;
        }
        else {
            logInfo(__METHOD__.' ok',['response'=>$response,'data'=>$data]);
            return true;
        }
    }
}
<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
Yii::import("common.modules.notifications.models.Notification");
Yii::import("common.modules.notifications.models.NotificationScope");
/**
 * Description of OptInPayload
 * This is for notification subscription
 * 
 * @author kwlok
 */
class OptInPayload extends ChatbotPayload 
{
    /**
     * Params key to be kept as short as possible to keep its to string length the shortest
     */
    public static $notificationParamKey = 'n';
    public static $scopeParamKey        = 's';
    public static $accountParamKey      = 'a';
    /**
     * Constructor.
     * @param string $type The payload type
     * @param string|NotificationScope $scope The notification scope encoded string {@link NotificationScope}
     * @param string $account_id If the user account id is known, pass it in
     */
    public function __construct($type,$scope,$account_id=Account::GUEST)
    {
        $params = [];
        if ($scope instanceof NotificationScope)
            $params[static::$scopeParamKey] = $scope->toString();
        else
            $params[static::$scopeParamKey] = $scope;
            
        $params[static::$accountParamKey] = $account_id;
        /**
         * Retrieve only public (non-account required) notifications
         * account-required notification is to be subscribed later when user logs in Messenger
         * @see OptInController::showAccountRequiredSubscriptions
         */
        foreach (self::getPublicNotifications() as $notification => $alias) {
            $params[static::$notificationParamKey][] = $alias;
        };
        
        parent::__construct($type, $params, false);//typePrefix false
    }    
    /**
     * @return NotificationScope Notification socpe
     */
    public function getScope()
    {
        $json = json_decode($this->params[static::$scopeParamKey]);
        $scope = new NotificationScope($json->id, $json->class);
        return $scope;
    }
    /**
     * Check if this payload is triggerd by an account user
     */
    public function getHasAccount()
    {
        return $this->account != Account::GUEST;
    }
    /**
     * Get the account user
     */
    public function getAccount()
    {
        return $this->params[static::$accountParamKey];
    }
    /**
     * Get notification as name
     * @return string
     */
    public function getNotifications()
    {
        $notifications = [];
        $findName = function($alias){
            $found = null;
            foreach (Notification::subscriptionsConfig() as $name => $config) {
                if (isset($config['alias']) && $config['alias']==$alias){
                    $found = $name;
                    break;
                }
            }
            return $found;
        };
        //restore back to notification name
        foreach ($this->params[static::$notificationParamKey] as $alias) {
            $notifications[] = $findName($alias);
        }
        return $notifications;
    }    
    /**
     * Get notification display names
     * @return string
     */
    public function getNotificationDisplayNames()
    {
        $updates = '';
        foreach ($this->notifications as $name) {
            $updates .= Notification::siiName()[$name].', ';
        }
        return rtrim($updates,', ');//remove last entry
    }    
    /**
     * Serialize as string
     * @return string
     */
    public function toString()
    {
        return base64_encode(ChatbotPayload::encode(get_class($this), $this->type, $this->params));
    }    
    /**
     * Decode base64 first 
     * @param string $encodedPayload
     * @return mixed
     */
    public static function decode($encodedPayload)
    {
        $payloadString = base64_decode($encodedPayload);
        
        $payload = explode(self::SEPARATOR, $payloadString);
        $type = $payload[0];
        
        if (isset($payload[1])){
            $data = json_decode($payload[1],true);
            logInfo(__METHOD__.' payload data', $data);
            if (isset($data['class'])){
                $class = $data['class'];
                if (isset($data['params']) && 
                    isset($data['params'][static::$scopeParamKey]) && 
                    isset($data['params'][static::$accountParamKey]))
                    return new $class($type,$data['params'][static::$scopeParamKey],$data['params'][static::$accountParamKey]);//decoding no need type prefix
            }
        }
            
        return null;
    }    
    /**
     * Get account required notifications
     * @param string $encodedPayload
     * @return mixed
     */
    public static function getAccountRequiredNotifications()
    {
        $notifs = [];
        foreach (Notification::subscriptionsConfig() as $notification => $config) {
            if (isset($config['accountRequired']) && $config['accountRequired']){
                $notifs[$notification] = $config['alias'];
            }            
        };
        return $notifs;
    }    
    /**
     * Get non-account required notifications
     * @return array
     */
    public static function getPublicNotifications()
    {
        $notifs = [];
        foreach (Notification::subscriptionsConfig() as $notification => $config) {
            if (!isset($config['accountRequired']) || 
                (isset($config['accountRequired']) && !$config['accountRequired'])){
                $notifs[$notification] = $config['alias'];
            }            
        };
        return $notifs;
    }    
}

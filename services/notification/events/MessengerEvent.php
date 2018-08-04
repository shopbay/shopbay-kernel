<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.services.notification.events.NotificationEvent');
/**
 * MessengerEvent represents the parameter for the {@link NotificationManager::onMessenger onMessenger} event.
 *
 * @author kwlok
 */
class MessengerEvent extends NotificationEvent
{
    /**
     * @var string facebook page scoped id 
     */
    public $recipient;
    /**
     * @var string message subject
     */
    public $subject;
    /**
     * @var string message content (actual value depends on message type)
     */
    public $content;
    /**
     * Constructor.
     * @param string $scope e.g. json_encode {"id":1,"class":"Shop"}
     * @param string $recipient 
     * @param string $subject 
     * @param string $content 
     * @param string $params Payload params, if any
     */
    public function __construct($scope,$recipient,$subject,$content,$params=[],$mode=self::ASYNCHRONOUS)
    {
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->content = $content;
        $this->mode = $mode;
        parent::__construct($scope,$params);//$scope is the sender
    }
    /**
     * Scope is set as the event sender
     * @return type
     */
    public function getScope()
    {
        return $this->sender;
    }
    /**
     * Get scope object. 
     * @return type
     * @throws CException
     */
    public function getScopeObject()
    {
        return self::parseScope($this->scope);
    }
    /**
     * Parse the notification scope. 
     * @return type
     * @throws CException
     */
    public static function parseScope($scopeString)
    {
        $scope = json_decode($scopeString);
        $class = $scope->class;
        $model = $class::model()->findByPk($scope->id);
        if ($model==null){
            throw new CException('Scope not found');
        }
        return $model;
    }
}
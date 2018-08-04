<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.models.MessengerModelTrait');
/**
 * Description of MessengerButton
 *
 * @author kwlok
 */
abstract class MessengerButton extends CComponent
{
    use MessengerModelTrait;
    
    protected $type = 'unset';//button type
    protected $title;//button title
    protected $payload;//button payload
    /**
     * Constructor.
     * @param string $title
     * @param string $payload
     */
    public function __construct($title,$payload)
    {
        $this->title = $title;
        $this->payload = $payload;
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'payload' => $this->payload,
        ];    
    }
}

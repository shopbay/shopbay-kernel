<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerTemplate');
Yii::import('common.modules.chatbots.providers.messenger.buttons.Bubble');
/**
 * Description of GenericTemplate
 *
 * @author kwlok
 */
class GenericTemplate extends MessengerTemplate
{
    public static $bubblesLimit = 10;
    /**
     * Bubbles to be included in generic message, and is limited to 10
     * @var array
     */
    protected $bubbles = [];
    /**
     * Constructor.
     * @param string $recipient
     * @param array $bubbles
     */
    public function __construct($recipient,$bubbles=[])
    {
        if (is_array($bubbles)){
            foreach ($bubbles as $bubble) {
                $this->addBubble($bubble);
            }
        }
        else {
            $this->addBubble($bubbles);
        }
        
        $this->checkCountLimit('bubbles', self::$bubblesLimit);
        
        $payload = [
            'template_type' => 'generic',
            'elements' => $this->bubbles,
        ];
        
        parent::__construct($recipient,$payload);
    }    
    /**
     * Add bubble data
     * @param Bubble $bubble
     * @throws CException
     */
    protected function addBubble($bubble)
    {
        if (!$bubble instanceof Bubble)
            throw new CException('Invalid bubble');
        $this->bubbles[] = $bubble->data;
    }

}
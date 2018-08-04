<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerTemplate');
Yii::import('common.modules.chatbots.providers.messenger.buttons.MessengerButton');
/**
 * Description of ButtonTemplate
 *
 * @author kwlok
 */
class ButtonTemplate extends MessengerTemplate
{
    public static $buttonsLimit = 3;
    public static $textLengthLimit = 320;
    /**
     * Text that appears in main body, must be UTF-8 and has a 320 character limit
     * @var string
     */
    protected $text;
    /**
     * Set of buttons that appear as call-to-actions and is limited to 3
     * @var array
     */
    protected $buttons = [];
    /**
     * Constructor.
     * @param string $recipient
     * @param string $text Button text must be UTF-8 and has a 320 character limit
     * @param mixed $buttons array of buttons; Can be single button as well if not passed in as array
     */
    public function __construct($recipient,$text,$buttons=[])
    {
        $this->text = $text;
        if (is_array($buttons)){
            foreach ($buttons as $button) {
                $this->addButton($button);
            }
        }
        else {
            $this->addButton($buttons);
        }
        
        $this->checkLengthLimit('text', self::$textLengthLimit);
        $this->checkCountLimit('buttons', self::$buttonsLimit);
        
        $payload = [
            'template_type' => 'button',
            'text' => $text,
            'buttons' => $this->buttons,
        ];
        parent::__construct($recipient,$payload);
    }    
    /**
     * Add button data
     * @param MessengerButton $button
     * @throws CException
     */
    protected function addButton($button)
    {
        if (!$button instanceof MessengerButton)
            throw new CException('Invalid button');
        $this->buttons[] = $button->data;
    }
}
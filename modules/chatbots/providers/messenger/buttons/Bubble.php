<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.models.MessengerModelTrait');
/**
 * Description of Bubble
 *
 * @author kwlok
 */
class Bubble extends CComponent
{
    use MessengerModelTrait;
    public static $titleLengthLimit = 80;
    public static $subtitleLengthLimit = 80;
    public static $buttonsLimit = 3;
    
    protected $title;//Bubble title has a 80 character limit
    protected $subtitle;//Bubble subtitle has a 80 character limit
    protected $itemUrl;//URL that is opened when bubble is tapped
    protected $imageUrl;//Bubble image
    protected $buttons = [];//Set of buttons that appear as call-to-actions, is limited to 3
    /**
     * Constructor.
     * Title and at least one other field (image url, subtitle or buttons) is required with non-empty value
     * 
     * @param type $title
     * @param type $subtitle optional
     * @param type $itemUrl optional
     * @param type $imageUrl optional
     * @param array $buttons optional
     */
    public function __construct($title,$subtitle=null,$itemUrl=null,$imageUrl=null,$buttons=[])
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->itemUrl = $itemUrl;
        $this->imageUrl = $imageUrl;
        foreach ($buttons as $button) {
            $this->buttons[] = $button->data;
        }
        
        $this->checkLengthLimit('title', self::$titleLengthLimit);
        $this->checkLengthLimit('subtitle', self::$subtitleLengthLimit);
        $this->checkCountLimit('buttons', self::$buttonsLimit);
        
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'item_url' => $this->itemUrl,
            'image_url' => $this->imageUrl,
            'buttons' => $this->buttons,
        ];    
    }
}

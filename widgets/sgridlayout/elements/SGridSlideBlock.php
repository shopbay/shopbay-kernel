<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridSlideBlock
 *
 * @author kwlok
 */
class SGridSlideBlock extends SGridColumn
{
    public $type = SGridLayout::SLIDE_BLOCK;
    /**
     * Expect data format:
     *  [
     *     'image' => '<img src="http://www.skb.com/images/fullimage3.jpg"/>',
     *     'text' => [
     *         '<locale1>' => '..',
     *         '<locale2>' => '..',
     *     ],
     *     'ctaLabel' => [
     *         '<locale1>' => '..',
     *         '<locale2>' => '..',
     *     ],
     *     'ctaUrl' => '<any link>'
     *  ]
     */
    public $items = [];
    /**
     * Render view file
     * 
     * Follow bootstrap()->Carousel() data structure 'items'
     * Taking input $items, and transform to following format:
     * 
     * @var array
     * [
     *   //the item contains only the image
     *   '<img src="http://www.skb.com/images/fullimage1.jpg"/>',
     *   // equivalent to the above
     *   [
     *     'content' => '<img src="http://www.skb.com/images/fullimage2.jpg"/>',
     *   ],
     *   [
     *     'content' => '<img src="http://www.skb.com/images/fullimage3.jpg"/>',
     *     'caption' => '<p>_caption_text_</p><a href="_cta_url_">_cta_label_</a>',
     *     'options' => [''],
     *   ],
     * ]
     * @return string
     */
    public function render()
    {
        foreach ($this->items as $index => $item) {
            //construct content from image
            if (isset($item['image']))
                $this->items[$index]['content'] = CHtml::image($item['image'], 'Slide Image', ['class'=>'slide-image']);

            $caption = '';
            if (isset($item['text'])){
                $text = $this->getLanguageValue($item['text'], true);
                if (strlen($text)>0)
                    $caption = CHtml::tag('p',[],$text);
            }
            if (isset($item['ctaLabel'])){
                $label = $this->getLanguageValue($item['ctaLabel'], true);
                if (strlen($label)>0)
                    $caption .= CHtml::link($label,isset($item['ctaUrl'])?$item['ctaUrl']:'#',[]);
            }
            if (strlen($caption)>0)
                $caption = CHtml::tag ('div', ['class'=>'caption-wrapper'], $caption);
            //formulate caption html
            $this->items[$index]['caption'] = $caption;
        }
        $this->renderBlock();//render block content first
        return parent::render();
    }   
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if ($this->modal){
            $widget = $this->cloneAsWidget([
                //local settings
                'items'=>$this->items,//todo, need to breakdown into individual fields
            ]);
            $this->content = $widget->blockContent;
        }
        else {
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._slideblock',['element'=>$this],true);
        }
    }    
}

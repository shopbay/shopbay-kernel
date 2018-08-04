<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridImageBlock
 *
 * @author kwlok
 */
class SGridImageBlock extends SGridColumn
{
    public $type = SGridLayout::IMAGE_BLOCK;
    /**
     * string the column size (1 - 12)
     */
    public $size = 4;//default width
    /**
     * Item title
     * @var string
     */
    public $title;
    /**
     * Item description
     * @var string
     */
    public $desc;
    /**
     * Call to action
     * @var array ['label'=>'<label>','url'=>'<url>']
     */
    public $cta = []; 
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
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
            $styles = $this->parseWidgetStyles();
            $this->style = implode(';', $styles['styles']);
            $widget = $this->cloneAsWidget([
                //local settings
                'title'=>$this->title,
                'desc'=>$this->desc,
                'ctaLabel'=>isset($this->cta['label'])?$this->cta['label']:'',
                'ctaUrl'=>isset($this->cta['url'])?$this->cta['url']:'',
                'bgImage'=>$styles['bgImage'],
            ]);
            $this->content = $widget->blockContent;
        }
        else {        
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._imageblock',['element'=>$this],true);
        }
    }    
    /**
     * Extra background image from style
     * And keep others style intact
     */
    protected function parseWidgetStyles()
    {
        $result = ['bgImage'=>null,'styles'=>[]];//assume not found
        $styles = explode(';', $this->style);
        foreach ($styles as $style) {
            if (strpos($style,'background-image')!==false){
                $bgImage = str_replace('background-image:url(','',$style);//take away prefix 
                $bgImage = rtrim($bgImage,')');//trim right
                $bgImage = rtrim(ltrim($bgImage,'"'),'"');//trim left and right double quotes if any
                $result['bgImage'] = $bgImage;
            }
            else
                $result['styles'][] = $style;
        }
        return $result;
    }    
}

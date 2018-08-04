<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridMenuBlock
 *
 * @author kwlok
 */
class SGridMenuBlock extends SGridColumn
{
    public $type = SGridLayout::MENU_BLOCK;
    /**
     * string the column size (1 - 12)
     */
    public $size = 3;//default width
    /**
     * Menu title 
     * @var string
     */
    public $title;
    /**
     * Menu items
     * @var array
     * ['<link>'=>'<label>']
     */
    public $menu = [];
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
        $this->prepareMenu();
        if ($this->modal){
            $widget = $this->cloneAsWidget([
                //local settings
                'title'=>$this->title,
                'menu'=>$this->menu,
            ]);
            $this->content = $widget->blockContent;
        }
        else {
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._menublock',['element'=>$this],true);
        }
    }     
    /**
     * Construct menu
     * Transform $items into data structure that can be used for rendering
     */
    protected function prepareMenu()
    {
        foreach($this->menu as $key => $item){
            //If menu already contains url/label, faster performance (no need to query db)
            if (!isset($item['url']) && !isset($item['label']) && isset($item['type'])){
                if ($item['type']=='page'){
                    if (Helper::isInteger($item['id']))
                        $model = Page::model()->findByPk($item['id']);
                    else
                        $model = Page::model()->locateOwner($this->layout->page->pageOwner)->locatePage($item['id'])->find();

                    if ($model!=null){
                        //add missing url and label
                        $this->menu[$key]['id'] = $model->id;//change to model id
                        $this->menu[$key]['url'] = $model->getUrl(request()->isSecureConnection);
                        if ($this->layout->page->onPreview && !$this->layout->page->edit)//for edit, cannot append preview params int he url as they will affect html block saving
                            $this->menu[$key]['url'] = $this->layout->page->appendExtraQueryParams($model->getUrl(request()->isSecureConnection));                        
                        $this->menu[$key]['label'] = json_decode($model->title,true);
                    }
                    else
                        throw new CException('Menu page "'.$item['id'].'" not found');
                }
            }
        }
        //logTrace(__METHOD__,$this->menu);
    }    
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
Yii::import("common.widgets.SButtonColumn");
/**
 * Description of SPageMenu (a customized version of CMenu)
 *
 * @author kwlok
 */
class SPageMenu extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spagemenu.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spagemenu';    
    /**
     * Menu items
     * <pre>
     * array (
     *     'id'=>'...' //mandatory
     *     'url'=>'...' //mandatory (either url or linkOptions)
     *     'linkOptions'=>'...' //mandatory (either url or linkOptions)
     *     'subscript'=>'...', //optional, default to value of 'id' if not set
     *     'title'=>'...', //optional, default to value of 'id' if not set
     *     'visible'=>'...' //optional, default to true
     * )
     * </pre>
     * @see CMenu $items
     * @var array Menu items
     */
    public $items;    
    /**
     * Indicate if to show subscript; Default to true
     * @var boolean 
     */
    public $showSubscript = true;  
    /**
     * string the css class of the widget
     */
    public $cssClass;       
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->_validate();
        
        if (!isset($this->cssClass))
            $this->cssClass = 'page-menu';
        
        $this->render('index');
    }
    
    private function _validate() 
    {
        if (!is_array($this->items))
            throw new CException(Sii::t('sii','SPageMenu items must be in array form'));            

        foreach ($this->items as $item) {
            if (!isset($item['id']))
                throw new CException(Sii::t('sii','SPageMenu items must have {field}',array('{field}'=>'id')));            
            if (!isset($item['url']) && !isset($item['linkOptions']))
                throw new CException(Sii::t('sii','SPageMenu items must have either url or linkOptions'));            
        }
    }
    
    protected function getMenu()
    {
        $menu = new CList();
        foreach ($this->items as $item) {
            $menu->add(array(
                'label'=>$this->_getMenuItem(
                            $this->_parseLabel($item['id']),
                            $this->showSubscript?(isset($item['subscript'])?$item['subscript']:$item['id']):null, 
                            isset($item['title'])?$item['title']:$item['id']),
                'url'=>isset($item['url'])?$item['url']:'javascript:void(0);',
                'visible'=>isset($item['visible'])?$item['visible']:true, 
                'linkOptions'=>isset($item['linkOptions'])?$item['linkOptions']:null, 
            ));
        }
        return $menu->toArray();
    }
    private function _getMenuItem($label,$subscript,$title)
    {
        return $this->render('_menuitem',array(
                'label'=>$label,
                'subscript'=>$subscript,
                'title'=>$title),
                true); 
    }
    
    private function _parseLabel($id)
    {
        if (Helper::isInteger($id))
            return $id;

        $icon = SButtonColumn::getButtonIcon($id);
        if ($icon!=false)
            return $icon;
        else
            return strtoupper(substr($id, 0, 1));
    }
    
    public static function menuItem($id,$subscript,$title=null)
    {
        $menu = new SPageMenu();
        return $menu->_getMenuItem($menu->_parseLabel($id), $subscript, $title);
    }
    
}


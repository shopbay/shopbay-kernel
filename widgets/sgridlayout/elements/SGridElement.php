<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.SGridLayout");
/**
 * Description of SGridElement
 *
 * @author kwlok
 */
abstract class SGridElement extends CComponent 
{
    /**
     * The element type
     * @var string
     */
    public $type;//element type
    /**
     * string the element name
     */
    public $name;
    /**
     * string the column size (1 - 12)
     */
    public $size = 12;//full width
    /**
     * string the column content
     */
    public $content;
    /**
     * Owner/creator of this element; Refer to SGridLayout or its child class
     */
    public $owner;
    /**
     * SController the controller owing the element
     */
    public $controller;
    /**
     * Element html style
     * @var string 
     */
    public $style;
    /**
     * When true the element is run in page editor and modal form is auto included
     * @var boolean 
     */
    public $modal = false;
    /**
     * When true the element is editable (in page editor) 
     * @see The control are implemented at pagelayouteditor.js
     * @var boolean 
     */
    public $editable = true;
    /**
     * When true the element is deletable (in page editor) 
     * @see The control are implemented at pagelayouteditor.js
     * @var boolean 
     */
    public $deletable = true;
    /**
     * When true the element is moveable (in page editor) 
     * @see The controls are implemented at pagelayouteditor.js
     * @var boolean 
     */
    public $moveable = true;
    /**
     * If the element is locked (not droppable in page editor), it cannot be deleted and edited (even $deletable and $editable is true)
     * @see The controls are implemented at pagelayouteditor.js
     * @var boolean 
     */
    public $locked = false;
    /**
     * Construtor
     * @param type $config
     */
    public function __construct($owner, $controller,$config=[]) 
    {
        $this->owner = $owner;
        $this->controller = $controller;
        if (!empty($config)){
            foreach ($config as $key => $value) {
                if (property_exists($this, $key))
                    $this->$key = $value;
            }
        }
        $this->init();
    }
    /**
     * The init function (called during constructor)
     */
    public function init()
    {
        //for child class to init 
    }
    /**
     * Get element layout
     */
    public function getLayout()
    {
        return $this->owner;
    }    
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
        return $this->controller->renderPartial($this->viewFile,['element'=>$this],true);
    }
    /**
     * Attach element data info
     * @return array
     */
    public function getElementDataArray()
    {
        if ($this->modal)
            return [
                'data-type'=>'sgrid'.$this->type,
                'data-name'=>$this->name,
                'data-locked'=>$this->locked,
                'data-edit'=>$this->editable,
                'data-delete'=>$this->deletable,
                'data-size'=>$this->size,
            ];
        else
            return [];
    }
    /**
     * The view file for rendering
     */
    abstract public function getViewFile();
    /**
     * Clone for edit use
     * @param type $config
     * @return \widgetClass
     */
    public function cloneAsWidget($config=[])
    {
        $widgetClass = get_class($this).'Widget';
        //logTrace(__METHOD__.' ',$widgetClass);
        return new $widgetClass($this->owner, $this->controller, array_merge([
            //default common settings
            'name'=>$this->name,
            'style'=>$this->style,
            'size'=>$this->size,
            'locked'=>$this->locked,
            'editable'=>$this->editable,
            'deletable'=>$this->deletable,
            'moveable'=>$this->moveable,
        ],$config));//$config with same property will override default common settings
    }    
    /**
     * Serialize element value suitable for html transmit
     * Auto tranform back to widget serialization
     * @param array|string $value or $field
     * @return string
     */
    public function serializeValue($field)
    {
        if (is_array($field))//is a value array
            return CHtml::encode(json_encode($field));
        
        //Below are internal object referencing and must transform back to string
        Yii::import('common.modules.themes.widgets.themegridlayout.ThemeGridLayout');
        if (isset($this->{$field}['page']))
            $this->{$field}['page'] = ThemeGridLayout::$widgetProperty.'page';
        if (isset($this->{$field}['theme']))
            $this->{$field}['theme'] = ThemeGridLayout::$widgetProperty.'page_theme';
        return CHtml::encode(json_encode($this->{$field}));
    }    
    /**
     * Deserialize element value suitable for internal element processing
     * Auto tranform back to widget object
     * @param string $field
     * @return string
     */
    public function deserializeValue($field)
    {
        Yii::import('common.modules.themes.widgets.themegridlayout.ThemeGridLayout');
        if (isset($this->{$field}['page']) && $this->{$field}['page']==ThemeGridLayout::$widgetProperty.'page')
            $this->{$field}['page'] = $this->owner->page;
        if (isset($this->{$field}['theme']))
            $this->{$field}['theme'] = $this->owner->pageTheme;
        return $this->{$field};
    }  
    /**
     * Get field language value 
     * @param string $field
     */
    public function getLanguageValue($field,$raw=false)
    {
        if ($field==null)
            return '';
        
        if ($raw && is_array($field))
            return $this->siiMessage($field, $this->owner->locale);
        elseif (is_array($this->{$field}))
            return $this->siiMessage($this->{$field}, $this->owner->locale);
        else
            return $this->{$field};
    }      

    public function getLocales()
    {
        return $this->layout->page->pageOwner->getLanguages();
    }
    
    public function siiField($value)
    {
        return Sii::toArray(array_keys($this->getLocales()), $value);
    }    
    /**
     * Get translation; If locale field not found, fall back to default locale
     * @param type $field
     * @param type $locale
     * @return type
     */
    protected function siiMessage($field,$locale)
    {
        return isset($field[$locale]) ? $field[$locale] : $field[param('LOCALE_DEFAULT')] ;
    }
    
    
}

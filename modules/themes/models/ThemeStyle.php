<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.themes.models.Theme');
/**
 * Description of ThemeStyle
 * @see styles_schema_example.json for schema
 * 
 * @author kwlok
 */
class ThemeStyle extends CComponent 
{
    public $theme;//theme id
    public $group;//theme group
    public $id;//style id
    public $name;//style name
    public $images = [];
    public $css = [];
    public $js = [];
    /**
     * Contructor
     * @param type $id
     * @param type $config
     */
    public function __construct($group, $theme,$id,$config=[])
    {
        $this->group = $group;
        $this->theme = $theme;
        $this->id = $id;
        foreach ($config as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
    }
    
    public function getUniqueId()
    {
        return $this->theme.'_'.$this->id;
    }
    
    public function getName($locale)
    {
        return $this->name[$locale];
    }

    public function getImage($device=null)
    {
        if (!isset($device))
            $device = 'desktop';
        foreach ($this->images as $image) {
            if (isset($image[$device]))
                return $image[$device];
        }
        return null;
    }
    
    public function getCssPathAlias()
    {
        return Tii::getCssPathAlias($this->group, $this->theme,$this->id);
    }

    public function getScriptPathAlias()
    {
        return Tii::getScriptPathAlias($this->group, $this->theme,$this->id);
    }
    
    public function getImagePathAlias()
    {
        return Tii::getImagePathAlias($this->group, $this->theme);
    }
    /**
     * Read theme styles configuration
     * @param type $theme
     * @return type
     */
    public static function config($group, $theme)
    {
        return Tii::getThemeLayout($group, $theme, 'styles.json');
    }
}

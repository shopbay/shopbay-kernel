<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of UserMenuItem
 *
 * @author kwlok
 */
class UserMenuItem 
{
    public $id;
    public $label;
    public $icon;
    public $iconDisplay = true;//or false
    public $iconPlacement = 'left';//or right
    public $url;
    public $onclick;
    public $visible = true;
    public $cssClass;
    public $active = false;//if element is selected
    public $items = [];//sub level menu items
    
    public function __construct($config=[]) 
    {
        if (!empty($config)){
            foreach ($config as $key => $value) {
                if (property_exists($this, $key))
                    $this->$key = $value;
            }
        }
    }    
    /**
     * Array data suitable to be used as input for {@link CMenu}
     * @return array
     */
    public function toArray()
    {
        $label = $this->label;
        $data = [
            'url'=>$this->url,
            'visible'=>$this->visible,
            'itemOptions'=>['class'=>$this->cssClass],
        ];
        
        if ($this->iconDisplay){
            if ($this->iconPlacement=='right')
                $label = $label.$this->icon;
            else
                $label = $this->icon.$label;
        }
        
        $data['label'] = $label;
        $data['active'] = $this->active;
        if ($this->active) 
            $data['itemOptions']['class'] .= ' active';
            
        if (isset($this->onclick))
            $data['linkOptions'] = ['onclick'=>$this->onclick];
        if (isset($this->items))
            $data['items'] = $this->items;

        return $data;        
    }
}
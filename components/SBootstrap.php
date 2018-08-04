<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SBootstrap
 * Usage Guide: 
 * (1) Configure SBootstrap as a component at main.php
 * (2) Run following:
 *     echo Yii::app()->bootstrap->carousel([
 *       'items' => [
 *          // the item contains only the image
 *           '<img src="http://<testdomain>/images/fullimage1.jpg"/>',
 *          // equivalent to the above
 *          ['content' => '<img src="http://<testdomain>/images/fullimage2.jpg"/>'],
 *          // the item contains both the image and the caption
 *          [
 *              'content' => '<img src="http://<testdomain>/images/fullimage3.jpg"/>',
 *              'caption' => '<h4>This is title</h4><p>This is the caption text</p>',
 *              'options' => [''],
 *           ],
 *      ]
 *   ]);
 * @author kwlok
 */
class SBootstrap extends CApplicationComponent
{
    /**
     * Init
     */
    public function init()
    {
        parent::init();
        bootstrapYii2Engine();
        Yii::app()->controller->registerBootstrapAssets();
        importYii2Extension('bootstrap',[
            'BootstrapAsset','BootstrapPluginAsset','BootstrapThemeAsset','Widget',
            'Alert','Carousel','Collapse','Button','ButtonDropdown','ButtonGroup',
            'Dropdown','Modal','Nav','NavBar','Tabs','Html','BaseHtml',
            //yii 2.0.9 support
            'InputWidget','ToggleButtonGroup','BootstrapWidgetTrait','ActiveField','ActiveForm',
        ],'src');//add class folder 'src'
    }  
    /**
     * This magic method auto calls bootstrap widget (generic method)
     * E.g. SBootstrap->carousel($config) will trigger \yii\bootstrap\Carousel($config)
     * E.g. SBootstrap->button($config) will trigger \yii\bootstrap\Button($config)
     * 
     * @param type $name
     * @param type $arguments
     * @return type
     */
    public function __call($name, $arguments)
    {
        $class = '\\yii\\bootstrap\\'.ucfirst($name);
        return $class::widget($arguments[0]);
    }   
    /**
     * Modal widget begin method
     */
    public function beginModal($config)
    {
        \yii\bootstrap\Modal::begin($config);
    }
    /**
     * Modal widget end method
     * Modal has problem working with existing UI framework due to the positioning of the parent containers. 
     * Tweak: We "move" your modal out from these containers before displaying it. 
     */
    public function endModal($id)
    {
        \yii\bootstrap\Modal::end();
        cs()->registerScript('modal_'.$id,'$("#'.$id.'").appendTo("body");');
    }  
    /**
     * NavBar widget begin method
     */
    public function beginNavBar($config)
    {
        \yii\bootstrap\NavBar::begin($config);
    }
    /**
     * NavBar widget end method
     */
    public function endNavBar()
    {
        \yii\bootstrap\NavBar::end();
    }    
    
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of STheme
 * Customized from CTheme as theme files are resided inside respective module and not quite follow Yii theme file path convention
 * 
 * @property string $name Theme name.
 * @property string $baseUrl The relative URL to the theme folder (without ending slash).
 * @property string $basePath The file path to the theme folder.
 * 
 * @author kwlok
 */
abstract class STheme extends CTheme 
{ 
    /**
     * OVERRIDEN
     * 
     * @return string the path for controller views. Defaults to path alias 'modules.themes.<theme_name>.views'.
     */
    public function getViewPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.$this->name.DIRECTORY_SEPARATOR.'views';
    }
    /**
     * OVERRIDEN
     * 
     * Finds the view file for the specified controller's view.
     * @param CController $controller the controller
     * @param string $viewName the view name
     * @return string the view file path. False if the file does not exist.
     */
    public function getViewFile($controller,$viewName)
    {
        return $controller->resolveViewFile($viewName,$this->getViewPath().'/'.$controller->getId(),$this->getViewPath());
    }
    /**
     * OVERRIDEN
     * 
     * Finds the layout file for the specified controller's layout.
     * @param CController $controller the controller
     * @param string $layoutName the layout name
     * @return string the layout file path. False if the file does not exist.
     */
    public function getLayoutFile($controller,$layoutName)
    {
        return $controller->resolveViewFile($layoutName,$this->getViewPath().'/layouts',$this->getViewPath());
    }
    /**
     * Get theme config
     */
    public function getConfig($theme=null,$scope=null)
    {
        if ($theme===null&&$scope===null){
            return include $this->getDatasource();
        }
        elseif (isset($theme)&&$scope===null){
            return $this->getConfig()[$theme];
        }
        else {
            $config = $this->getConfig();
            $themeConfig = new CMap();
            foreach ($config as $key => $value) {
                foreach ($value as $subkey => $data) {
                    if ($subkey==$scope)
                        $themeConfig->add($key, $data);
                }
            }
            return $themeConfig->itemAt($theme);
        }
    }
    
}

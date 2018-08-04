<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopPageTheme
 * Customized from CTheme as theme files are resided inside "shops" module and not quite follow Yii theme file path convention
 * 
 * @author kwlok
 */
class ShopPageTheme extends STheme 
{ 
    /**
     * Constructor.
     * @param string $name name of the theme
     * @param string $basePath base theme path
     * @param string $baseUrl base theme URL
     */
    public function __construct($name=null)
    {
        if (!isset($name))
            $name = Tii::defaultTheme();
        $basePath = param('SHOP_THEME_BASEPATH');
        //$basePath = Yii::getPathOfAlias('common.modules.shops.themes');
        $baseUrl = '/themes';
        parent::__construct($name, $basePath, $baseUrl);
    }
    
}

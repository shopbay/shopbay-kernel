<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.themes.models.Theme');
/**
 * Shopbay theming framework helper class 
 * 
 * Tii is the code name of the theming framework
 * 
 * @author kwlok
 */
class Tii 
{
    CONST GROUP_SHOP = 'shop';
    CONST STYLE_DEFAULT = 'default';//default style id
    CONST STYLE_COMMON  = 'common';//common style (internal style to share among styles)
    /**
     * @return string Get default theme
     */
    public static function defaultTheme($group=Tii::GROUP_SHOP)
    {
        return Config::getSystemSetting($group.'_theme_default');
    }
    /**
     * @return string Get default theme style
     */
    public static function defaultStyle()
    {
        return Tii::STYLE_DEFAULT;
    }
    /**
     * Get theme path alias which stores all the theme assets files
     * e.g. shopthemes
     * 
     * @return string
     */
    protected static function getThemePathAlias($group)
    {
        return $group.'themes';
    }    
    /**
     * Get theme layout configuration
     * @return array
     */
    public static function getThemeLayout($group,$theme,$filename)
    {
        $basepath = Yii::getPathOfAlias(static::getThemePathAlias($group).'.'.$theme.'.views.layouts');
        return json_decode(file_get_contents($basepath.DIRECTORY_SEPARATOR.$filename),true);
    }    
    /**
     * Get path alias which stores all the theme css files
     * @return string
     */
    public static function getCssPathAlias($group,$theme,$style)
    {
        return static::getThemePathAlias($group).'.'.$theme.'.assets.css.'.$style;
    }
    /**
     * Get path alias which stores all the theme javscript files
     * @return string
     */
    public static function getScriptPathAlias($group,$theme,$style)
    {
        return static::getThemePathAlias($group).'.'.$theme.'.assets.js.'.$style;
    }
    /**
     * Get path alias which stores all the theme image files
     * @return string
     */
    public static function getImagePathAlias($group,$theme)
    {
        return static::getThemePathAlias($group).'.'.$theme.'.assets.images';
    }
    /**
     * Search themes
     * @return CActiveDataProvider
     */
    public static function searchThemes($group,$status=Process::THEME_ONLINE)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['theme_group'=>$group]);
        $criteria->addColumnCondition(['status'=>$status]);
        return new CActiveDataProvider('Theme',[
            'criteria'=>$criteria,
            'pagination'=>[
                'pageSize'=>Config::getSystemSetting('record_per_page'),
            ],
            'sort'=>false,
        ]);
    } 
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.themes.widgets.themegridlayout.ThemeGridLayout");
Yii::import("common.modules.themes.widgets.themegridlayout.ThemeOffCanvasMenu");
/**
 * Description of ThemeOffCanvasGridLayout
 * This layout extends ThemeGridLayout to support off canvas menu
 * 
 * @author kwlok
 */
abstract class ThemeOffCanvasGridLayout extends ThemeGridLayout
{
    use ThemeOffCanvasMenu;
}

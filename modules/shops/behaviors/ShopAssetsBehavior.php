<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopAssetsBehavior
 *
 * @author kwlok
 */
class ShopAssetsBehavior extends CBehavior 
{
    /**
     * Set shop assets path alias
     */
    public function setShopAssetsPathAlias()
    {
        //set shop resources path alias
        Yii::setPathOfAlias('shopwidgets', param('SHOP_WIDGET_BASEPATH'));
        logTrace(__METHOD__.' ',Yii::getPathOfAlias('shopwidgets'));
        Yii::setPathOfAlias('shopthemes', param('SHOP_THEME_BASEPATH'));
        logTrace(__METHOD__.' ',Yii::getPathOfAlias('shopthemes'));
    }    
}

<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.behaviors.ShopParentBehavior');
/**
 * Description of NewsBehavior
 *
 * @author kwlok
 */
class NewsBehavior extends ShopParentBehavior 
{
    public function getBaseUrl($secure=false)
    {
        return $this->getOwner()->getShopUrl($secure).'/news';
    }
}
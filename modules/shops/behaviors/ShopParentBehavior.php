<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopParentBehavior
 *
 * @author kwlok
 */
class ShopParentBehavior extends CActiveRecordBehavior 
{
    /**
     * This is public accessible shop url of this product
     * @return type
     */
    public function getShopUrl($secure=false)
    {
        return $this->getOwner()->shop->getUrl($secure);
    }

    public function getShopsArray($locale=null)
    {
        $data = new CMap();
        foreach (Shop::model()->mine()->approved()->findAll() as $model) {
            $data->add($model->id,$model->displayLanguageValue('name',$locale));
        }
        return $data->toArray();
    }     
}
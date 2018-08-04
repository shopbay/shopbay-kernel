<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.behaviors.ShopParentBehavior');
/**
 * Description of BrandBehavior
 *
 * @author kwlok
 */
class BrandBehavior extends ShopParentBehavior 
{
    public function getBaseUrl($secure=false)
    {
        return $this->getOwner()->getShopUrl($secure).'/brand';
    }
    /**
     * This is public accessible url
     * @return type
     */
    public function getUrl($secure=false)
    {
        return $this->getBaseUrl($secure).'/'.$this->getOwner()->slug;
    }

    public function toMenuArray($locale)
    {
        return [
            $this->getOwner()->displayLanguageValue('name',$locale) => $this->getOwner()->url,
        ];
    }    
    /*
     * @return if in skipslug scenario
     */
    public function getIsSkipSlugScenario()
    {
        return $this->getOwner()->getScenario()==Brand::model()->getSkipSlugScenario();
    }
    /**
     * Find model by url slug
     * @param type $shop
     * @param type $slug
     * @return mixed Return CActiveRecord if found; Return false if not found
     */
    public function findModelBySlug($shop,$slug)
    {
        $modelClass = get_class($this->getOwner());

        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('slug'=>$slug,'shop_id'=>$shop));
        $model = $modelClass::model()->find($criteria);
        if ($model===null){
            logError(__METHOD__." $modelClass not found",$criteria);
            return false;
        }
        else
            return $model;
    }
}

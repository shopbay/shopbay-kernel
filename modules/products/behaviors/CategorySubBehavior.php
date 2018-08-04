<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.products.behaviors.ProductBaseBehavior");
/**
 * Description of CategorySubBehavior
 *
 * @author kwlok
 */
class CategorySubBehavior extends ProductBaseBehavior 
{
    /**
     * This is base url for category
     * @return type
     */
    public function getBaseUrl($secure=false)
    {
        return $this->getOwner()->category->getUrl($secure);
    }     
    /**
     * This is public accessible url 
     * @return type
     */
    public function getUrl($secure=false)
    {
        return $this->getOwner()->getBaseUrl($secure).'/'.$this->getOwner()->slug;
    }   
    
    public function toMenuArray($locale)
    {
        return array_merge($this->getOwner()->category->toMenuArray($locale),[
            $this->getOwner()->displayLanguageValue('name',$locale) => $this->getOwner()->url,
        ]);
    }    
    /*
     * @return if in skipslug scenario
     */
    public function getIsSkipSlugScenario()
    {
        return $this->getOwner()->getScenario()==CategorySub::model()->getSkipSlugScenario();
    }  
    /**
     * Find model by url slug
     * @param type $category
     * @param type $slug
     * @return mixed Return CActiveRecord if found; Return false if not found
     */
    public function findModelBySlug($category,$slug)
    {
        $modelClass = get_class($this->getOwner());
        
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('slug'=>$slug,'category_id'=>$category));
        $model = $modelClass::model()->find($criteria);
        if ($model===null){
            logError(__METHOD__." $modelClass not found",$criteria);
            return false;
        }
        else
            return $model;
    }    
    
}

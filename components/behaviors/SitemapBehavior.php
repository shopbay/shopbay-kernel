<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SitemapBehavior
 *
 * @author kwlok
 */
class SitemapBehavior extends CActiveRecordBehavior
{
    /**
     * @var string The name of the attribute to page owner. Defaults to 'shop_id'
     */
    public $pageOwnerAttribute = 'shop_id';
    /**
     * @var array The extra model scopes for filtering
     */
    public $scopes = [];
    /**
     * @var string The extra sitemap condition on top of shop_id
     */
    public $extraCondition;
    /**
     * @var string The sort condition
     */
    public $sort;
    /**
     * Sitemap finder method
     * @param $ownerId
     * @return CComponent
     */
    public function sitemap($ownerId,$ownerType=null)
    {
        $finder = $this->getOwner();
        if (!empty($this->scopes)){
            foreach ($this->scopes as $scope) {
                $finder = $finder->{$scope}();
            }
        }

        $criteria = new CDbCriteria();
        $condition = $this->pageOwnerAttribute.'='.$ownerId;
        if (isset($ownerType))
            $condition .= ' AND owner_type=\''.$ownerType.'\'';
        
        if (isset($this->extraCondition))
            $condition .= ' AND '.$this->extraCondition;
        
        $criteria->addCondition($condition);

        if (isset($this->sort))
            $criteria->order = $this->sort;

        logTrace(__METHOD__.' criteria',$criteria);
        $finder->getDbCriteria()->mergeWith($criteria);

        return $finder;
    }
}
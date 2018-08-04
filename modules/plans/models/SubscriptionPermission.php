<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_rbac_item_child".
 *
 * @author kwlok
 */
class SubscriptionPermission extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_rbac_item_child';
    }
    /**
     * @return CActiveRecord 
     */
    public function locatePlan($plan) 
    {
        $condition = 'parent = \''.$plan.'\'';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        return $this;
    }  
    /**
     * @return CActiveRecord 
     */
    public function locateSubscription($plan,$subscription) 
    {
        $condition = 'parent = \''.$plan.'\' AND child LIKE \'%'.$subscription.'%\'';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        return $this;
    }   
    /**
     * Fuzzy search service on non-absolute permission text search 
     * @param type $plan
     * @param type $service
     * @return type
     */
    public function fuzzySearch($plan,$service,$pattern=Feature::LIMIT_PATTERN)
    {
        //remove pattern 
        $fuzzy = rtrim($service,$pattern);
        //start search
        $permission = $this->locateSubscription($plan,$fuzzy)->find();
        if ($permission!=null){
            logTrace(__METHOD__." permission FOUND using pattern '$service'",$permission->attributes);
            return $permission->child;//change subscription to exact name
        }
        else {
            logTrace(__METHOD__." permission NOT FOUND using pattern '$service'");
            return $service;//return original value if not found
        }
    }
}

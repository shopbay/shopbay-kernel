<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_rbac_assignment".
 *
 * @author kwlok
 */
class SubscriptionAssignment extends CActiveRecord
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
        return 's_rbac_assignment';
    }
    /**
     * @return CActiveRecord 
     */
    public function locateRbac($userId,$plan) 
    {
        $condition = 'user_id = \''.$userId.'\' AND item_name = \''.$plan.'\'';     
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        return $this;
    }  

}

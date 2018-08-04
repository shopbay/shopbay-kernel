<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Based search model active record
 *
 * @author kwlok
 */
class SearchModel extends \yii\elasticsearch\ActiveRecord
{
    /*
     * Active record model class name
     */
    public $arClass;
    /*
     * Active record model class name
     */
    public $activeStatus = 'ON;';
    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        // path mapping for '_id' is setup to field 'id'
        return [];
    }
    /**
     * Get active record model based on primary key
     * @param string $arClass Active record class name
     * @return ActiveRecord locate db Active Record record based on primary key (can be in other database, e.g. redis or sql)
     * @throws CException
     */
    public function getModel($arClass=null)
    {
        if ($arClass==null){
            if ($this->arClass==null)
                throw new CException('Search active record class not defined.');
            $arClass=$this->arClass;
        }
        return $arClass::model()->findByPk($this->primaryKey);
    }
    /**
     * Assign attributes values directly from mapped models
     * This method is called before index is saved into elasticsearch
     */
    public function assignAttributes($mappedModel)
    {
        foreach ($this->attributes() as $attribute) {
            if ($attribute=='id'){
                $this->primaryKey = $mappedModel->id; 
            }
            else if ($attribute=='status'){
                //It seems setting field $status as original valu e.g. PRD;ON; PRD;OFF; does not work for filtered query (term filter)
                //Hence, here convert all XX;ON; status to 1 (active), and XX;OFF; to 0 (inactive)
                $pos = strpos($mappedModel->status, $this->activeStatus);
                if ($pos === false) {
                   $this->$attribute = SearchFilter::INACTIVE;//not found; status OFF; convert to INACTIVE
                } else {
                   $this->$attribute = SearchFilter::ACTIVE;//found; status ON; convert to ACTIVE
                }                
            }
            else
                $this->$attribute = $mappedModel->$attribute; 
        }
    }     
    /**
     * Set some old attributes so that it is not a 'new' record
     */
    public function setAsOldRecord() 
    {
        $this->setOldAttributes(array('id'=>$this->primaryKey));
    }
  
}
<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ThemeParams
 *
 * @author kwlok
 */
trait ThemeParams 
{
    protected function getParamsAttribute()
    {
        return 'params';
    }     
    /**
     * Finder method for param
     * @return type
     */
    public function withParam($field,$value) 
    {
        $criteria = new CDbCriteria();
        $criteria->compare($this->getParamsAttribute(), '"'.$field.'":"'.$value.'"', true);
        //logTrace(__METHOD__.' criteria',$criteria);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }      
    /**
     * Get param setting 
     * @param type $field
     * @return param value
     */
    public function getParam($field)
    {
        $params = $this->getParamsData();
        if (!empty($params))
            return isset($params[$field])?$params[$field]:null;
        else
            return null;
    }
    /**
     * Get all params data
     * @return array 
     */
    public function getParamsData()
    {
        $data = json_decode($this->{$this->getParamsAttribute()},true);
        return $data!=null ? $data : [];
    }   
    
    public function saveParamField($field,$data)
    {
        $params = $this->getParamsData();
        $params[$field] = $data;
        $this->{$this->getParamsAttribute()} = json_encode($params);
        if (!$this->validate()){
            logError(__METHOD__.' Failed to save theme param '.$field,$this->errors);
            throw new CException(__CLASS__.' Failed to save theme param '.$field);
        }
        $this->save();
        logTrace(__METHOD__." $field saved ok",json_encode($params));
    }
    
    public function saveParams($params=[])
    {
        foreach ($params as $field => $data) {
            $this->saveParamField($field, $data);
        }
    }
    
}

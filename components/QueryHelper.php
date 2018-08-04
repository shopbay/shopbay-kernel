<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Helper class to assist SQL query, search or other DB operations
 * @author kwlok
 */
class QueryHelper 
{
    /**
     * Search a locale name in json_encoded data 
     * @param type $input
     * @return string
     */
    public static function parseLocaleNameSearch($criteria,$attribute,$value)
    {
        if (!empty($value)){
            $jsonToken = json_encode($value);
            $criteria->compare($attribute, trim($jsonToken, '"'),true);//trim away begining and ending quotes after json_encode for wild card search
        }
        return $criteria;
    }    
    /**
     * Search a string in json_encoded data 
     * @param type $input
     * @return string
     */
    public static function parseJsonStringSearch($criteria,$attribute,$value,$quotes='"')
    {
        if (!empty($value)){
            $jsonToken = '":'.$quotes.$value;//embrace open quotes to ensure only search into json_encoded for particular field only
            $criteria->compare($attribute,$jsonToken,true);
        }
        return $criteria;
    }    
    /**
     * Prepare criteria for date time query
     * @param type $criteria Initial criteria
     * @param type $value
     * @return CDbCriteria 
     */
    public static function prepareDatetimeCriteria($criteria,$attribute,$value)
    {
        if (!empty($value)) {
            $datetime = self::parseDatetimeSearch($value);
            if ($datetime!=false){
                if ($datetime['op']==SPageFilterForm::OP_EQUAL)
                    $criteria->addBetweenCondition($attribute, $datetime['start_date'], $datetime['end_date']);
                elseif ($datetime['op']==SPageFilterForm::OP_NOT_EQUAL){
                    $criteria->compare($attribute,SPageFilterForm::OP_LESS_THAN.$datetime['start_date'],false,'OR');
                    $criteria->compare($attribute,SPageFilterForm::OP_GREATER_THAN.$datetime['end_date'],false,'OR');
                }
                elseif (in_array($datetime['op'],[SPageFilterForm::OP_LAST_24_HOURS,SPageFilterForm::OP_LAST_7_DAYS,SPageFilterForm::OP_LAST_30_DAYS,SPageFilterForm::OP_LAST_90_DAYS])){
                    $criteria->compare($attribute,SPageFilterForm::OP_GREATER_THAN_OR_EQUAL.$datetime['date']);
                }
                else {
                    $criteria->compare($attribute,$datetime['op'].$datetime['date']);
                }
            }
        }
        return $criteria;
    }
    /**
     * Used for both SQL date and time search
     * @return array 
     */
    public static function parseDatetimeSearch($input)
    {
        $op = '';//operator
        $value= '';
        if(preg_match(self::getPageFilterOpsRegExp(),$input,$matches)){
            $op=$matches[1];
            $value=$matches[2];
        }
        logTrace(__METHOD__.' matches',$matches);
        
        //operator conversion
        if ($op=='')
            $op=SPageFilterForm::OP_EQUAL;//convert to "equal"
        
        $timeComponent = false;
        $date = SDateTime::createFromFormat('Y-m-d',$value);
        if ($date === false){//failure, try second format with time
            $date = SDateTime::createFromFormat('Y-m-d H:i',$value);
            $timeComponent = true;
        }

        if ($date === false){//cannot parse the datetime
            logError(__METHOD__.' fail to parse datetime ',$value);
            return false;
        }
        
        if (!$timeComponent) {//when no time component is present, set the time component
            $date = self::parseTimeComponent($date, $op);
        }
        
        if ($op==SPageFilterForm::OP_EQUAL || $op==SPageFilterForm::OP_NOT_EQUAL){
            //$date_end is used when operator is "equal" or "not equal"
            $date_timestamp = $date->getTimeStamp();//a timestamp 
            $end_date = new DateTime("@$date_timestamp"); 
            $end_date->add(new DateInterval('P1D'));//one day diff
            $result = [
                'op'=>$op,
                'start_date'=>$date->getTimeStamp(),
                'start_date_string'=>$date->format('Y-m-d H:i'),
                'end_date'=>$end_date->getTimeStamp(),
                'end_date_string'=>$end_date->format('Y-m-d H:i'),
            ];
        }
        else {
            $result = [
                'op'=>$op,
                'date'=>$date->getTimeStamp(),
                'date_string'=>$date->format('Y-m-d H:i'),
            ];
        }
        
        logTrace(__METHOD__.' result',$result);
        return $result;
    }
    /**
     * Prepare criteria for date time query
     * @param type $criteria Initial criteria
     * @param type $value
     * @return CDbCriteria 
     */
    public static function prepareDateCriteria($criteria,$attribute,$value)
    {
        if (!empty($value)){
            $date = self::parseDateSearch($value);
            if ($date!=false)
                $criteria->compare($attribute,$date['op'].$date['date_string']);
        }
        return $criteria;
    }    
    /**
     * Used for pure date search
     * @return $date->format('Y-m-d')
     */
    public static function parseDateSearch($input)
    {
        $op = '';
        $value= '';
        if(preg_match(self::getPageFilterOpsRegExp(),$input,$matches)){
            $op = $matches[1];
            $value = $matches[2];
        }
        logTrace(__METHOD__.' matches',$matches);

        if ($op=='')
            $op=SPageFilterForm::OP_EQUAL;//convert to "equal"
        
        $date = DateTime::createFromFormat('Y-m-d',$value);
        
        if ($date === false){//cannot parse the datetime
            logError(__METHOD__.' fail to parse datetime ',$value);
            return false;
        }
        
        $date = self::parseTimeComponent($date, $op);
        
        return [
            'op'=>$op,
            'date'=>$date->getTimeStamp(),
            'date_string'=>$date->format('Y-m-d'),
        ];
    }    
    /**
     * Search status by display text
     * @param type $input
     * @return string
     */
    public static function parseStatusSearch($input,$statusFlag=null)
    {
        if ($input==null || $input=='')
            return null;//nothing found
        
        $condition = 'UCASE(text)=\''.strtoupper($input).'\'';
        if ($statusFlag!=null)
            $condition .= ' AND name like \''.$statusFlag.'%\'';//check starting with
            
        $criteria=new CDbCriteria;
        $criteria->select = 'name';
        $criteria->condition = $condition;
        logTrace(__METHOD__.' criteria',$criteria);
        $process = Process::model()->find($criteria);
        if ($process==null)
            return null;//not record found
        
        return $process->name;
    }
    /**
     * Decides the time component when it is not presented
     * @param type $op
     */
    protected static function parseTimeComponent($date,$op)
    {
        logTrace(__METHOD__.' before: '.$date->format('Y-m-d H:i:s'));
            //cater for all the operators scenario            
        if ($op==SPageFilterForm::OP_EQUAL || $op==SPageFilterForm::OP_NOT_EQUAL)
            $date->setTime(0,0,0);
        elseif ($op==SPageFilterForm::OP_GREATER_THAN || $op==SPageFilterForm::OP_LESS_THAN_OR_EQUAL)
            $date->setTime(23,59,59);
        elseif ($op==SPageFilterForm::OP_LESS_THAN || $op==SPageFilterForm::OP_GREATER_THAN_OR_EQUAL)
            $date->setTime(0,0,0);
        elseif ($op==SPageFilterForm::OP_LAST_7_DAYS || $op==SPageFilterForm::OP_LAST_30_DAYS || $op==SPageFilterForm::OP_LAST_90_DAYS)
            $date->setTime(0,0,0);
        elseif ($op==SPageFilterForm::OP_LAST_24_HOURS){
            //no need to set time; follows current time
        }
        logTrace(__METHOD__.' after: '.$date->format('Y-m-d H:i:s'));
        return $date;
    }
    /**
     * Helper method to construct criteria "IN" condition
     * @param type $field
     * @param type $values
     * @return string
     */
    public static function constructInCondition($field, $values)
    {
        if (count($values)==0)
            return $field.'=-9999';//nothing found

        $condition = $field.' IN (';
        $cnt=0;
        foreach ($values as $value){
            $condition .= $value;
            $cnt++;
            if ($cnt!=count($values))
                $condition .= ',';
        }
        $condition .= ')';
        return $condition;
    }    
    /**
     * Helper method to construct criteria "NOT IN" condition
     * @param type $field
     * @param type $values
     * @return string
     */    
    public static function constructNotInCondition($field, $values)
    {
        if (count($values)==0)
            return $field.'=-9999';//nothing found

        $condition = $field.' NOT IN (';
        $cnt=0;
        foreach ($values as $value){
            $condition .= '\''.$value.'\'';
            $cnt++;
            if ($cnt!=count($values))
                $condition .= ',';
        }
        $condition .= ')';
        return $condition;
    }    
    /**
     * The order of operators are important
     * @see SPageFilterForm::getOperators()
     * @return type
     */
    public static function getPageFilterOps()
    {
        return array_keys(SPageFilterForm::getOperators());
    }
    /**
     * Example regular express string is: /^(?:\s*(<>|<=|>=|<|>|=|~))?(.*)$/
     */    
    public static function getPageFilterOpsRegExp()
    {
        $regExpStr = '/^(?:\s*(';//begin part
        foreach (self::getPageFilterOps() as $op) {
            $regExpStr .= $op.'|';//middle parts
        }
        $regExpStr = rtrim($regExpStr, '|');//remove the last '|'
        $regExpStr .= '))?(.*)$/';//end part
        logTrace(__METHOD__.' result',$regExpStr);
        return $regExpStr;
    }
}

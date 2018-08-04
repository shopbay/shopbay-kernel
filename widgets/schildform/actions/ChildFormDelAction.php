<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ChildFormDelAction
 *
 * @author kwlok
 */
class ChildFormDelAction extends CAction 
{
    /**
     * Name of the session state variable to temporary store attribut objects. Defaults to 'undefined'
     * @var string
     */
    public $stateVariable = 'undefined';
    /**
     * A callback method to invoke before deleting child form 
     * If set, it is always expect passing in argument to be $key itself, and 
     * returning true if no errors; If have errors, returning error message in array
     * 
     * Example: At controller side, there is a method callback "beforeDeleteChildform($key)
     * 
     * public function beforeDeleteChildform($key)
     * {
     *     if (<error>)
     *        return array('status'=>'failure','key'=>$key,'message'=><error message>);
     *     else
     *        return array('status'=>'success');
     * }
     * @var array
     */
    public $beforeDelete;
    
    public function init( ) 
    {
        parent::init();
    }
    /**
     * Delete option form and clear session
     * @param integer $key the ID of session object to delete
     */
    public function run() 
    {
        if(isset($_GET['key'])) {
            header('Content-type: application/json');
            $key = $_GET['key'];
            switch ($key) {
                case 'all':
                    SActiveSession::clear($this->stateVariable);
                    echo CJSON::encode(array('status'=>'success'));
                    break;
                default:
                    //check if there is beforeDelete method
                    if (isset($this->beforeDelete)){
                        $result = $this->controller->{$this->beforeDelete}($key);
                        //logTrace(__METHOD__.' beforeDelete',$result);
                        if (is_array($result) && isset($result['status']) && $result['status']=='failure'){
                            echo CJSON::encode($result);
                            break;
                        }
                    }
                    //proceed normal
                    if (SActiveSession::remove($this->stateVariable,$key))
                        echo CJSON::encode(array('status'=>'success','key'=>$key,'count'=>SActiveSession::count($this->stateVariable)));
                    else
                        echo CJSON::encode(array('status'=>'failure','key'=>$key,'message'=>Sii::t('sii','Key not found.')));
                    break;
            }
            Yii::app()->end();      
        }
        throwError403(Sii::t('sii','Unauthorized Access'));
    }  
}
<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AddressStateGetAction
 *
 * @author kwlok
 */
class AddressStateGetAction extends CAction 
{
    /**
     * Retrieve state list based on country code
     */
    public function run() 
    {
        if (isset($_GET['country'])){
            $states = SLocale::getStates($_GET['country']);
            header('Content-type: application/json');
            echo CJSON::encode($states);
            Yii::app()->end();                
        }
        else
            throwError403(Sii::t('sii','Unauthorized Access'));        
    }  
}
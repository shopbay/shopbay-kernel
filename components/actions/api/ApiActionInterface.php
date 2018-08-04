<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 *
 * @author kwlok
 */
interface ApiActionInterface 
{
    /**
     * Init action
     */
    public function init();
    /**
     * Run in "api" mode 
     */
    public function callApi();
    /**
     * A method to handle when http code is between 200 and 300
     * @param type $response
     */
    public function onSuccess($response,$httpCode);
    /**
     * A method to handle when http code is not between 200 and 300
     * @param type $response
     */
    public function onError($error,$httpCode);
}

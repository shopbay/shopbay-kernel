<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of AdminController
 *
 * @author kwlok
 */
class AdminController extends TaskBaseController 
{     
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelFilter = null;//for now no need filter
    }    
    public function actionIndex()
    {
        $this->render('index',array('role'=>Role::ADMINISTRATOR));
    }    
}
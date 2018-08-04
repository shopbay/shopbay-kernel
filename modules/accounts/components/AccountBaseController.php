<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.simagemanager.controllers.ImageControllerTrait');
/**
 * Description of AccountBaseController
 *
 * @author kwlok
 */
class AccountBaseController extends AuthenticatedController 
{
    use ImageControllerTrait;
    
    protected $modelType = 'undefined';
    
    public function init()
    {
        parent::init();
        //-----------------
        // @see ImageControllerTrait
        $this->imageStateVariable = SActiveSession::ACCOUNT_IMAGE; 
    }        
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * @see ImageControllerTrait::runBeforeAction()
     */
    protected function beforeAction($action)
    {
        return $this->runBeforeAction($action);
    } 
}
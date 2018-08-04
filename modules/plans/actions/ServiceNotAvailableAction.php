<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ServiceNotAvailableAction
 *
 * @author kwlok
 */
class ServiceNotAvailableAction extends CAction 
{
    public $flashId;
    public $breadcrumbs;
    public $pageHeading;
    public $shopModel;
    public $viewFile = 'plans.views.subscription.service_not_available';
    
    public function run() 
    {
        $this->controller->render($this->viewFile,[
            'breadcrumbs'=>$this->breadcrumbs,
            'heading'=>$this->pageHeading,
            'flashId'=>$this->flashId,
            'sidebar'=>$this->getSidebar(),
        ]);  
    }
    
    public function getSidebar()
    {
        if (isset($this->shopModel))
            return [
                SPageLayout::COLUMN_LEFT=>[
                    'content'=>$this->controller->renderView('shops.sidebar',array('model'=>$this->shopModel),true),
                    'cssClass'=>  SPageLayout::WIDTH_05PERCENT,
                ],
            ];  
        else
            return null;
    }
}
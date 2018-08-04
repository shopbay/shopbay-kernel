<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WizardControllerBehavior
 *
 * @author kwlok
 */
class WizardControllerBehavior extends CBehavior 
{    
    public $appId = 'undefined';
    
    public function loadWizards($flash,$user,$context=null,$profile=null)
    {
        if (isset($flash)){
            $flash = Yii::app()->serviceManager->getWizardManager()->runWizard($this->_wizardCallerId(),$user,$flash,$context,$profile);
            return $flash;
        }
        else
            return [];
    }
        
    public function getMerchantWizard()
    {
        Yii::import('common.modules.help.wizards.merchant.MerchantWizard');
        return new MerchantWizard(MerchantWizardProfile::HELP);;
    }

    private function _wizardCallerId()
    {
        if ($this->getOwner()->action!=null)
            $caller = $this->appId.':'.$this->getOwner()->uniqueId.'/'.$this->getOwner()->action->id;
        else 
            $caller = 'unknown';
        
        logTrace(__METHOD__,$caller);
        return $caller;
    }
   
}

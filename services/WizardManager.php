<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.models.Wizard');
Yii::import('common.modules.help.wizards.account.*');
Yii::import('common.modules.help.wizards.merchant.*');
Yii::import('common.modules.help.wizards.shop.*');
Yii::import('common.modules.help.wizards.product.*');
Yii::import('common.modules.help.wizards.shipping.*');
Yii::import('common.modules.help.wizards.inventory.*');
Yii::import('common.modules.help.wizards.workflow.*');
/**
 * Description of WizardManager
 *
 * @author kwlok
 */
class WizardManager extends ServiceManager 
{
    /**
     * Run wizard to get adivces
     * @param type $caller
     * @param type $user
     * @param type $flash
     * @param type $context
     * @param type $profile Wizard profile as filter
     * @return type
     */
    public function runWizard($caller,$user,$flash,$context=null,$profile=null)
    {
        foreach ($this->_findWizards($caller,$profile) as $wizard) {
            $wizardClass = $wizard->name;
            if (is_object($context) && $context instanceof Product ){//expect one product
                logTrace(__METHOD__.' case product',$wizard->attributes);
                $wizardInstance = new $wizardClass($context,$wizard->profile);
            } 
            elseif (is_object($context) && $context instanceof Shop){//expect one shop
                logTrace(__METHOD__.' case shop',$wizard->attributes);
                $wizardInstance = new $wizardClass($context,$wizard->profile);
            }
            elseif ($wizard->name=='WorkflowWizard'){
                logTrace(__METHOD__.' case workflow', $wizard->attributes);
                $app = explode(':',$caller);//extract app id
                $env = $this->_findEnv($app[0]);
                logTrace(__METHOD__.' env',$env);
                $wizardInstance = new $wizardClass($wizard->profile,$env['role'],$env['modelFilter']);
            }
            else {
                logTrace(__METHOD__.' case default', $wizard->attributes);
                $wizardInstance = new $wizardClass($wizard->profile);
            }
            $flash = $this->_mergeFlash($caller,$user,$wizardInstance,$flash);
        }
        return $flash;
    }    
    /**
     * Flash merge (including permission check)
     * @param type $caller
     * @param type $user
     * @param type $wizard
     * @param type $flash
     * @return type
     */
    private function _mergeFlash($caller,$user,$wizard,$flash)
    {
        if ($this->_validateWizard($caller, $wizard->getProfileClass())){
            if (is_scalar($flash))
                $flash = [$flash];//convert into array
            $extraFlash = $wizard->generateFlash($user);
            if ($extraFlash!=false){
                if (is_array($flash))
                    return array_merge([$extraFlash],$flash);
                else
                    return array($flash,$extraFlash);
            }
            else
                return $flash;
        }
        else
            return $flash;
    }
    /**
     * Validation rules to run wizard
     * TODO: Can include wizard display schedule, and many other features
     * 
     * @param type $caller
     * @param type $profile
     * @return boolean True or False
     */
    private function _validateWizard($caller, $profile)
    {
        return Wizard::model()->profile($caller,$profile)->exists();
    }
    /**
     * Find wizards to run wizard
     * 
     * @param type $caller
     * @param type $profile Wizard profile as filter
     * @return boolean True or False
     */
    private function _findWizards($caller,$profile=null)
    {
        $finder = Wizard::model()->caller($caller);
        if (isset($profile))
            $finder = Wizard::model()->profile($caller,$profile);
        return $finder->findAll();
    }    
    /**
     * Find wizard environment (which app it is running in)
     * @param type $app
     * @return array
     * @throws CException
     */
    private function _findEnv($app)
    {
        switch ($app) {
            case 'customer'://refer to params.php WIZARD_APP_ID
            case 'shop':
                $role = Role::CUSTOMER;
                $modelFilter = 'mine';
                break;
            case 'merchant':
                $role = Role::MERCHANT;
                $modelFilter = 'merchant';
                break;
            case 'admin':
                $role = Role::ADMINISTRATOR;
                $modelFilter = null;
                break;
            default:
                throw new CException(Sii::t('sii','App not found'));
        }
        return [
            'app'=>$app,
            'role'=>$role,
            'modelFilter'=>$modelFilter,
        ];
    }    
}

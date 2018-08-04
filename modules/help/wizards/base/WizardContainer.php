<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardBase');
/**
 * Description of Wizard
 *
 * @author kwlok
 */
class WizardContainer extends WizardBase 
{
    private $_p;//advice profile
    /**
     * Wizard constructor
     * @param string $id
     * @param mixed $profile
     */
    public function __construct($id,$profile=null)
    {
        Yii::app()->getModule('help')->init();//call module init so that wizard.css is generated into asset-bundle
        parent::__construct($id);
        if (isset($profile)){
            $profileClass = 'Wizard'.$profile;
            $this->setProfile(new $profileClass($profile));
            $this->setName($this->profile->name);
        }
    }
    /**
     * Run wizard to get advices based on profile
     */
    public function runProfile()
    {
        $this->profile->run();
    }
    /**
     * Set profile 
     */
    public function setProfile($profile)
    {
        $this->_p = $profile;
    }
    /**
     * @return profile name
     */
    public function getProfile()
    {
        return $this->_p;
    }
    /**
     * @return profile class name
     */
    public function getProfileClass()
    {
        if (isset($this->profile))
            return get_class($this->profile);
        else
            return 'undefined';
    }
    /**
     * @return advices
     */
    public function getAdvices()
    {
        return $this->profile->getAdvices();
    }    
    /**
     * @return boolean has advices
     */
    public function hasAdvices()
    {
        return $this->profile->hasAdvices();
    }    
    /**
     * Render advices
     * @return html string
     */
    public function renderAdvices()
    {
        $output = CHtml::openTag('div',array('class'=>'wizard-advices '.$this->profile->theme.' rounded'));
        $this->runProfile();
        if ($this->hasAdvices()){
            $output .= CHtml::tag('div',array('class'=>'title'), $this->profile->icon.$this->name);
            $output .= Helper::htmlList($this->getAdvices(),array('class'=>'checklist'));
        }
        $output .= CHtml::closeTag('div');
        return $output;
    }
    /**
     * Generate flash
     * @return flash id if flash is generated; False if there is no advices
     */
    public function generateFlash($webuser)
    {
        $this->runProfile();
        if ($this->hasAdvices()){
            $webuser->setFlash($this->flashId,array(
                'message'=>Helper::htmlList($this->getAdvices(),array('class'=>'checklist')),
                'type'=>'advice',
                'theme'=>$this->profile->theme,
                'icon'=>$this->profile->icon,
                'title'=>$this->name,
            ));
            return $this->flashId;
        }
        else
            return false;
    }
    /**
     * @return flash id
     */
    public function getFlashId()
    {
        return $this->getId();
    }  
    
}

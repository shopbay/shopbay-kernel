<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardBase');
/**
 * Description of WizardProfile
 *
 * @author kwlok
 */
class WizardProfile extends WizardBase
{
    /*
     * private properties
     */
    private $_a;//advices
    private $_r = [];//requirements placeholder
    private $_status = false;//profile status, True if all requirements are met
    private $_m;//profile message when all requirements are met
    private $_i;//profile icon
    private $_t;//profile color theme - the key follows flash "success, error, notice, advice"
    /**
     * Profile constructor
     * @param int $id 
     */
    public function __construct($id)
    {
        parent::__construct($id);
        $this->_a = new CList();
        $this->_m = array(
            'visible'=>false,
            'message'=>Sii::t('sii','Congratulations, all profile requirements are met.'),
        );
    }
    /**
     * Add requirement
     */
    public function addRequirement($requirement)
    {
        $this->_r[] = $requirement;
    }
    /*
     * Profile requirements (return valid ones only)
     */
    public function requirements()
    {
        $reqMetCount = 0;//count how many requirements are met
        foreach ($this->_r as $requirement) {
            if (isset($requirement['status']) && !$requirement['status']){
                $reqMetCount++;
            }
        }

        if ($reqMetCount < count($this->_r))//at least one requirement not met
            return $this->_r;
        else  {//all requirements met
            $this->_status = true;
            return [];//empty requirements
        }
    }    
    /*
     * Execute profile rules
     */
    public function run()
    {
        foreach ($this->requirements() as $requirement) {
            if (is_array($requirement))
                $advice = $requirement['advice'];
            else 
                $advice = $requirement;
       
            if (isset($requirement['status'])){
                if ($requirement['status'])
                    $advice .= $requirement['action'];
                $this->addAdvice($this->formatAdvice($advice,'checkbox',!$requirement['status']));//negate status so to get uncheck 
            }
            else {
                $this->addAdvice($this->formatAdvice($advice));
            }
        }
        if ($this->requirementsMet() && $this->isProfileMessageVisible)
            $this->addAdvice($this->formatAdvice($this->getProfileMessage(),'checkbox',true));
    }
    /*
     * Format message
     */
    public function formatAdvice($message,$listType='caret',$checked=false)
    {
        $output = CHtml::openTag('div',array('class'=>'advice '.($checked?'checked':'')));
        switch ($listType) {
            case 'checkbox':
                if ($checked)
                    $output .= '<i class="fa fa-check-square-o"></i>';
                else
                    $output .= '<i class="fa fa-square-o"></i>';
                break;
            default:
                $output .= '<i class="fa fa-caret-right"></i>';
                break;
        }
        $output .= $message;
        $output .= CHtml::closeTag('div');
        return $output;
    }
    /**
     * Format link
     * @param type $route
     * @return type
     */
    public function formatLink($route,$text=null)
    {
        if (!isset($text))
            $text = Sii::t('sii','Do it now.');
        return CHtml::link($text, url($route) , array('class'=>'advice-link'));
    }
    /**
     * Add advice by topic
     */
    public function addAdvice($message)
    {
        $this->_a->add($message);
    }
    /**
     * @return advices
     */
    public function getAdvices()
    {
        return $this->_a->toArray();
    }
    /**
     * @return boolean
     */
    public function hasAdvices()
    {
        return $this->_a->getCount()>0;
    }
    /*
     * Check of all requirements met
     */
    public function requirementsMet()
    {
        return $this->_status;
    }
    /*
     * Set profile message when all requirements are met
     */
    public function setProfileMessage($message)
    {
        $this->_m = $message;
    }
    /*
     * Set profile message when all requirements are met
     */
    public function getIsProfileMessageVisible()
    {
        return $this->_m['visible'];
    }
    /*
     * @return profile message
     */
    public function getProfileMessage()
    {
        return $this->_m['message'];
    }
    /**
     * Set icon 
     */
    public function setIcon($icon)
    {
        $this->_i = $icon;
    }
    /**
     * @return icon
     */
    public function getIcon()
    {
        if (!isset($this->_i))
            $this->_i = SButtonColumn::getButtonIcon('help');
        return $this->_i;
    }    
    /**
     * Set theme 
     */
    public function setTheme($theme)
    {
        $this->_t = $theme;
    }
    /**
     * @return theme
     */
    public function getTheme()
    {
        if (!isset($this->_t))
            $this->_t = 'default';
        return $this->_t;
    }    
}

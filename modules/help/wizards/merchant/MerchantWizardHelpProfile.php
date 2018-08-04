<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.merchant.MerchantWizardProfile');
/**
 * Description of MerchantWizardHelpProfile
 *
 * @author kwlok
 */
class MerchantWizardHelpProfile extends MerchantWizardProfile
{
    /**
     * Profile constructor
     * @param mxied $id profile id
     */
    public function __construct($id=__CLASS__)
    {
        parent::__construct($id);
        $this->setName(Sii::t('sii','Learn more about how {app} Shop works and helps in growing your business?',array('{app}'=>param('SITE_NAME'))));
        $this->addRequirement(Sii::t('sii','Check out {link} for our definitive shop user guide.',array('{link}'=>CHtml::link(Sii::t('sii','Help Center'),url('help'),['target'=>'_blank']))));
        $this->addRequirement(Sii::t('sii','Check out {link}. There are plenty of tutorials and Q&A contributed by community to help you start.',array('{link}'=>CHtml::link(Sii::t('sii','Community Portal'),url('community'),['target'=>'_blank']))));
    }     
}
